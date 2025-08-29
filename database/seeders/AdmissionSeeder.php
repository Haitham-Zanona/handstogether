<?php
namespace Database\Seeders;

use App\Models\Admission;
use Illuminate\Database\Seeder;

class AdmissionSeeder extends Seeder
{
    public function run()
    {
        $admissions = [
            [
                'student_name'       => 'علي محمد حسن',
                'parent_name'        => 'محمد حسن علي',
                'phone'              => '+970-599-111-222',
                'email'              => 'ali.parent@gmail.com',
                'student_birth_date' => '2005-03-15',
                'address'            => 'نابلس - حي الأشرفية',
                'status'             => 'pending',
            ],
            [
                'student_name'       => 'فاطمة أحمد صالح',
                'parent_name'        => 'أحمد صالح محمود',
                'phone'              => '+970-599-333-444',
                'email'              => 'fatima.parent@gmail.com',
                'student_birth_date' => '2004-07-22',
                'address'            => 'نابلس - شارع فيصل',
                'status'             => 'pending',
            ],
            [
                'student_name'       => 'يوسف خالد عمر',
                'parent_name'        => 'خالد عمر يوسف',
                'phone'              => '+970-599-555-666',
                'email'              => 'yusuf.parent@gmail.com',
                'student_birth_date' => '2005-11-08',
                'address'            => 'نابلس - حي الشيخ مسلم',
                'status'             => 'pending',
            ],
            [
                'student_name'       => 'زينب سامي حسين',
                'parent_name'        => 'سامي حسين أحمد',
                'phone'              => '+970-599-777-888',
                'email'              => 'zainab.parent@gmail.com',
                'student_birth_date' => '2004-12-30',
                'address'            => 'نابلس - حي القريون',
                'status'             => 'approved',
                'reviewed_at'        => now()->subDays(3),
            ],
            [
                'student_name'       => 'محمد نور الدين',
                'parent_name'        => 'نور الدين عبد الله',
                'phone'              => '+970-599-999-000',
                'email'              => 'noor.parent@gmail.com',
                'student_birth_date' => '2005-06-18',
                'address'            => 'نابلس - حي رفيديا',
                'status'             => 'rejected',
                'notes'              => 'لا يوجد مكان في المجموعة المطلوبة',
                'reviewed_at'        => now()->subDays(7),
            ],
        ];

        foreach ($admissions as $admission) {
            Admission::create($admission);
        }
    }
}
