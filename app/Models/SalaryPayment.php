<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalaryPayment extends Model
{
    protected $fillable = [
        'payable_type', 'payable_id',
        'amount', 'daily_rate', 'days_worked',
        'cycle_start_date', 'cycle_end_date', 'payment_date',
        'payment_method', 'notes', 'created_by',
    ];

    protected $casts = [
        'amount'           => 'decimal:2',
        'daily_rate'       => 'decimal:4',
        'cycle_start_date' => 'date',
        'cycle_end_date'   => 'date',
        'payment_date'     => 'date',
    ];

    public function payable()
    {
        return $this->morphTo();
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getPayableNameAttribute(): string
    {
        $user = $this->payable?->user;
        return $user?->name ?? '—';
    }

    public function getPayableRoleAttribute(): string
    {
        return match ($this->payable_type) {
            'App\\Models\\Teacher'  => 'مدرس',
            'App\\Models\\Employee' => 'موظف',
            default                 => '—',
        };
    }
}
