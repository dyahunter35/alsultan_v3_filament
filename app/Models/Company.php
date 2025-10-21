<?php

namespace App\Models;

use App\Enums\CompanyType;
use App\Enums\CurrencyOption;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Company extends Model
{
    use HasFactory;

    protected $guarded = [];


    protected $casts = [
        'default_currency' => CurrencyOption::class,
        'type' => CompanyType::class
    ];

    public function scopeCompany($query)
    {
        return $query->where('type', CompanyType::Company->value);
    }

    public function scopeContractor($query)
    {
        return $query->where('type', CompanyType::Contractor->value);
    }

    // Trucks where this company is the "company"
    public function trucksAsCompany()
    {
        return $this->hasMany(Truck::class, 'company_id');
    }

    // Trucks where this company is the "contractor"
    public function trucksAsContractor()
    {
        return $this->hasMany(Truck::class, 'contractor_id');
    }

    public function currencyTransactions()
    {
        return $this->morphMany(CurrencyTransaction::class, 'party');
    }
    public function expenses()
    {
        // through trucks
        return $this->hasManyThrough(Expense::class, Truck::class);
    }

    protected function firstName(): Attribute
    {
        return Attribute::make(
            // get: fn(string $value) => $this->trucks(),
            //set: fn (string $value) => strtolower($value),
        );
    }
}
