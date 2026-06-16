<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'pgsql') {
            // PostgreSQL requires explicit USING clause to cast varchar → bigint
            DB::statement('ALTER TABLE lectures ALTER COLUMN series_id TYPE bigint USING series_id::bigint');
        } else {
            Schema::table('lectures', function (Blueprint $table) {
                $table->unsignedBigInteger('series_id')->nullable()->change();
            });
        }
    }

    public function down(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'pgsql') {
            DB::statement('ALTER TABLE lectures ALTER COLUMN series_id TYPE varchar(255) USING series_id::varchar');
        } else {
            Schema::table('lectures', function (Blueprint $table) {
                $table->string('series_id')->nullable()->change();
            });
        }
    }
};
