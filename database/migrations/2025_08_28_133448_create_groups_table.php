<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('groups', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('grade_level');                 // إزالة nullable لأنه مطلوب
            $table->string('section')->default('أ');      // إضافة حرف الشعبة
            $table->integer('section_number')->default(1); // إضافة رقم الشعبة
            $table->integer('students_count')->default(0);
            $table->integer('max_capacity')->default(20);
            $table->boolean('is_active')->default(true);
            $table->text('description')->nullable();
            $table->timestamps();

        });

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

        foreach ($gradeLevels as $grade) {
            DB::table('groups')->insert([
                'name'           => $grade . ' - الشعبة أ',
                'grade_level'    => $grade,
                'section'        => 'أ',
                'section_number' => 1,
                'students_count' => 0,
                'max_capacity'   => 20,
                'is_active'      => true,
                'description'    => 'المجموعة الأساسية لطلاب ' . $grade,
                'created_at'     => now(),
                'updated_at'     => now(),
            ]);
        }

    }

    public function down()
    {
        Schema::dropIfExists('groups');
    }
};
