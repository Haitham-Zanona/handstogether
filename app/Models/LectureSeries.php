<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LectureSeries extends Model
{

    protected $fillable = ['title', 'start_date', 'end_date', 'start_time', 'end_time', 'teacher_id', 'group_id', 'subject_id', 'status'];

    protected $casts = [
        'start_date' => 'date',
        'end_date'   => 'date',
        'start_time' => 'datetime:H:i',
        'end_time'   => 'datetime:H:i',
    ];

    public function lectures()
    {
        return $this->hasMany(Lecture::class, 'series_id');
    }
    public function days()
    {
        return $this->hasMany(SeriesDay::class, 'series_id');
    }
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

}