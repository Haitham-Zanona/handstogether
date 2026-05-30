<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // users: national_id used in every parent/student login query
        Schema::table('users', function (Blueprint $table) {
            $table->index('national_id');
            $table->index('role');
            $table->index(['role', 'is_active']);
        });

        // payments: month & type filtered constantly; composite covers most query patterns
        Schema::table('payments', function (Blueprint $table) {
            $table->index('month');
            $table->index('type');
            $table->index('due_date');
            $table->index('paid_date');
            $table->index(['month', 'status']);
            $table->index(['student_id', 'status']);
        });

        // attendance: status column used in every report
        Schema::table('attendance', function (Blueprint $table) {
            $table->index('status');
            $table->index(['student_id', 'status']);
        });

        // lectures: composite (date, status) for upcoming/active lecture queries
        Schema::table('lectures', function (Blueprint $table) {
            $table->index('type');
            $table->index(['date', 'status']);
            $table->index(['group_id', 'date']);
        });

        // students: parent_id and admission_id lookup
        Schema::table('students', function (Blueprint $table) {
            $table->index('admission_id');
            $table->index('parent_id');
        });

        // group_subjects: teacher assignment lookups
        Schema::table('group_subjects', function (Blueprint $table) {
            $table->index('teacher_id');
            $table->index('subject_id');
        });

        // parent_messages: unread count runs on every admin sidebar render
        Schema::table('parent_messages', function (Blueprint $table) {
            $table->index('is_read');
            $table->index('parent_user_id');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['national_id']);
            $table->dropIndex(['role']);
            $table->dropIndex(['role', 'is_active']);
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->dropIndex(['month']);
            $table->dropIndex(['type']);
            $table->dropIndex(['due_date']);
            $table->dropIndex(['paid_date']);
            $table->dropIndex(['month', 'status']);
            $table->dropIndex(['student_id', 'status']);
        });

        Schema::table('attendance', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropIndex(['student_id', 'status']);
        });

        Schema::table('lectures', function (Blueprint $table) {
            $table->dropIndex(['type']);
            $table->dropIndex(['date', 'status']);
            $table->dropIndex(['group_id', 'date']);
        });

        Schema::table('students', function (Blueprint $table) {
            $table->dropIndex(['admission_id']);
            $table->dropIndex(['parent_id']);
        });

        Schema::table('group_subjects', function (Blueprint $table) {
            $table->dropIndex(['teacher_id']);
            $table->dropIndex(['subject_id']);
        });

        Schema::table('parent_messages', function (Blueprint $table) {
            $table->dropIndex(['is_read']);
            $table->dropIndex(['parent_user_id']);
        });
    }
};
