<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    protected $fillable = ['name', 'description'];

    public function students()
    {
        return $this->hasMany(Student::class);
    }

    public function lectures()
    {
        return $this->hasMany(Lecture::class);
    }

    public function teachers()
    {
        return $this->hasManyThrough(Teacher::class, Lecture::class);
    }

    // Get total students count
    public function getTotalStudentsAttribute()
    {
        return $this->students()->count();
    }

    // Get active lectures for today
    public function getTodayLectures()
    {
        return $this->lectures()
            ->whereDate('date', today())
            ->with('teacher.user')
            ->orderBy('start_time')
            ->get();
    }
}
