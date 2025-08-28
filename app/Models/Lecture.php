<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lecture extends Model
{
    protected $fillable = [
        'title', 'date', 'start_time', 'end_time', 'teacher_id', 'group_id',
    ];

    protected $casts = [
        'date'       => 'date',
        'start_time' => 'datetime',
        'end_time'   => 'datetime',
    ];

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    public function attendance()
    {
        return $this->hasMany(Attendance::class);
    }

    // Check if lecture is today
    public function getIsTodayAttribute()
    {
        return $this->date->isToday();
    }

    // Check if lecture has started
    public function getHasStartedAttribute()
    {
        return now() >= $this->start_time;
    }

    // Get attendance summary
    public function getAttendanceSummary()
    {
        $attendance = $this->attendance()->get()->groupBy('status');

        return [
            'present' => $attendance->get('present', collect())->count(),
            'absent'  => $attendance->get('absent', collect())->count(),
            'late'    => $attendance->get('late', collect())->count(),
            'total'   => $this->group->students()->count(),
        ];
    }

    // Get students without attendance records for this lecture
    public function getStudentsWithoutAttendance()
    {
        $recordedStudentIds = $this->attendance()->pluck('student_id');

        return $this->group->students()
            ->whereNotIn('id', $recordedStudentIds)
            ->with('user')
            ->get();
    }

    // Format for FullCalendar
    public function toCalendarEvent()
    {
        return [
            'id'              => $this->id,
            'title'           => $this->title,
            'start'           => $this->date->format('Y-m-d') . 'T' . $this->start_time->format('H:i:s'),
            'end'             => $this->date->format('Y-m-d') . 'T' . $this->end_time->format('H:i:s'),
            'backgroundColor' => '#2778E5',
            'borderColor'     => '#EE8100',
            'extendedProps'   => [
                'teacher'        => $this->teacher->user->name,
                'group'          => $this->group->name,
                'students_count' => $this->group->students_count,
            ],
        ];
    }
}
