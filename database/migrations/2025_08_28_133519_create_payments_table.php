<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
            $table->decimal('amount', 10, 2);
            $table->string('month'); // Format: YYYY-MM
            $table->enum('status', ['paid', 'unpaid', 'pending'])->default('pending');
            $table->date('due_date')->nullable();
            $table->date('paid_date')->nullable();
            $table->string('payment_method')->nullable(); // cash, bank_transfer, etc.
            $table->text('notes')->nullable();
            $table->timestamps();

            // Prevent duplicate payments for same student/month
            $table->unique(['student_id', 'month']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('payments');
    }
};
