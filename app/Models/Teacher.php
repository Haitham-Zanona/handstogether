<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Teacher extends Model
{
    protected $fillable = ['user_id', 'specialization'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function lectures()
    {
        return $this->hasMany(Lecture::class);
    }

    public function groups()
    {
        return $this->hasManyThrough(Group::class, Lecture::class);
    }

    // Get students taught by this teacher
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
