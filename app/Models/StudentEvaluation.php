<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentEvaluation extends Model
{
    protected $fillable = [
        'group_id', 'student_id', 'teacher_id', 'eval_number',
        'activity_participation', 'behavior_discipline', 'academic_improvement',
        'homework', 'short_tests', 'notes',
    ];

    public function getCriteriaSum(): int
    {
        return $this->activity_participation + $this->behavior_discipline
             + $this->academic_improvement + $this->homework + $this->short_tests;
    }
}
