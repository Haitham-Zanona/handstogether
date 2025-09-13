<?php
namespace Database\Factories;

use App\Models\Lecture;
use Illuminate\Database\Eloquent\Factories\Factory;

class LectureFactory extends Factory
{
    protected $model = Lecture::class;

    public function definition()
    {
        // $subjects = [
        //     'محاضرة الرياضيات', 'درس اللغة العربية', 'حصة العلوم',
        //     'درس اللغة الإنجليزية', 'محاضرة التاريخ', 'درس الجغرافيا', 'حصة الحاسوب',
        // ];

        // $startTime = fake()->time('H:i:s', '16:00:00');
        // $endTime   = fake()->time('H:i:s', '18:00:00');

        // return [
        //     'title'       => fake()->randomElement($subjects),
        //     'date'        => fake()->dateTimeBetween('now', '+1 month')->format('Y-m-d'),
        //     'start_time'  => $startTime,
        //     'end_time'    => $endTime,
        //     'teacher_id'  => Teacher::factory(),
        //     'group_id'    => Group::factory(),
        //     'description' => fake()->text(150),
        // ];
    }
}