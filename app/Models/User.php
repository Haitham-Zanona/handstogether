<?php
// app/Models/User.php
namespace App\Models;

use App\Models\Student;
use App\Models\Teacher;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $fillable = [
        'name', 'email', 'password', 'is_active', 'role',
        'phone', 'national_id', 'birth_date', 'parent_id',

    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected $casts = [
        'is_active'         => 'boolean',
        'birth_date'        => 'date',
        'email_verified_at' => 'datetime',
        'password'          => 'hashed',
    ];

    // Relationships
    public function student()
    {
        return $this->hasOne(Student::class);
    }

    // Scope للمستخدمين النشطين
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function teacher()
    {
        return $this->hasOne(Teacher::class);
    }

    public function children()
    {
        return $this->hasMany(Student::class, 'parent_id');
    }

    // Role helpers
    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    public function isTeacher()
    {
        return $this->role === 'teacher';
    }

    public function isParent()
    {
        return $this->role === 'parent';
    }

    public function isStudent()
    {
        return $this->role === 'student';
    }

    // Get dashboard route based on role
    public function getDashboardRoute()
    {
        return match ($this->role) {
            'admin'   => route('admin.dashboard'),
            'teacher' => route('teacher.dashboard'),
            'parent'  => route('parent.dashboard'),
            'student' => route('student.dashboard'),
            default   => route('home')
        };
    }
}
