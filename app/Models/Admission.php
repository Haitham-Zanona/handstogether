<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Admission extends Model
{
    protected $fillable = ['student_name', 'parent_name', 'phone', 'status'];

    // Scope for specific status
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    // Get status in Arabic
    public function getStatusInArabicAttribute()
    {
        return match ($this->status) {
            'pending' => 'في الانتظار',
            'approved' => 'مقبول',
            'rejected' => 'مرفوض',
            default => 'غير محدد'
        };
    }

    // Convert admission to student and parent users
    public function convertToStudent($groupId = null)
    {
        if ($this->status !== 'pending') {
            throw new \Exception('يمكن قبول الطلبات في الانتظار فقط');
        }

        // Create parent user
        $parent = User::create([
            'name'     => $this->parent_name,
            'email'    => strtolower(str_replace(' ', '', $this->parent_name)) . '@academy.local',
            'password' => bcrypt('123456'), // Default password
            'role'     => 'parent',
            'phone'    => $this->phone,
        ]);

        // Create student user
        $studentUser = User::create([
            'name'     => $this->student_name,
            'email'    => strtolower(str_replace(' ', '', $this->student_name)) . '@academy.local',
            'password' => bcrypt('123456'), // Default password
            'role'     => 'student',
        ]);

        // Create student record
        $student = Student::create([
            'user_id'   => $studentUser->id,
            'parent_id' => $parent->id,
            'group_id'  => $groupId,
        ]);

        // Update admission status
        $this->update(['status' => 'approved']);

        return $student;
    }
}
