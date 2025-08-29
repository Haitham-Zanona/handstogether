<?php
namespace Database\Seeders;

use App\Models\Payment;
use App\Models\Student;
use Illuminate\Database\Seeder;

class PaymentSeeder extends Seeder
{
    public function run()
    {
        $students = Student::all();

        // Create payments for the last 6 months
        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i)->format('Y-m');

            foreach ($students as $student) {
                $amount = rand(800, 1200); // Random amount between 800-1200

                // 80% chance of payment being made
                if (rand(1, 100) <= 80) {
                    $status   = 'paid';
                    $paidDate = now()->subMonths($i)->addDays(rand(1, 25));
                } else {
                    $status   = ['unpaid', 'pending'][rand(0, 1)];
                    $paidDate = null;
                }

                Payment::create([
                    'student_id'     => $student->id,
                    'amount'         => $amount,
                    'month'          => $month,
                    'status'         => $status,
                    'due_date'       => now()->subMonths($i)->endOfMonth(),
                    'paid_date'      => $paidDate,
                    'payment_method' => $status === 'paid' ? ['cash', 'bank_transfer'][rand(0, 1)] : null,
                ]);
            }
        }
    }
}
