<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    protected $fillable = [
        'user_id', 'job_title', 'hire_date', 'salary', 'account_type', 'account_number',
    ];

    protected $casts = [
        'hire_date' => 'date',
        'salary'    => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
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
