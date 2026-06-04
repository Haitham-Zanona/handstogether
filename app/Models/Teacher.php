<?php
namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Teacher extends Model
{
    protected $fillable = [
        'user_id', 'specialization', 'specializations', 'account_type', 'account_number',
        'hire_date', 'salary',
    ];

    protected $casts = [
        'specializations' => 'array',
        'hire_date'       => 'date',
        'salary'          => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function attendances()
    {
        return $this->hasMany(TeacherAttendance::class);
    }

    public function lectures()
    {
        return $this->hasMany(Lecture::class);
    }

    // Direct assignment via pivot (used for staff management)
    public function assignedGroups()
    {
        return $this->belongsToMany(Group::class, 'teacher_group');
    }

    // Groups derived from lectures (legacy — kept for backward compat)
    public function groups()
    {
        return $this->hasManyThrough(Group::class, Lecture::class)->distinct();
    }

    // Returns a Builder (not a relation) — cannot be eager-loaded
    public function students()
    {
        return Student::whereHas('group.lectures', function ($query) {
            $query->where('teacher_id', $this->id);
        });
    }

    // Get today's lectures
    public function getTodayLectures()
    {
        return $this->lectures()
            ->whereDate('date', today())
            ->with(['group', 'attendance.student.user'])
            ->orderBy('start_time')
            ->get();
    }

    // Get weekly schedule
    public function getWeeklySchedule()
    {
        return $this->lectures()
            ->whereBetween('date', [now()->startOfWeek(), now()->endOfWeek()])
            ->with('group')
            ->orderBy('date')
            ->orderBy('start_time')
            ->get();
    }

    public function salaryPayments()
    {
        return $this->morphMany(SalaryPayment::class, 'payable');
    }

    // Calculates current 31-day cycle info. Pass $untilDate to override "today".
    public function getCurrentCycleInfo(?string $untilDate = null): array
    {
        if (!$this->hire_date || !$this->salary) {
            return [];
        }
        $daily      = round((float) $this->salary / 31, 4);
        $until      = $untilDate ? Carbon::parse($untilDate) : Carbon::now();
        $daysSince  = (int) $this->hire_date->diffInDays($until);
        $cycleStart = $this->hire_date->copy()->addDays((int) floor($daysSince / 31) * 31);
        $daysWorked = min((int) $cycleStart->diffInDays($until) + 1, 31);
        return [
            'daily_rate'      => $daily,
            'cycle_start'     => $cycleStart->toDateString(),
            'cycle_end'       => $cycleStart->copy()->addDays(30)->toDateString(),
            'days_worked'     => $daysWorked,
            'prorated_amount' => round($daily * $daysWorked, 2),
            'full_cycle_due'  => $daysWorked >= 31,
        ];
    }
}
