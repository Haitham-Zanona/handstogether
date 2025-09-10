<?php
namespace Database\Seeders;

use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Seeder;

class StudentsSeeder extends Seeder
{
    public function run()
    {
        // إنشاء 20 طالب بدون مجموعات
        for ($i = 1; $i <= 20; $i++) {
            $user = User::create([
                'name' => "طالب تجريبي {$i}",
                'email' => "student{$i}@test.com",
                'password' => bcrypt('password'),
                'role'     => 'student',
            ]);

            Student::create([
                'user_id'    => $user->id,
                'group_id'   => null, // بدون مجموعة
                'birth_date' => now()->subYears(rand(6, 16)),
            ]);
        }
    }
}
