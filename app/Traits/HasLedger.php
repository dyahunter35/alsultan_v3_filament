<?php

namespace App\Traits;

use Illuminate\Support\Collection;
use Carbon\Carbon;

trait HasLedger
{
    /**
     * 🔹 كل التحركات المالية للعميل مع رصيد افتتاحي
     *
     * @param string|\DateTime|null $startDate
     * @param string|\DateTime|null $endDate
     */
    public function financialLedger($startDate = null, $endDate = null): Collection
    {
        $ledger = collect();
        $startDate = $startDate ? Carbon::parse($startDate) : null;
        $endDate   = $endDate ? Carbon::parse($endDate) : null;

        $relations = [
            'expensesAsPayer'       => ['in' => 0, 'out' => 'total_amount', 'desc' => 'دفع مصروف'],
            'expensesAsBeneficiary' => ['in' => 'total_amount', 'out' => 0, 'desc' => 'استلام مصروف'],
            'supplyings'            => ['in' => 0, 'out' => 'total_amount', 'desc' => 'توريد'],
            'sales'                 => ['in' => 'total', 'out' => 0, 'desc' => 'بيع'],
            'currencyConversion'    => ['in' => 0, 'out' => 'total', 'desc' => 'تحويل عملة']
        ];

        $openingBalance = 0;
        $transactions = collect();

        // 🔹 جلب كل التحركات مرة واحدة لكل علاقة
        foreach ($relations as $relation => $props) {
            $items = $this->$relation()
                ->when($endDate, fn($q) => $q->where('created_at', '<=', $endDate))
                ->get();

            $transactions[$relation] = $items;

            // حساب الرصيد الافتتاحي
            if ($startDate) {
                foreach ($items as $item) {
                    if ($item->created_at < $startDate) {
                        $openingBalance +=
                            (is_string($props['in']) ? ($item->{$props['in']} ?? 0) : $props['in']) -
                            (is_string($props['out']) ? ($item->{$props['out']} ?? 0) : $props['out']);
                    }
                }
            } else {
                // لو ما في startDate، كل التحركات تُعتبر في الفترة
                $openingBalance = 0;
            }
        }

        // 🔹 إضافة الرصيد الافتتاحي كبند أول
        $ledger->push([
            'type' => 'opening_balance',
            'date' => $startDate ?? now(),
            'description' => 'رصيد مرحل',
            'amount_in' => 0,
            'amount_out' => 0,
            'balance' => $openingBalance,
        ]);

        // 🔹 معالجة التحركات بعد startDate
        foreach ($relations as $relation => $props) {
            $transactions[$relation]->each(function ($item) use ($ledger, $props, $startDate, $relation) {
                if ($startDate && $item->created_at < $startDate) return;

                $ledger->push([
                    'type' => $relation,
                    'date' => $item->created_at,
                    'description' => $props['desc'] .
                        ($relation === 'currencyConversion' ? ' (' . optional($item->currency)->name . ')' : ''),
                    'amount_in' => is_string($props['in']) ? ($item->{$props['in']} ?? 0) : $props['in'],
                    'amount_out' => is_string($props['out']) ? ($item->{$props['out']} ?? 0) : $props['out'],
                ]);
            });
        }

        // 🔹 ترتيب حسب التاريخ وحساب الرصيد المتراكم
        $balance = $openingBalance;
        return $ledger
            ->sortBy('date')
            ->values()
            ->map(function ($item) use (&$balance) {
                $balance += $item['amount_in'] - $item['amount_out'];
                $item['balance'] = $balance;
                return $item;
            });
    }
}
