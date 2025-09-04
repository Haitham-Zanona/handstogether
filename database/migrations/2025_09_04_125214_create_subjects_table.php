<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('subjects', function (Blueprint $table) {
            $table->id();
            $table->string('name');                    // اسم المادة
            $table->string('grade_level')->nullable(); // المرحلة المرتبطة (اختياري)
            $table->timestamps();

        });

        // إدخال المواد الأساسية
        DB::table('subjects')->insert([
            ['name' => 'اللغة إنجليزية'],
            ['name' => 'اللغة عربية'],
            ['name' => 'الرياضيات'],
            ['name' => 'العلوم'],
        ]);

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subjects');
    }
};
