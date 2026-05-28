<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MonthlyTestScore extends Model
{
    protected $fillable = [
        'group_id', 'student_id', 'test_number',
        'month', 'score', 'notes', 'entered_by',
    ];

    protected $casts = ['score' => 'decimal:2'];
}
