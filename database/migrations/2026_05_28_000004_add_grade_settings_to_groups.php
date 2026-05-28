<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1) إضافة حقول إعدادات الدرجات إلى جدول المجموعات (إن لم تكن موجودة)
        Schema::table('groups', function (Blueprint $table) {
            if (! Schema::hasColumn('groups', 'grade_weights'))
                $table->json('grade_weights')->nullable()->after('description');
            if (! Schema::hasColumn('groups', 'is_archived'))
                $table->boolean('is_archived')->default(false)->after('is_active');
            if (! Schema::hasColumn('groups', 'final_exam_active'))
                $table->boolean('final_exam_active')->default(false)->after('is_archived');
            if (! Schema::hasColumn('groups', 'start_date'))
                $table->date('start_date')->nullable()->after('final_exam_active');
            if (! Schema::hasColumn('groups', 'end_date'))
                $table->date('end_date')->nullable()->after('start_date');
        });

        // 2) student_evaluations — أزل FK أولاً ثم الـ index ثم العمود
        Schema::table('student_evaluations', function (Blueprint $table) {
            $table->dropForeign(['semester_id']);
        });
        Schema::table('student_evaluations', function (Blueprint $table) {
            $table->dropUnique('eval_unique');
            $table->dropColumn('semester_id');
            $table->unique(['group_id', 'student_id', 'eval_number'], 'eval_unique');
        });

        // 3) monthly_test_scores
        Schema::table('monthly_test_scores', function (Blueprint $table) {
            $table->dropForeign(['semester_id']);
        });
        Schema::table('monthly_test_scores', function (Blueprint $table) {
            $table->dropUnique('test_unique');
            $table->dropColumn('semester_id');
            $table->unique(['group_id', 'student_id', 'test_number'], 'test_unique');
        });

        // 4) final_exam_scores
        Schema::table('final_exam_scores', function (Blueprint $table) {
            $table->dropForeign(['semester_id']);
        });
        Schema::table('final_exam_scores', function (Blueprint $table) {
            $table->dropUnique('final_unique');
            $table->dropColumn('semester_id');
            $table->unique(['group_id', 'student_id'], 'final_unique');
        });

        // 5) حذف جدول semesters بعد نقل مفهومه إلى groups
        Schema::dropIfExists('semesters');
    }

    public function down(): void
    {
        Schema::create('semesters', function (Blueprint $table) {
            $table->id();
            $table->string('label');
            $table->enum('type', ['first', 'second'])->default('first');
            $table->string('academic_year', 20);
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->json('grade_weights');
            $table->boolean('is_active')->default(false);
            $table->boolean('is_archived')->default(false);
            $table->boolean('final_exam_active')->default(false);
            $table->timestamps();
        });

        Schema::table('student_evaluations', function (Blueprint $table) {
            $table->dropUnique('eval_unique');
            $table->foreignId('semester_id')->after('id')->constrained()->onDelete('cascade');
            $table->unique(['semester_id', 'student_id', 'eval_number'], 'eval_unique');
        });

        Schema::table('monthly_test_scores', function (Blueprint $table) {
            $table->dropUnique('test_unique');
            $table->foreignId('semester_id')->after('id')->constrained()->onDelete('cascade');
            $table->unique(['semester_id', 'student_id', 'test_number'], 'test_unique');
        });

        Schema::table('final_exam_scores', function (Blueprint $table) {
            $table->dropUnique('final_unique');
            $table->foreignId('semester_id')->after('id')->constrained()->onDelete('cascade');
            $table->unique(['semester_id', 'student_id'], 'final_unique');
        });

        Schema::table('groups', function (Blueprint $table) {
            $table->dropColumn(['grade_weights', 'is_archived', 'final_exam_active', 'start_date', 'end_date']);
        });
    }
};
