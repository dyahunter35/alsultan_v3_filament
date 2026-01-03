<?php

namespace App\Traits;

use App\Models\Expense;
use App\Models\Order;
use App\Models\Supplying;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasCustomerFinancials
{
    /* -----------------------------------------
     | علاقات العمليات للعميل
     |------------------------------------------*/

    /** 🔸 المصروفات التي استفاد منها العميل */
    public function expensesAsBeneficiary(): MorphMany
    {
        return $this->morphMany(Expense::class, 'beneficiary');
    }

    /** 🔸 المصروفات التي دفعها العميل */
    public function expensesAsPayer(): MorphMany
    {
        return $this->morphMany(Expense::class, 'payer');
    }

    /** 🔸 المبيعات التي تمت لهذا العميل */
    public function sales(): HasMany
    {
        return $this->hasMany(Order::class, 'customer_id');
    }

    /** 🔸 التوريدات (إن وجدت) */
    public function supplyings(): HasMany
    {
        return $this->hasMany(Supplying::class, 'customer_id');
    }

    /* -----------------------------------------
     | الحسابات المالية
     |------------------------------------------*/

    /** 🔹 إجمالي ما استلمه العميل */
    public function getTotalReceivedAttribute(): float
    {
        return (float) $this->expensesAsBeneficiary()->sum('total_amount');
    }

    /** 🔹 إجمالي ما دفعه العميل */
    public function getTotalPaidAttribute(): float
    {
        return (float) $this->expensesAsPayer()->sum('total_amount');
    }

    /** 🔹 إجمالي مشتريات العميل (فواتير البيع) */
    public function getTotalOrdersAttribute(): float
    {
        return (float) $this->sales()->sum('total');
    }

    /** 🔹 إجمالي توريدات العميل */
    public function getTotalSupplyingsAttribute(): float
    {
        return (float) $this->suppLyings()->sum('total_amount');
    }

    /* -----------------------------------------
     | صافي حساب العميل مع الشركة
     |------------------------------------------*/

    /**
     * 🔹 الصافي = (مشتريات + استلامات) - (مدفوعات + توريدات)
     *
     * • موجب ⇒ العميل مَدين للشركة 💰
     * • سالب ⇒ الشركة مَدينة للعميل 🔄
     */
    public function getNetBalanceAttribute(): float
    {
        return ($this->total_orders + $this->total_received)
            - ($this->total_paid + $this->total_supplyings);
    }

    /* -----------------------------------------
     | جميع عمليات العميل مجمعة
     |------------------------------------------*/

    public function getCustomerOperationsAttribute()
    {
        return collect([
            'beneficiary_expenses' => $this->expensesAsBeneficiary()->get(),
            'payer_expenses' => $this->expensesAsPayer()->get(),
            'orders' => $this->sales()->get(),
            'supplyings' => $this->supplyings()->get(),
        ]);
    }
}
