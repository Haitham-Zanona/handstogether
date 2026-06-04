<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Teacher extends Model
{
    protected $fillable = [
        'user_id', 'specialization', 'specializations', 'account_type', 'account_number',
        'hire_date', 'salary',
    ];

    protected $casts = [
        'specializations' => 'array',
        'hire_date'       => 'date',
        'salary'          => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function attendances()
    {
        return $this->hasMany(TeacherAttendance::class);
    }

    public function lectures()
    {
        return $this->hasMany(Lecture::class);
    }

    // Direct assignment via pivot (used for staff management)
    public function assignedGroups()
    {
        return $this->belongsToMany(Group::class, 'teacher_group');
    }

    // Groups derived from lectures (legacy — kept for backward compat)
    public function groups()
    {
        return $this->hasManyThrough(Group::class, Lecture::class)->distinct();
    }

    // Returns a Builder (not a relation) — cannot be eager-loaded
    public function students()
    {
        return Student::whereHas('group.lectures', function ($query) {
            $query->where('teacher_id', $this->id);
        });
    }

    // Get today's lectures
    public function getTodayLectures()
    {
        return $this->lectures()
            ->whereDate('date', today())
            ->with(['group', 'attendance.student.user'])
            ->orderBy('start_time')
            ->get();
    }

    // Get weekly schedule
    public function getWeeklySchedule()
    {
        return $this->lectures()
            ->whereBetween('date', [now()->startOfWeek(), now()->endOfWeek()])
            ->with('group')
            ->orderBy('date')
            ->orderBy('start_time')
            ->get();
    }
}
