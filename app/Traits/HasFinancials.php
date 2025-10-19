<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

trait HasFinancials
{
    /** 🔸 المصروفات التي دفعها المستخدم */
    public function expensesAsPayer(): MorphMany
    {
        return $this->morphMany(\App\Models\Expense::class, 'payer');
    }

    /** 🔸 المصروفات التي استلمها المستخدم */
    public function expensesAsBeneficiary(): MorphMany
    {
        return $this->morphMany(\App\Models\Expense::class, 'beneficiary');
    }

    /** 🔸 التوريدات التي كان المندوب مسؤول عنها */
    public function supplyingsAsRepresentative(): HasMany
    {
        return $this->hasMany(\App\Models\Supplying::class, 'representative_id');
    }

    /** 🔸 المبيعات التي كان المندوب مسؤول عنها */
    public function ordersAsRepresentative(): HasMany
    {
        return $this->hasMany(\App\Models\Order::class, 'representative_id');
    }

    /** 🔹 إجمالي المدفوعات كمُصرف */
    public function getTotalPaidAttribute(): float
    {
        return (float) $this->expensesAsPayer()->sum('total_amount');
    }

    /** 🔹 إجمالي الاستلامات كمستفيد */
    public function getTotalReceivedAttribute(): float
    {
        return (float) $this->expensesAsBeneficiary()->sum('total_amount');
    }

    /** 🔹 إجمالي التوريدات */
    public function getTotalSupplyingsAttribute(): float
    {
        return (float) $this->supplyingsAsRepresentative()->sum('total_amount');
    }

    /** 🔹 إجمالي المبيعات */
    public function getTotalOrdersAttribute(): float
    {
        return (float) $this->ordersAsRepresentative()->sum('total');
    }

    /** 🔹 الصافي النهائي للمندوب */
    public function getNetBalanceAttribute(): float
    {
        // زيادة المطالبات عند الاستلام أو المبيعات، ونقص عند الدفع والتوريدات
        return ($this->total_received + $this->total_orders) - ($this->total_paid + $this->total_supplyings);
    }
}
