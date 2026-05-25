<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // admissions: status+created_at and student_id already indexed in create migration
        Schema::table('admissions', function (Blueprint $table) {
            $table->index('student_name');
            $table->index('group_id');
            $table->index('application_date');
        });

        // students: no indexes on user_id or group_id in create migration
        Schema::table('students', function (Blueprint $table) {
            $table->index('user_id');
            $table->index('group_id');
        });

        // lectures: teacher_id and group_id have foreign-key indexes; add missing ones
        Schema::table('lectures', function (Blueprint $table) {
            $table->index('date');
            $table->index('series_id');
            $table->index('status');
        });

        // attendance: unique(student_id, lecture_id) already covers student_id lookups;
        // add lecture_id for reverse lookups (find all students in a lecture)
        Schema::table('attendance', function (Blueprint $table) {
            $table->index('lecture_id');
        });

        // payments: unique(student_id, month) already covers student_id; add status
        Schema::table('payments', function (Blueprint $table) {
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::table('admissions', function (Blueprint $table) {
            $table->dropIndex(['student_name']);
            $table->dropIndex(['group_id']);
            $table->dropIndex(['application_date']);
        });

        Schema::table('students', function (Blueprint $table) {
            $table->dropIndex(['user_id']);
            $table->dropIndex(['group_id']);
        });

        Schema::table('lectures', function (Blueprint $table) {
            $table->dropIndex(['date']);
            $table->dropIndex(['series_id']);
            $table->dropIndex(['status']);
        });

        Schema::table('attendance', function (Blueprint $table) {
            $table->dropIndex(['lecture_id']);
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->dropIndex(['status']);
        });
    }
};
