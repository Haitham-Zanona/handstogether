<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key', 100)->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });

        DB::table('settings')->insert([
            ['key' => 'academy_name',           'value' => 'الأكاديمية التعليمية',           'created_at' => now(), 'updated_at' => now()],
            ['key' => 'academy_email',          'value' => 'info@academy.edu',                'created_at' => now(), 'updated_at' => now()],
            ['key' => 'academy_phone',          'value' => '+970-599-123456',                 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'academy_address',        'value' => 'نابلس، فلسطين',                   'created_at' => now(), 'updated_at' => now()],
            ['key' => 'monthly_fee',            'value' => '1000',                            'created_at' => now(), 'updated_at' => now()],
            ['key' => 'working_hours',          'value' => 'الأحد - الخميس: 8:00 ص - 4:00 م', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'academic_year_end_date', 'value' => null,                              'created_at' => now(), 'updated_at' => now()],
            ['key' => 'auto_notifications',     'value' => '1',                               'created_at' => now(), 'updated_at' => now()],
            ['key' => 'payment_reminder',       'value' => '1',                               'created_at' => now(), 'updated_at' => now()],
            ['key' => 'attendance_reminder',    'value' => '1',                               'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
