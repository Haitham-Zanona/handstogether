<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('group_subjects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
            $table->foreignId('teacher_id')->nullable()->constrained()->nullOnDelete();
            $table->json('schedule')->nullable(); // مواعيد المادة داخل المجموعة
            $table->boolean('is_active')->default(true); // حالة المادة في المجموعة
            $table->timestamps();

            // فهرس مركب لضمان عدم تكرار المادة في نفس المجموعة
            $table->unique(['group_id', 'subject_id']);

        });

        // ربط المواد بالمجموعات تلقائياً
        // هاي العملية ح تتم بعد إنشاء الجداول
        $this->linkGroupsWithSubjects();
    }


    /**
     * ربط المجموعات بالمواد المناسبة لمراحلها
     */
    private function linkGroupsWithSubjects()
    {
        // الحصول على كل المجموعات
        $groups = DB::table('groups')->get();
        
        foreach ($groups as $group) {
            // الحصول على المواد المناسبة لهذه المرحلة
            $subjects = DB::table('subjects')
                ->where('grade_level', $group->grade_level)
                ->get();
                
            foreach ($subjects as $subject) {
                DB::table('group_subjects')->insert([
                    'group_id' => $group->id,
                    'subject_id' => $subject->id,
                    'teacher_id' => null, // سيتم تعيين المدرسين لاحقاً
                    'schedule' => json_encode([
                        'days' => [], // أيام الأسبوع
                        'times' => [], // أوقات الحصص
                        'duration' => 45 // مدة الحصة بالدقائق
                    ]),
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('group_subjects');
    }
};