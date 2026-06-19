<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // التقييمات الدورية — الفصل الدراسي يُمثَّل بالمجموعة (group)
        Schema::create('student_evaluations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->onDelete('cascade');
            $table->foreignId('teacher_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('group_id')->constrained()->onDelete('cascade');
            $table->tinyInteger('eval_number');
            $table->tinyInteger('activity_participation');
            $table->tinyInteger('behavior_discipline');
            $table->tinyInteger('academic_improvement');
            $table->tinyInteger('homework');
            $table->tinyInteger('short_tests');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->unique(['group_id', 'student_id', 'eval_number'], 'eval_unique');
        });

        // درجات الاختبارات الشهرية
        Schema::create('monthly_test_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->onDelete('cascade');
            $table->foreignId('group_id')->constrained()->onDelete('cascade');
            $table->tinyInteger('test_number');
            $table->string('month', 7);
            $table->decimal('score', 5, 2);
            $table->text('notes')->nullable();
            $table->foreignId('entered_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            $table->unique(['group_id', 'student_id', 'test_number'], 'test_unique');
        });

        // درجات الامتحان النهائي
        Schema::create('final_exam_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->onDelete('cascade');
            $table->foreignId('group_id')->constrained()->onDelete('cascade');
            $table->decimal('score', 5, 2);
            $table->text('notes')->nullable();
            $table->foreignId('entered_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            $table->unique(['group_id', 'student_id'], 'final_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('final_exam_scores');
        Schema::dropIfExists('monthly_test_scores');
        Schema::dropIfExists('student_evaluations');
        Schema::dropIfExists('semesters');
    }
};
