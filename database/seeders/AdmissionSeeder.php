<?php
namespace Database\Seeders;

use App\Models\Admission;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class AdmissionSeeder extends Seeder
{
    public function run()
    {
        $admissions = [
            // طالب في حالة pending
            [
                'day'                => 'Monday',
                'application_date'   => now()->subDays(10),
                'application_number' => 'APP-' . Str::random(6),

                'student_name'       => 'علي محمد حسن',
                'student_id'         => '200501001',
                'birth_date'         => '2005-03-15',
                'grade'              => '10',
                'academic_level'     => 'Secondary',

                'parent_name'        => 'محمد حسن علي',
                'parent_id'          => '198501001',
                'parent_job'         => 'Engineer',

                'father_phone'       => '+970-599-111-222',
                'mother_phone'       => '+970-599-111-333',
                'address'            => 'نابلس - حي الأشرفية',

                'monthly_fee'        => 250.00,
                'study_start_date'   => '2025-09-01',
                'payment_due_from'   => '2025-09-01',
                'payment_due_to'     => '2025-12-01',

                'status'             => 'pending',
                'group_id'           => null,
            ],

            // طالب في حالة pending
            [
                'day'                => 'Tuesday',
                'application_date'   => now()->subDays(8),
                'application_number' => 'APP-' . Str::random(6),

                'student_name'       => 'فاطمة أحمد صالح',
                'student_id'         => '200401002',
                'birth_date'         => '2004-07-22',
                'grade'              => '11',
                'academic_level'     => 'Secondary',

                'parent_name'        => 'أحمد صالح محمود',
                'parent_id'          => '198401002',
                'parent_job'         => 'Teacher',

                'father_phone'       => '+970-599-333-444',
                'mother_phone'       => '+970-599-333-555',
                'address'            => 'نابلس - شارع فيصل',

                'monthly_fee'        => 270.00,
                'study_start_date'   => '2025-09-01',
                'payment_due_from'   => '2025-09-01',
                'payment_due_to'     => '2025-12-01',

                'status'             => 'pending',
                'group_id'           => null,
            ],

            // طالب في حالة approved
            [
                'day'                => 'Wednesday',
                'application_date'   => now()->subDays(6),
                'application_number' => 'APP-' . Str::random(6),

                'student_name'       => 'يوسف خالد عمر',
                'student_id'         => '200501003',
                'birth_date'         => '2005-11-08',
                'grade'              => '10',
                'academic_level'     => 'Secondary',

                'parent_name'        => 'خالد عمر يوسف',
                'parent_id'          => '198501003',
                'parent_job'         => 'Doctor',

                'father_phone'       => '+970-599-555-666',
                'mother_phone'       => '+970-599-555-777',
                'address'            => 'نابلس - حي الشيخ مسلم',

                'monthly_fee'        => 250.00,
                'study_start_date'   => '2025-09-01',
                'payment_due_from'   => '2025-09-01',
                'payment_due_to'     => '2025-12-01',

                'status'             => 'approved',
                'group_id'           => 1, // مثال على مجموعة تم تعيينها
            ],

            // طالب في حالة rejected
            [
                'day'                => 'Thursday',
                'application_date'   => now()->subDays(4),
                'application_number' => 'APP-' . Str::random(6),

                'student_name'       => 'زينب سامي حسين',
                'student_id'         => '200401004',
                'birth_date'         => '2004-12-30',
                'grade'              => '11',
                'academic_level'     => 'Secondary',

                'parent_name'        => 'سامي حسين أحمد',
                'parent_id'          => '198401004',
                'parent_job'         => 'Nurse',

                'father_phone'       => '+970-599-777-888',
                'mother_phone'       => '+970-599-777-999',
                'address'            => 'نابلس - حي القريون',

                'monthly_fee'        => 270.00,
                'study_start_date'   => '2025-09-01',
                'payment_due_from'   => '2025-09-01',
                'payment_due_to'     => '2025-12-01',

                'status'             => 'rejected',
                'group_id'           => null,
            ],
        ];

        foreach ($admissions as $admission) {
            Admission::create($admission);
        }

    }
}
