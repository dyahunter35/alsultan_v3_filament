<?php

namespace App\Models;

use App\Casts\GuestCustomer;
use App\Enums\CurrencyType;
use App\Enums\ExpenseGroup;
use App\Enums\ExpenseType;
use App\Traits\HasCurrencyFinancial;
use App\Traits\HasCustomerFinancialReport;
use App\Traits\HasCustomerFinancials;
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
use App\Traits\HasLedger;

class Customer extends Model implements HasMedia
{
    use HasFactory;

    use SoftDeletes;
    use InteractsWithMedia;
    // use HasFinancialRelations;
    //use HasCustomerFinancialReport;
    use HasLedger;
    use HasCurrencyFinancial;
    use HasCustomerFinancials;

    protected $guarded = [];

    protected $casts = [
        'permanent' => ExpenseGroup::class,
    ];

    public function scopePer($query, $type)
    {
        return $query->where('permanent', $type);
    }


    public function getNameAttribute($value): string
    {

        $permanentLabel = $this->permanent?->getLabel() ?? '';
        return ($this->permanent == ExpenseGroup::SALE) ? $value : $value . ($permanentLabel ? " ($permanentLabel)" : '');
    }
}
