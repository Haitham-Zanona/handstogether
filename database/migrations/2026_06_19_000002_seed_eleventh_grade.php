<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // إضافة مجموعة الصف الحادي عشر إذا لم تكن موجودة
        if (! DB::table('groups')->where('grade_level', 'الصف الحادي عشر')->exists()) {
            DB::table('groups')->insert([
                'name'              => 'الصف الحادي عشر - الشعبة أ',
                'grade_level'       => 'الصف الحادي عشر',
                'section'           => 'أ',
                'section_number'    => 1,
                'students_count'    => 0,
                'max_capacity'      => 20,
                'is_active'         => true,
                'is_archived'       => false,
                'final_exam_active' => false,
                'description'       => 'المجموعة الأساسية لطلاب الصف الحادي عشر',
                'grade_weights'     => null,
                'created_at'        => now(),
                'updated_at'        => now(),
            ]);
        }

        // إضافة مواد الصف الحادي عشر إذا لم تكن موجودة
        $subjects = ['اللغة الإنجليزية', 'اللغة العربية', 'الرياضيات', 'العلوم'];

        foreach ($subjects as $subject) {
            if (! DB::table('subjects')->where('name', $subject)->where('grade_level', 'الصف الحادي عشر')->exists()) {
                DB::table('subjects')->insert([
                    'name'        => $subject,
                    'grade_level' => 'الصف الحادي عشر',
                    'description' => $subject . ' للطلاب في الصف الحادي عشر',
                    'is_active'   => true,
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        DB::table('subjects')->where('grade_level', 'الصف الحادي عشر')->delete();
        DB::table('groups')->where('grade_level', 'الصف الحادي عشر')->delete();
    }
};
