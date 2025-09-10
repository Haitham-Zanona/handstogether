<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    protected $fillable = [
        'user_id', 'parent_id', 'group_id', 'birth_date', 'admission_id',
    ];

    protected $casts = [
        'birth_date' => 'date',
    ];

    // العلاقات الموجودة (بدون تغيير)
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function admission()
    {
        return $this->belongsTo(Admission::class);
    }

    public function parent()
    {
        return $this->belongsTo(User::class, 'parent_id');
    }

    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    public function attendance()
    {
        return $this->hasMany(Attendance::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    // الدوال الموجودة (بدون تغيير)
    public function getAgeAttribute()
    {
        return $this->birth_date?->age;
    }

    public function getAttendancePercentage()
    {
        $totalLectures = $this->group?->lectures()->count() ?? 0;
        if ($totalLectures === 0) {
            return 0;
        }

        $presentCount = $this->attendance()
            ->where('status', 'present')
            ->count();

        return round(($presentCount / $totalLectures) * 100, 2);
    }

    public function getMonthlyAttendance($month = null)
    {
        $month = $month ?: now()->format('Y-m');

        return $this->attendance()
            ->whereHas('lecture', function ($query) use ($month) {
                $query->whereRaw("DATE_FORMAT(date, '%Y-%m') = ?", [$month]);
            })
            ->with('lecture')
            ->get()
            ->groupBy('status');
    }

    public function getCurrentMonthPaymentStatus()
    {
        return $this->payments()
            ->where('month', now()->format('Y-m'))
            ->first()?->status ?? 'unpaid';
    }

    public function getUpcomingLectures()
    {
        return $this->group?->lectures()
            ->where('date', '>=', today())
            ->with('teacher.user')
            ->orderBy('date')
            ->orderBy('start_time')
            ->limit(5)
            ->get() ?? collect();
    }

    // الدوال الجديدة المقترحة (اختيارية)
    public function canTransferTo(Group $group)
    {
        return $group->can_add_students &&
        $group->grade_level === $this->group?->grade_level;
    }

    public function transferTo(Group $newGroup)
    {
        if ($this->canTransferTo($newGroup)) {
            $oldGroup = $this->group;

            $this->update(['group_id' => $newGroup->id]);

            if ($oldGroup) {
                $oldGroup->decrement('students_count');
            }
            $newGroup->increment('students_count');

            return true;
        }
        return false;
    }

    public function leaveGroup()
    {
        if ($this->group) {
            $this->group->decrement('students_count');
            $this->update(['group_id' => null]);
            return true;
        }
        return false;
    }

    public function hasGroup()
    {
        return ! is_null($this->group_id);
    }

    public function getGroupNameAttribute()
    {
        return $this->group ? $this->group->full_name : 'غير منضم لمجموعة';
    }
}