<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ParentMessage extends Model
{
    protected $fillable = ['parent_user_id', 'student_id', 'message', 'is_read', 'read_at'];

    protected $casts = ['is_read' => 'boolean', 'read_at' => 'datetime'];

    public function parent()
    {
        return $this->belongsTo(User::class, 'parent_user_id');
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }
}
