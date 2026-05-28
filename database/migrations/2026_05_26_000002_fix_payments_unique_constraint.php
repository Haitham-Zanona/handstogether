<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add a plain index on student_id first so MySQL can still enforce the FK
        // after we drop the composite unique that was serving as the FK backing index.
        $hasFkIndex = !empty(DB::select(
            "SHOW INDEX FROM payments WHERE Key_name = 'payments_student_id_fk_index'"
        ));
        if (!$hasFkIndex) {
            Schema::table('payments', fn(Blueprint $t) => $t->index('student_id', 'payments_student_id_fk_index'));
        }

        // Drop old unique that prevents multiple payment types in the same month
        $hasOldUnique = !empty(DB::select(
            "SHOW INDEX FROM payments WHERE Key_name = 'payments_student_id_month_unique'"
        ));
        if ($hasOldUnique) {
            Schema::table('payments', fn(Blueprint $t) => $t->dropUnique('payments_student_id_month_unique'));
        }

        // Add new constraint: one payment of each type per student per month
        $hasNewUnique = !empty(DB::select(
            "SHOW INDEX FROM payments WHERE Key_name = 'payments_student_month_type_unique'"
        ));
        if (!$hasNewUnique) {
            Schema::table('payments', fn(Blueprint $t) => $t->unique(
                ['student_id', 'month', 'type'],
                'payments_student_month_type_unique'
            ));
        }
    }

    public function down(): void
    {
        $hasNewUnique = !empty(DB::select(
            "SHOW INDEX FROM payments WHERE Key_name = 'payments_student_month_type_unique'"
        ));
        if ($hasNewUnique) {
            Schema::table('payments', fn(Blueprint $t) => $t->dropUnique('payments_student_month_type_unique'));
        }

        $hasOldUnique = !empty(DB::select(
            "SHOW INDEX FROM payments WHERE Key_name = 'payments_student_id_month_unique'"
        ));
        if (!$hasOldUnique) {
            Schema::table('payments', fn(Blueprint $t) => $t->unique(['student_id', 'month'], 'payments_student_id_month_unique'));
        }

        $hasFkIndex = !empty(DB::select(
            "SHOW INDEX FROM payments WHERE Key_name = 'payments_student_id_fk_index'"
        ));
        if ($hasFkIndex) {
            Schema::table('payments', fn(Blueprint $t) => $t->dropIndex('payments_student_id_fk_index'));
        }
    }
};
