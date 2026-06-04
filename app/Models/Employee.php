<?php

namespace App\Models;

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
}
