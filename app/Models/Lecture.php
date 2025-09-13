<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lecture extends Model
{
    protected $fillable = [
        'title', 'date', 'start_time', 'end_time', 'teacher_id', 'group_id', 'subject_id',
        'type', 'status', 'series_id', 'series_status', 'room', 'total_marks', 'duration',
        'description', 'cancellation_reason', 'cancelled_at', 'reschedule_reason',
        'reschedule_old_data', 'rescheduled_at',

    ];

    protected $casts = [
        'date'                => 'date',
        'start_time'          => 'datetime',
        'end_time'            => 'datetime',
        'cancelled_at'        => 'datetime',
        'rescheduled_at'      => 'datetime',
        'reschedule_old_data' => 'array',

    ];

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
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

    public function getIsUpcomingAttribute()
    {
        return $this->date->isFuture();
    }

    public function getIsPartOfSeriesAttribute()
    {
        return ! is_null($this->series_id);
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
            'backgroundColor' => $this->getTypeColor(),
            'borderColor'     => $this->getStatusColor(),
            'extendedProps'   => [
                'type'           => $this->type,
                'status'         => $this->status,
                'teacher_name'   => $this->teacher->user->name,
                'group_name'     => $this->group->name,
                'subject_name'   => $this->subject->name ?? '',
                'students_count' => $this->group->students_count,
                'series_id'      => $this->series_id,
                'description'    => $this->description,
                'room'           => $this->room,
                'total_marks'    => $this->total_marks,
            ],
        ];

    }

    public function getTypeColor()
    {
        return match ($this->type) {
            'final_exam' => '#DC3545',
            'exam'       => '#EE8100',
            'review'     => '#28A745',
            'activity'   => '#FFC107',
            default      => '#2778E5'
        };
    }

    public function getStatusColor()
    {
        return match ($this->status) {
            'cancelled'   => '#6C757D',
            'rescheduled' => '#6F42C1',
            'completed'   => '#198754',
            default       => $this->getTypeColor()
        };
    }

    // Scopes
    public function scopeUpcoming($query)
    {
        return $query->where('date', '>=', today());
    }

    public function scopeToday($query)
    {
        return $query->whereDate('date', today());
    }

    public function scopeThisWeek($query)
    {
        return $query->whereBetween('date', [now()->startOfWeek(), now()->endOfWeek()]);
    }

    public function scopeThisMonth($query)
    {
        return $query->whereMonth('date', now()->month);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeBySeries($query, $seriesId)
    {
        return $query->where('series_id', $seriesId);
    }

    public function scopeActiveSeries($query)
    {
        return $query->whereNotNull('series_id')
            ->where('series_status', 'active');
    }

}