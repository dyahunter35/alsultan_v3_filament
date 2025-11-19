<?php

namespace App\Traits;

use App\Enums\CurrencyType;
use App\Models\Currency;
use Carbon\Carbon;
use Illuminate\Support\Collection;

trait HasCurrencyFinancial
{
    /** تحويلات العملات */
    public function currencyConversion()
    {
        return $this->morphMany(\App\Models\CurrencyTransaction::class, 'party')
            ->where('type', CurrencyType::Convert);
    }
    public function currencyBalance()
    {
        return $this->morphMany(\App\Models\CurrencyBalance::class, 'owner');
    }

    /** إرسال عملة (خصم) */
    public function currencyAsPayer()
    {
        return $this->morphMany(\App\Models\CurrencyTransaction::class, 'payer')
            ->where('type', CurrencyType::SEND);
    }

    /** استلام عملة (زيادة) */
    public function currencyAsParty()
    {
        return $this->morphMany(\App\Models\CurrencyTransaction::class, 'party')
            ->where('type', CurrencyType::SEND);
    }

    /** رصيد عملة محددة */
    public function currencyValue($currencyId)
    {
        return $this->currencyBalance->where('currency_id', $currencyId)->first()->amount ?? 0;
    }

    /**
     * 🔥 صافي العملات لكل عملة
     */
    public function getNetCurrenciesAttribute(): Collection
    {
        return Currency::query()
            ->get()
            ->map(function ($currency) {

                $sent = $this->currencyAsPayer()
                    ->where('currency_id', $currency->id)
                    ->sum('amount');

                $received = $this->currencyAsParty()
                    ->where('currency_id', $currency->id)
                    ->sum('amount');

                $converted = $this->currencyConversion()
                    ->where('currency_id', $currency->id)
                    ->sum('total');

                return [
                    'currency' => $currency->name,
                    'sent' => $sent,
                    'received' => $received,
                    'converted' => $converted,
                    'net' => ($received - $sent - $converted),
                ];
            });
    }

    /**
     * 🔥 الرصيد حسب تاريخ محدد
     */
    public function getNetCurrenciesByDate(Carbon $date): Collection
    {
        $cut = $date->endOfDay();

        return Currency::query()
            ->get()
            ->map(function ($currency) use ($cut) {

                $sent = $this->currencyAsPayer()
                    ->where('currency_id', $currency->id)
                    ->where('created_at', '<=', $cut)
                    ->sum('amount');

                $received = $this->currencyAsParty()
                    ->where('currency_id', $currency->id)
                    ->where('created_at', '<=', $cut)
                    ->sum('amount');

                $converted = $this->currencyConversion()
                    ->where('currency_id', $currency->id)
                    ->where('created_at', '<=', $cut)
                    ->sum('total');

                return [
                    'currency' => $currency->name,
                    'sent' => $sent,
                    'received' => $received,
                    'converted' => $converted,
                    'net' => ($received - $sent - $converted),
                ];
            });
    }
}
