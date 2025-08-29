<?php
namespace Database\Factories;

use App\Models\Teacher;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class TeacherFactory extends Factory
{
    protected $model = Teacher::class;

    public function definition()
    {
        $specializations = [
            'الرياضيات', 'اللغة العربية', 'اللغة الإنجليزية',
            'العلوم', 'التاريخ', 'الجغرافيا', 'الحاسوب',
        ];

        return [
            'user_id'        => User::factory()->teacher(),
            'specialization' => fake()->randomElement($specializations),
        ];
    }
}
