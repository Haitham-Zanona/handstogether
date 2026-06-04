<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('salary_payments', function (Blueprint $table) {
            $table->id();
            $table->morphs('payable');               // payable_type + payable_id
            $table->decimal('amount', 10, 2);
            $table->decimal('daily_rate', 10, 4);
            $table->unsignedTinyInteger('days_worked'); // max 31
            $table->date('cycle_start_date');
            $table->date('cycle_end_date');
            $table->date('payment_date');
            $table->string('payment_method', 50)->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('payment_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('salary_payments');
    }
};
