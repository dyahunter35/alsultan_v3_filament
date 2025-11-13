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

    public function getNetCurrenciesByDate(Carbon $date): Collection
    {
        // نضبط التاريخ ليكون نهاية اليوم المحدد لضمان شمول جميع حركات ذلك اليوم
        $cutOffDate = $date->endOfDay();

        return self::query() // استخدام self::query() لبدء الاستعلام من نموذج Currency
            ->withSum(['transactionsAsPayer' => function ($q) use ($cutOffDate) {
                // تصفية حسب التاريخ ونوع الحركة (SEND)
                $q->where('type', CurrencyType::SEND)
                    ->where('created_at', '<=', $cutOffDate);
            }], 'amount')
            ->withSum(['transactionsAsParty' => function ($q) use ($cutOffDate) {
                // تصفية حسب التاريخ ونوع الحركة (SEND)
                $q->where('type', CurrencyType::SEND)
                    ->where('created_at', '<=', $cutOffDate);
            }], 'amount')
            ->get()
            ->map(function ($currency) {
                $sent = $currency->transactions_as_payer_sum_amount ?? 0;
                $received = $currency->transactions_as_party_sum_amount ?? 0;

                return [
                    'currency_name' => $currency->name,
                    'sent' => $sent,
                    'received' => $received,
                    // صافي الرصيد حتى ذلك التاريخ
                    'net' => $received - $sent,
                ];
            });
    }
}
