<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('admissions', function (Blueprint $table) {
            $table->id();

// بيانات الطلب
            $table->string('day');
            $table->date('application_date');
            $table->string('application_number')->unique();

// بيانات الطالب
            $table->string('student_name');
            $table->string('student_id', 9)->unique();
            $table->date('birth_date');
            $table->string('grade');
            $table->string('academic_level');

// بيانات ولي الأمر
            $table->string('parent_name');
            $table->string('parent_id', 9);
            $table->string('parent_job');

// بيانات التواصل
            $table->string('father_phone');
            $table->string('mother_phone')->nullable();
            $table->text('address');

// المعلومات المالية
            $table->decimal('monthly_fee', 8, 2);
            $table->date('study_start_date');
            $table->date('payment_due_from');
            $table->date('payment_due_to');

// حالة الطلب
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->foreignId('group_id')->nullable()->constrained('groups')->onDelete('set null');

// تواريخ الإنشاء والتحديث
            $table->timestamps();

// فهارس للبحث السريع
            $table->index(['status', 'created_at']);
            $table->index('student_id');
            $table->index('application_number');

        });
    }

    public function down()
    {
        Schema::dropIfExists('admissions');
    }
};
