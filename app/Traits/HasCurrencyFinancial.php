<?php

namespace App\Traits;

use App\Enums\CurrencyType;
use App\Models\Currency;
use App\Models\CurrencyBalance;
use App\Models\CurrencyTransaction;
use Illuminate\Support\Collection;

trait HasCurrencyFinancial
{

    public function currencyConversion()
    {
        return $this->morphMany(CurrencyTransaction::class, 'party')->where('type', CurrencyType::Convert);
    }

    public function currencyAsPayer()
    {
        return $this->morphMany(CurrencyTransaction::class, 'payer')->where('type', CurrencyType::SEND);
    }

    public function currencyAsParty()
    {
        return $this->morphMany(CurrencyTransaction::class, 'party')->where('type', CurrencyType::SEND);
    }

    public function currencyBalance()
    {
        return $this->morphMany(CurrencyBalance::class, 'owner');
    }

    public function currencyValue($currencyId)
    {
        return $this->currencyBalance->where('currency_id', $currencyId)->first()->amount ?? 0;
    }


    public function getNetCurrenciesAttribute(): Collection
    {
        return Currency::query()
            ->withSum(['transactionsAsPayer' => function ($q) {
                $q->where('type', \App\Enums\CurrencyType::SEND);
            }], 'amount')
            ->withSum(['transactionsAsParty' => function ($q) {
                $q->where('type', \App\Enums\CurrencyType::SEND);
            }], 'amount')
            ->get()
            ->map(function ($currency) {
                $sent = $currency->transactions_as_payer_sum_amount ?? 0;
                $received = $currency->transactions_as_party_sum_amount ?? 0;

                return [
                    'currency' => $currency->name,
                    'sent' => $sent,
                    'received' => $received,
                    'net' => $received - $sent,
                ];
            });
    }
}
