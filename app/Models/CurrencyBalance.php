<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CurrencyBalance extends Model
{
    protected $fillable = [
        'owner_id',
        'owner_type',
        'currency_id',
        'amount',
        'total_in_sdg'
    ];

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }

    public function owner()
    {
        return $this->morphTo();
    }

    public function updateTotal()
    {
        $this->total_in_sdg = $this->amount * $this->currency->exchange_rate;
        $this->save();
    }
}
