<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('teachers', function (Blueprint $table) {
            $table->json('specializations')->nullable()->after('specialization');
            $table->string('account_type')->nullable()->after('specializations');
            $table->string('account_number')->nullable()->after('account_type');
        });
    }

    public function down(): void
    {
        Schema::table('teachers', function (Blueprint $table) {
            $table->dropColumn(['specializations', 'account_type', 'account_number']);
        });
    }
};
