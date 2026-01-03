<?php

namespace App\Traits;

use App\Models\Expense;
use App\Models\Order;
use App\Models\Supplying;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasFinancials
{
    /* -----------------------------------------
     | علاقات العمليات
     |------------------------------------------*/

    // 1️⃣ كمستفيد (يزيد)
    public function expensesAsBeneficiary(): MorphMany
    {
        return $this->morphMany(Expense::class, 'beneficiary');
    }

    // 2️⃣ كدافع (ينقص)
    public function expensesAsPayer(): MorphMany
    {
        return $this->morphMany(Expense::class, 'payer');
    }

    // 3️⃣ كمندوب مسؤول عن العملية
    public function expensesAsRepresentative(): HasMany
    {
        return $this->hasMany(Expense::class, 'representative_id');
    }

    // 4️⃣ عمليات التوريد
    public function supplyingsAsRepresentative(): HasMany
    {
        return $this->hasMany(Supplying::class, 'representative_id');
    }

    // 5️⃣ عمليات المبيعات
    public function ordersAsRepresentative(): HasMany
    {
        return $this->hasMany(Order::class, 'representative_id');
    }

    /* -----------------------------------------
     | حسابات المندوب
     |------------------------------------------*/

    public function getTotalReceivedAttribute(): float
    {
        return (float) $this->expensesAsBeneficiary()->sum('total_amount');
    }

    public function getTotalPaidAttribute(): float
    {
        return (float) $this->expensesAsPayer()->sum('total_amount');
    }

    public function getTotalSupplyingsAttribute(): float
    {
        return (float) $this->supplyingsAsRepresentative()->sum('total_amount');
    }

    public function getTotalOrdersAttribute(): float
    {
        return (float) $this->ordersAsRepresentative()->sum('total');
    }

    /* -----------------------------------------
     | صافي حساب المندوب
     |------------------------------------------*/

    public function getNetBalanceAttribute(): float
    {
        return ($this->total_received + $this->total_orders)
            - ($this->total_paid + $this->total_supplyings);
    }

    /* -----------------------------------------
     | جميع عمليات المندوب مجمعة
     |------------------------------------------*/

    public function getRepresentativeOperationsAttribute()
    {
        return collect([
            'beneficiary_expenses' => $this->expensesAsBeneficiary()->get(),
            'payer_expenses' => $this->expensesAsPayer()->get(),
            'representative_expenses' => $this->expensesAsRepresentative()->get(),
            'supplyings' => $this->supplyingsAsRepresentative()->get(),
            'orders' => $this->ordersAsRepresentative()->get(),
        ]);
    }
}
