<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->tinyInteger('reminder_grace_days')->unsigned()->default(0)->after('notes');
            $table->timestamp('last_reminder_sent_at')->nullable()->after('reminder_grace_days');
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn(['reminder_grace_days', 'last_reminder_sent_at']);
        });
    }
};
