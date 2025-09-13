<?php
// database/migrations/xxxx_add_advanced_fields_to_lectures_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('lectures', function (Blueprint $table) {
                                                                                    // حقول إضافية للمحاضرات
            $table->string('type')->default('lecture')->after('title');             // lecture, exam, review, activity, final_exam
            $table->string('status')->default('scheduled')->after('type');          // scheduled, completed, rescheduled, cancelled
            $table->string('series_id')->nullable()->after('status');               // معرف السلسلة
            $table->string('series_status')->default('active')->after('series_id'); // active, completed

            // حقول الامتحانات
            $table->string('room')->nullable()->after('description');
            $table->integer('total_marks')->nullable()->after('room');
            $table->integer('duration')->nullable()->after('total_marks'); // بالدقائق

            // حقول التأجيل والإلغاء
            $table->text('cancellation_reason')->nullable()->after('duration');
            $table->timestamp('cancelled_at')->nullable()->after('cancellation_reason');
            $table->text('reschedule_reason')->nullable()->after('cancelled_at');
            $table->json('reschedule_old_data')->nullable()->after('reschedule_reason');
            $table->timestamp('rescheduled_at')->nullable()->after('reschedule_old_data');

            // إضافة subject_id إذا لم يكن موجود
            if (! Schema::hasColumn('lectures', 'subject_id')) {
                $table->foreignId('subject_id')->nullable()->constrained()->after('group_id');
            }
        });
    }

    public function down()
    {
        Schema::table('lectures', function (Blueprint $table) {
            $table->dropColumn([
                'type', 'status', 'series_id', 'series_status',
                'room', 'total_marks', 'duration',
                'cancellation_reason', 'cancelled_at',
                'reschedule_reason', 'reschedule_old_data', 'rescheduled_at',
            ]);

            if (Schema::hasColumn('lectures', 'subject_id')) {
                $table->dropForeign(['subject_id']);
                $table->dropColumn('subject_id');
            }
        });
    }
};