<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalaryPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'payer_id',
        'payment_date',
        'for_month',
        'base_salary',
        'transportation_allowance',
        'housing_allowance',
        'work_hours',
        'hourly_rate',
        'advances_deducted',
        'penalties',
        'incentives',
        'net_pay',
        'notes',
    ];

    protected $casts = [
        'payment_date' => 'date',
        'base_salary' => 'decimal:2',
        'transportation_allowance' => 'decimal:2',
        'housing_allowance' => 'decimal:2',
        'hourly_rate' => 'decimal:2',
        'advances_deducted' => 'decimal:2',
        'penalties' => 'decimal:2',
        'incentives' => 'decimal:2',
        'net_pay' => 'decimal:2',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'employee_id');
    }

    public function payer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'payer_id');
    }
}
