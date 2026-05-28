<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FinalExamScore extends Model
{
    protected $fillable = [
        'group_id', 'student_id', 'score', 'notes', 'entered_by',
    ];

    protected $casts = ['score' => 'decimal:2'];
}
