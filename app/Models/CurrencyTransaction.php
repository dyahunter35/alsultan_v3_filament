<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CurrencyTransaction extends Model
{
    use SoftDeletes;
    //
    protected $fillable = [
        'currency_id',
        'party_type',
        'party_id',
        'payer_type',
        'payer_id',
        'amount',
        'total',
        'transaction_type',
        'notes',
    ];

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }

    public function party()
    {
        return $this->morphTo();
    }

    public function payer()
    {
        return $this->morphTo();
    }
}
