<?php

namespace App\Models;

use App\Enums\CompanyType;
use App\Enums\CurrencyOption;
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

    public function trucks(): HasMany {
        return $this->hasMany(Truck::class);
    }

    
}
