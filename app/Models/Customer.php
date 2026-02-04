<?php

namespace App\Models;

use App\Enums\ExpenseGroup;
use App\Traits\HasCurrencyFinancial;
use App\Traits\HasCustomerFinancialReport;
use App\Traits\HasCustomerFinancials;
use App\Traits\HasFinancialRelations;
use App\Traits\HasLedger;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Customer extends Model implements HasMedia
{
    use HasCurrencyFinancial;
    use HasCustomerFinancials;
    use HasFactory;

    // use HasFinancialRelations;
    // use HasCustomerFinancialReport;
    use HasLedger;
    use InteractsWithMedia;
    use SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        'permanent' => ExpenseGroup::class,
    ];

    public function scopePer($query, $type)
    {
        return $query->where('permanent', $type);
    }

    public function scopeSale($query)
    {
        return $query->where('permanent', ExpenseGroup::SALE);
    }

    public function getNameAttribute($value): string
    {

        $permanentLabel = $this->permanent?->getLabel() ?? '';

        return ($this->permanent == ExpenseGroup::SALE) ? $value : $value . ($permanentLabel ? " ($permanentLabel)" : '');
    }
}
