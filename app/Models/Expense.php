<?php

namespace App\Models;

use App\Enums\ExpenseType;
use App\Enums\Payment;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Expense extends Model
{
    use SoftDeletes;
    protected $guarded = [];

    protected $casts = [
        'expense_type' => ExpenseType::class,
        'payment_method' => Payment::class,
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (auth()->check()) {
                $model->created_by = auth()->id();
            }
        });


        static::created(function ($expense) {
            app('App\Services\CustomerService')->updateCustomerBalance($expense->beneficiary_id);
            app('App\Services\CustomerService')->updateCustomerBalance($expense->payer_id);
        });
        static::updated(function ($expense) {
            app('App\Services\CustomerService')->updateCustomerBalance($expense->beneficiary_id);
            app('App\Services\CustomerService')->updateCustomerBalance($expense->payer_id);
        });
        static::deleted(function ($expense) {
            app('App\Services\CustomerService')->updateCustomerBalance($expense->beneficiary_id);
            app('App\Services\CustomerService')->updateCustomerBalance($expense->payer_id);
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
}
