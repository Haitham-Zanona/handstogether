<?php
namespace Database\Seeders;

use App\Models\Group;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run()
    {
        // Create Admin
        $admin = User::create([
            'name'     => 'أحمد المدير',
            'email'    => 'admin@academy.edu',
            'password' => Hash::make('123456'),
            'role'     => 'admin',
            'phone'    => '+970-599-123-456',
        ]);

        // Create Teachers
        $teachers = [
            [
                'name'           => 'د. محمد أحمد',
                'email'          => 'teacher1@academy.edu',
                'specialization' => 'الرياضيات',
            ],
            [
                'name'           => 'أ. فاطمة علي',
                'email'          => 'teacher2@academy.edu',
                'specialization' => 'اللغة العربية',
            ],
            [
                'name'           => 'م. خالد محمود',
                'email'          => 'teacher3@academy.edu',
                'specialization' => 'العلوم',
            ],
            [
                'name'           => 'أ. سارة حسن',
                'email'          => 'teacher4@academy.edu',
                'specialization' => 'اللغة الإنجليزية',
            ],
            [
                'name'           => 'د. يوسف إبراهيم',
                'email'          => 'teacher5@academy.edu',
                'specialization' => 'التاريخ',
            ],
        ];

        foreach ($teachers as $teacherData) {
            $user = User::create([
                'name'     => $teacherData['name'],
                'email'    => $teacherData['email'],
                'password' => Hash::make('123456'),
                'role'     => 'teacher',
                'phone'    => '+970-599-' . rand(100000, 999999),
            ]);

            Teacher::create([
                'user_id'        => $user->id,
                'specialization' => $teacherData['specialization'],
            ]);
        }

        // Create Parents and Students
        $groups = Group::all();

        for ($i = 1; $i <= 30; $i++) {
            // Create Parent
            $parent = User::create([
                'name'     => 'ولي الأمر ' . $i,
                'email'    => 'parent' . $i . '@academy.edu',
                'password' => Hash::make('123456'),
                'role'     => 'parent',
                'phone'    => '+970-599-' . rand(100000, 999999),
            ]);

            // Create Student
            $studentUser = User::create([
                'name'     => 'الطالب ' . $i,
                'email'    => 'student' . $i . '@academy.edu',
                'password' => Hash::make('123456'),
                'role'     => 'student',
                'phone'    => '+970-599-' . rand(100000, 999999),
            ]);

            Student::create([
                'user_id'    => $studentUser->id,
                'parent_id'  => $parent->id,
                'group_id'   => $groups->isNotEmpty() ? $groups->random()->id : null,
                'birth_date' => now()->subYears(rand(16, 20))->subDays(rand(1, 365)),
            ]);
        }
    }
}