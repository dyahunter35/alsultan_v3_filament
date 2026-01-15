<?php

namespace App\Models;

use App\Enums\PaymentOptions;
use App\Services\CustomerService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Expense extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        'payment_method' => PaymentOptions::class,
    ];

    public function scopeTypes($query, $type)
    {
        return $query->whereIn(
            'expense_type_id',
            ExpenseType::where('group', $type)->pluck('id')
        );
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($expense) {
            if (auth()->check()) {
                $expense->created_by = auth()->id();
            }
            $expense->total_amount = $expense->amount ?? 0
                * $expense->unit_price ?? 1;
            // $expense->saveQuietly();
        });
        static::created(function ($expense) {
            app(CustomerService::class)->updateCustomersBalance();

        });
        static::updating(function ($expense) {
            $expense->total_amount = $expense->amount ?? 0 * $expense->unit_price ?? 1;
        });
        static::updated(function ($expense) {
            app(CustomerService::class)->updateCustomersBalance();

        });
        static::deleted(function ($expense) {
            app(CustomerService::class)->updateCustomersBalance();
        });
    }

    public function beneficiary()
    {
        return $this->morphTo();
    }

    public function payer()
    {
        return $this->morphTo();
    }

    public function representative()
    {
        return $this->belongsTo(User::class, 'representative_id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function truck()
    {
        return $this->belongsTo(Truck::class);
    }

    public function type()
    {
        return $this->belongsTo(ExpenseType::class, 'expense_type_id');
    }
}
