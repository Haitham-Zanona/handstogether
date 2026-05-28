<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'student_id', 'amount', 'month', 'status', 'type',
        'due_date', 'paid_date', 'payment_method', 'account_name', 'notes',
        'reminder_grace_days', 'last_reminder_sent_at',
    ];

    protected $casts = [
        'due_date'              => 'date',
        'paid_date'             => 'date',
        'amount'                => 'decimal:2',
        'last_reminder_sent_at' => 'datetime',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    // Scope for specific status
    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    public function scopeUnpaid($query)
    {
        return $query->where('status', 'unpaid');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    // Get formatted month
    public function getFormattedMonthAttribute()
    {
        return \Carbon\Carbon::createFromFormat('Y-m', $this->month)->format('F Y');
    }

    // Check if payment is overdue
    public function getIsOverdueAttribute()
    {
        if ($this->status === 'paid') {
            return false;
        }

        $paymentMonth = \Carbon\Carbon::createFromFormat('Y-m', $this->month);
        return $paymentMonth->isPast();
    }

    // Get status in Arabic
    public function getStatusInArabicAttribute()
    {
        return match ($this->status) {
            'paid' => 'مدفوع',
            'unpaid' => 'غير مدفوع',
            'pending' => 'في الانتظار',
            default => 'غير محدد'
        };
    }
}
