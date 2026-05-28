<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. تعديل جدول الانتساب
        Schema::table('admissions', function (Blueprint $table) {
            // إضافة عدد الدفعات
            if (! Schema::hasColumn('admissions', 'num_payments')) {
                $table->unsignedSmallInteger('num_payments')->default(10)->after('monthly_fee');
            }
            // حذف حقول فترة الاستحقاق القديمة
            if (Schema::hasColumn('admissions', 'payment_due_from')) {
                $table->dropColumn('payment_due_from');
            }
            if (Schema::hasColumn('admissions', 'payment_due_to')) {
                $table->dropColumn('payment_due_to');
            }
        });

        // 2. تعديل جدول الدفعات
        Schema::table('payments', function (Blueprint $table) {
            if (! Schema::hasColumn('payments', 'account_name')) {
                $table->string('account_name')->nullable()->after('payment_method');
            }
            if (! Schema::hasColumn('payments', 'type')) {
                $table->enum('type', ['monthly', 'educational_bundle', 'admission_fee'])
                    ->default('monthly')
                    ->after('status');
            }
        });

        // 3. جدول تذكيرات التواصل المالي
        if (! Schema::hasTable('payment_reminders')) {
            Schema::create('payment_reminders', function (Blueprint $table) {
                $table->id();
                $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
                $table->foreignId('payment_id')->nullable()->constrained('payments')->onDelete('set null');
                $table->dateTime('remind_at');
                $table->enum('status', ['pending', 'sent', 'cancelled'])->default('pending');
                $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
                $table->timestamps();

                $table->index(['status', 'remind_at']);
            });
        }
    }

    public function down(): void
    {
        Schema::table('admissions', function (Blueprint $table) {
            if (Schema::hasColumn('admissions', 'num_payments')) {
                $table->dropColumn('num_payments');
            }
            if (! Schema::hasColumn('admissions', 'payment_due_from')) {
                $table->date('payment_due_from')->nullable();
            }
            if (! Schema::hasColumn('admissions', 'payment_due_to')) {
                $table->date('payment_due_to')->nullable();
            }
        });

        Schema::table('payments', function (Blueprint $table) {
            if (Schema::hasColumn('payments', 'account_name')) {
                $table->dropColumn('account_name');
            }
            if (Schema::hasColumn('payments', 'type')) {
                $table->dropColumn('type');
            }
        });

        Schema::dropIfExists('payment_reminders');
    }
};
