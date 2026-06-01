<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add plain index on student_id to back the FK (database-agnostic check)
        if (! Schema::hasIndex('payments', 'payments_student_id_fk_index')) {
            Schema::table('payments', fn(Blueprint $t) =>
                $t->index('student_id', 'payments_student_id_fk_index')
            );
        }

        // Drop old unique that prevented multiple payment types in the same month
        if (Schema::hasIndex('payments', 'payments_student_id_month_unique')) {
            Schema::table('payments', fn(Blueprint $t) =>
                $t->dropUnique('payments_student_id_month_unique')
            );
        }

        // New constraint: one payment of each type per student per month
        if (! Schema::hasIndex('payments', 'payments_student_month_type_unique')) {
            Schema::table('payments', fn(Blueprint $t) =>
                $t->unique(['student_id', 'month', 'type'], 'payments_student_month_type_unique')
            );
        }
    }

    public function down(): void
    {
        if (Schema::hasIndex('payments', 'payments_student_month_type_unique')) {
            Schema::table('payments', fn(Blueprint $t) =>
                $t->dropUnique('payments_student_month_type_unique')
            );
        }

        if (! Schema::hasIndex('payments', 'payments_student_id_month_unique')) {
            Schema::table('payments', fn(Blueprint $t) =>
                $t->unique(['student_id', 'month'], 'payments_student_id_month_unique')
            );
        }

        if (Schema::hasIndex('payments', 'payments_student_id_fk_index')) {
            Schema::table('payments', fn(Blueprint $t) =>
                $t->dropIndex('payments_student_id_fk_index')
            );
        }
    }
};
