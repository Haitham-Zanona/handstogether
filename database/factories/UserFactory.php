<?php
namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition()
    {
        return [
            'name'              => fake()->name(),
            'email'             => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password'          => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
            'role'              => fake()->randomElement(['admin', 'teacher', 'parent', 'student']),
            'phone'             => fake()->phoneNumber(),
            'remember_token'    => Str::random(10),
        ];
    }

    public function admin()
    {
        return $this->state(fn(array $attributes) => [
            'role' => 'admin',
        ]);
    }

    public function teacher()
    {
        return $this->state(fn(array $attributes) => [
            'role' => 'teacher',
        ]);
    }

    public function parent()
    {
        return $this->state(fn(array $attributes) => [
            'role' => 'parent',
        ]);
    }

    public function student()
    {
        return $this->state(fn(array $attributes) => [
            'role' => 'student',
        ]);
    }
}
