<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    protected $fillable = [
        'user_id', 'parent_id', 'group_id', 'birth_date',
    ];

    protected $casts = [
        'birth_date' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
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

    // Get age
    public function getAgeAttribute()
    {
        return $this->birth_date?->age;
    }

    // Get attendance percentage
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

    // Get monthly attendance for specific month
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

    // Get payment status for current month
    public function getCurrentMonthPaymentStatus()
    {
        return $this->payments()
            ->where('month', now()->format('Y-m'))
            ->first()?->status ?? 'unpaid';
    }

    // Get upcoming lectures
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
}
