<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasFinancialRelations
{
    /** 🔸 المصروفات التي دفعها العميل */
    public function expensesAsPayer(): MorphMany
    {
        return $this->morphMany(\App\Models\Expense::class, 'payer');
    }

    /** 🔸 المصروفات التي استلمها العميل */
    public function expensesAsBeneficiary(): MorphMany
    {
        return $this->morphMany(\App\Models\Expense::class, 'beneficiary');
    }

    /** 🔸 التوريدات التي قام بها العميل */
    public function supplyings()
    {
        return $this->hasMany(\App\Models\Supplying::class);
    }

    /** 🔸 المبيعات الخاصة بالعميل */
    public function sales()
    {
        return $this->hasMany(\App\Models\Order::class, 'customer_id');
    }

    /** 🔹 إجمالي المدفوعات */
    public function getTotalPaidAttribute(): float
    {
        return (float) $this->expensesAsPayer()->sum('total_amount');
    }

    /** 🔹 إجمالي الاستلامات */
    public function getTotalReceivedAttribute(): float
    {
        return (float) $this->expensesAsBeneficiary()->sum('total_amount');
    }

    /** 🔹 إجمالي التوريدات */
    public function getTotalSupplyingsAttribute(): float
    {
        return (float) $this->supplyings()->sum('total_amount');
    }

    /** 🔹 إجمالي المبيعات */
    public function getTotalSalesAttribute(): float
    {
        return (float) $this->sales()->sum('total');
    }

    /** 🔹 الصافي النهائي للعميل */
    public function getNetBalanceAttribute(): float
    {
        // زيادة المطالبات عند شراء، ونقص عند دفع المصروفات أو التوريدات
        return ($this->total_sales) - ($this->total_paid + $this->total_supplyings);
    }
}
