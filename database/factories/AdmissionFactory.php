<?php
namespace Database\Factories;

use App\Models\Admission;
use Illuminate\Database\Eloquent\Factories\Factory;

class AdmissionFactory extends Factory
{
    protected $model = Admission::class;

    public function definition()
    {

        $studentId = $this->generateUniqueId();
        $parentId  = $this->generateUniqueId();

// تأكد أن رقم الطالب مختلف عن الوالد
        while ($parentId === $studentId) {
            $parentId = $this->generateUniqueId();
        }

        return [
            'student_name'       => fake('ar_SA')->name(),
            'parent_name'        => fake('ar_SA')->name(),
            'phone'              => '+970-599-' . fake()->numberBetween(100000, 999999),
            'email'              => fake()->safeEmail(),
            'student_birth_date' => fake()->dateTimeBetween('-20 years', '-16 years'),
            'address'            => fake('ar_SA')->address(),
            'status'             => fake()->randomElement(['pending', 'approved', 'rejected']),
            'notes'              => fake()->optional(0.3)->text(100),
        ];
    }

    public function pending()
    {
        return $this->state(fn(array $attributes) => [
            'status' => 'pending',
        ]);
    }

    public function approved()
    {
        return $this->state(fn(array $attributes) => [
            'status'      => 'approved',
            'reviewed_at' => fake()->dateTimeBetween('-1 month', 'now'),
        ]);
    }

    public function rejected()
    {
        return $this->state(fn(array $attributes) => [
            'status'      => 'rejected',
            'reviewed_at' => fake()->dateTimeBetween('-1 month', 'now'),
            'notes'       => 'تم الرفض لأسباب إدارية',
        ]);
    }

    public function generateUniqueId(): string
    {
        do {
            // توليد رقم عشوائي من 9 أرقام
            $id = str_pad(random_int(100000000, 999999999), 9, '0', STR_PAD_LEFT);
        } while (
            Admission::where('student_id', $id)->exists() ||
            Admission::where('parent_id', $id)->exists()
        );

        return $id;
    }
}
