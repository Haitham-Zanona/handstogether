<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('subjects', function (Blueprint $table) {
            $table->id();
            $table->string('name');                    // اسم المادة
            $table->string('grade_level')->nullable(); // المرحلة المرتبطة (اختياري)
            $table->text('description')->nullable();   // وصف المادة
            $table->boolean('is_active')->default(true); // حالة المادة
            $table->timestamps();

        });

        $subjects = ['اللغة الإنجليزية', 'اللغة العربية', 'الرياضيات', 'العلوم'];
        $gradeLevels = [
            'الصف الأول',
            'الصف الثاني',
            'الصف الثالث',
            'الصف الرابع',
            'الصف الخامس',
            'الصف السادس',
            'الصف السابع',
            'الصف الثامن',
            'الصف التاسع',
            'الصف العاشر',
        ];

        $insertData = [];

        // إنشاء مواد لكل مرحلة
        foreach ($gradeLevels as $grade) {
            foreach ($subjects as $subject) {
                $insertData[] = [
                    'name' => $subject,
                    'grade_level' => $grade,
                    'description' => $subject . ' للطلاب في ' . $grade,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        // إدخال البيانات
        DB::table('subjects')->insert($insertData);

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subjects');
    }
};
