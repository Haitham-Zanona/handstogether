<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Expense extends Model
{
    protected $fillable = [
        'category', 'description', 'amount',
        'expense_date', 'notes', 'created_by',
    ];

    protected $casts = [
        'amount'       => 'decimal:2',
        'expense_date' => 'date',
    ];

    public static array $defaultCategories = [
        'كهرباء', 'ماء', 'إيجار', 'صيانة', 'مواد مكتبية', 'أخرى',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeByMonth(Builder $query, int $year, int $month): Builder
    {
        return $query->whereYear('expense_date', $year)->whereMonth('expense_date', $month);
    }
}
