<?php

namespace App\Models;

use App\Enums\Payment;
use App\Enums\PaymentOptions;
use App\Models\ExpenseType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Expense extends Model
{
    use SoftDeletes;
    protected $guarded = [];

    protected $casts = [
        //'expense_type' => ExpenseType::class,
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

        static::creating(function ($model) {
            if (auth()->check()) {
                $model->created_by = auth()->id();
            }
        });

        /*  static::saving(function ($expense) {
            if ($expense->expense_type_id) {
                $expense->custom_expense_type = null;
            } elseif ($expense->custom_expense_type) {
                $expense->expense_type_id = null;
            }
        });

        static::created(function ($expense) {
            if ($expense->payer_type === 'App\Models\Customer') {
                app('App\Services\CustomerService')->updateCustomerBalance($expense->payer_id);
            }
            if ($expense->beneficiary_type === 'App\Models\Customer') {
                app('App\Services\CustomerService')->updateCustomerBalance($expense->beneficiary_id);
            }
        });
        static::updated(function ($expense) {
            if ($expense->payer_type === 'App\Models\Customer') {
                app('App\Services\CustomerService')->updateCustomerBalance($expense->payer_id);
            }
            if ($expense->beneficiary_type === 'App\Models\Customer') {
                app('App\Services\CustomerService')->updateCustomerBalance($expense->beneficiary_id);
            }
        });
        static::deleted(function ($expense) {
            if ($expense->payer_type === 'App\Models\Customer') {
                app('App\Services\CustomerService')->updateCustomerBalance($expense->payer_id);
            }
            if ($expense->beneficiary_type === 'App\Models\Customer') {
                app('App\Services\CustomerService')->updateCustomerBalance($expense->beneficiary_id);
            }
        }); */
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
