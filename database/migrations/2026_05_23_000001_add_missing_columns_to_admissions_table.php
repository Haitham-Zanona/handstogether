<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('admissions', function (Blueprint $table) {
            if (! Schema::hasColumn('admissions', 'phone')) {
                $table->string('phone')->nullable()->after('mother_phone');
            }

            if (! Schema::hasColumn('admissions', 'rejection_reason')) {
                $table->text('rejection_reason')->nullable()->after('approved_by');
            }

            if (! Schema::hasColumn('admissions', 'parent_user_id')) {
                $table->unsignedBigInteger('parent_user_id')->nullable()->after('rejection_reason');
                $table->foreign('parent_user_id')->references('id')->on('users')->onDelete('set null');
            }

            if (! Schema::hasColumn('admissions', 'student_user_id')) {
                $table->unsignedBigInteger('student_user_id')->nullable()->after('parent_user_id');
                $table->foreign('student_user_id')->references('id')->on('users')->onDelete('set null');
            }
        });
    }

    public function down(): void
    {
        Schema::table('admissions', function (Blueprint $table) {
            if (Schema::hasColumn('admissions', 'student_user_id')) {
                $table->dropForeign(['student_user_id']);
                $table->dropColumn('student_user_id');
            }

            if (Schema::hasColumn('admissions', 'parent_user_id')) {
                $table->dropForeign(['parent_user_id']);
                $table->dropColumn('parent_user_id');
            }

            if (Schema::hasColumn('admissions', 'rejection_reason')) {
                $table->dropColumn('rejection_reason');
            }

            if (Schema::hasColumn('admissions', 'phone')) {
                $table->dropColumn('phone');
            }
        });
    }
};
