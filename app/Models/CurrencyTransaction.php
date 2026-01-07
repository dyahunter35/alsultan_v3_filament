<?php

namespace App\Models;

use App\Enums\CurrencyType;
use App\Services\CustomerService;
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
        'note',
        'truck_id',
        'type',
        'rate',
    ];

    protected $casts = [
        'type' => \App\Enums\CurrencyType::class,
    ];

    protected static function booted(): void
    {
        static::saved(function (self $tx) {
            \App\Models\CurrencyBalance::refreshBalances([
                [$tx->payer_type, $tx->payer_id],
                [$tx->party_type, $tx->party_id],
            ]);

            // \App\Models\Customer::
        });
        static::creating(function (self $tx) {

            /* $tx->total = $tx->rate ?? 1 * $tx->amount ?? 1;
            $tx->saveQuietly(); */
            // \App\Models\Customer::
        });

        static::updating(function (self $tx) {

            // \App\Models\Customer::
        });

        static::updated(function (self $tx) {
            \App\Models\CurrencyBalance::refreshBalances();
            /*  $tx->updateQuietly(
                 [
                     'total' => $tx->rate ?? 1 * $tx->amount ?? 1,
                 ]
             ); */
        });

        static::deleted(function (self $tx) {
            \App\Models\CurrencyBalance::refreshBalances([
                [$tx->payer_type, $tx->payer_id],
                [$tx->party_type, $tx->party_id],
            ]);
        });
    }

    /*
        protected static function booted(): void
        {

            static::created(function ($ct) {
                if ($ct->type == CurrencyType::SEND) {
                    CurrencyBalance::refreshAllBalances();
                } else {
                    app(CustomerService::class)->updateCustomersBalance();
                }
                $ct->total = $ct->rate ?? 1 * $ct->amount;
                $ct->saveQuietly();
            });

            // 🟡 When a stock history record is updated
            static::updated(function ($ct) {
                if ($ct->type == CurrencyType::SEND) {
                    CurrencyBalance::refreshAllBalances();
                } else {
                    app(CustomerService::class)->updateCustomersBalance();
                }
                $ct->total = $ct->rate ?? 1 * $ct->amount;
                $ct->saveQuietly();
            });

            // 🔴 When a stock history record is deleted
            static::deleted(function ($ct) {
                if ($ct->type == CurrencyType::SEND) {
                    CurrencyBalance::refreshAllBalances();
                } else {
                    app(CustomerService::class)->updateCustomersBalance();
                }
            });
        }
     */
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

    public function truck()
    {
        return $this->belongsTo(Truck::class);
    }
}
