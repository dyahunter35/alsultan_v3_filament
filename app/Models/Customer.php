<?php

namespace App\Models;

use App\Casts\GuestCustomer;
use App\Enums\ExpenseGroup;
use App\Enums\ExpenseType;
use App\Traits\HasCustomerFinancialReport;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use App\Traits\HasFinancialRelations;

class Customer extends Model implements HasMedia
{
    use HasFactory;

    use SoftDeletes;
    use InteractsWithMedia;
    use HasFinancialRelations;
    use HasCustomerFinancialReport;

    protected $guarded = [];

    protected $casts = [
        'permanent' => ExpenseGroup::class,
    ];

    public function getNameAttribute($value): string
    {

        $permanentLabel = $this->permanent?->getLabel() ?? '';
        return ($this->permanent == ExpenseGroup::SALE) ? $value : $value . ($permanentLabel ? " ($permanentLabel)" : '');
    }

    /** 🔸 المصروفات التي دفعها */
    public function expensesAsPayer(): MorphMany
    {
        return $this->morphMany(Expense::class, 'payer');
    }

    /** 🔸 المصروفات التي استلمها */
    public function expensesAsBeneficiary(): MorphMany
    {
        return $this->morphMany(Expense::class, 'beneficiary');
    }

    /** 🔸 التوريدات التي نفذها */
    public function supplyings()
    {
        return $this->hasMany(Supplying::class);
    }

    public function sales()
    {
        return $this->hasMany(Order::class, 'customer_id');
    }
}
