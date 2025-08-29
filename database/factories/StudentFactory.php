<?php
namespace Database\Factories;

use App\Models\Group;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class StudentFactory extends Factory
{
    protected $model = Student::class;

    public function definition()
    {
        return [
            'user_id'    => User::factory()->student(),
            'parent_id'  => User::factory()->parent(),
            'group_id'   => Group::factory(),
            'birth_date' => fake()->dateTimeBetween('-25 years', '-16 years')->format('Y-m-d'),
        ];
    }
}