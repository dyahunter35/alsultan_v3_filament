<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Currency extends Model
{
    protected $fillable = ['name', 'code', 'symbol', 'exchange_rate'];

    public function balances()
    {
        return $this->hasMany(CurrencyBalance::class);
    }
}
