<?php

namespace App\Traits;

use Illuminate\Support\Collection;

trait HasCustomerFinancialReport
{
    /**
     * 🔹 كل التحركات المالية للعميل بدءًا من تاريخ محدد مع رصيد افتتاحي
     *
     * @param  \DateTime|string|null  $startDate  تاريخ البداية، لو null يعرض كل التاريخ
     */
    public function financialLedgerFrom($startDate = null): Collection
    {
        $ledger = collect();

        // تحويل startDate إلى Carbon
        $startDate = $startDate ? \Carbon\Carbon::parse($startDate) : null;

        // 🔹 أولًا نحسب الرصيد المرحل قبل التاريخ المحدد
        $openingBalance = 0;

        $this->expensesAsPayer()->when($startDate, fn($q) => $q->where('created_at', '<', $startDate))
            ->get()
            ->each(fn($expense) => $openingBalance -= $expense->total_amount);

        $this->expensesAsBeneficiary()->when($startDate, fn($q) => $q->where('created_at', '<', $startDate))
            ->get()
            ->each(fn($expense) => $openingBalance += $expense->total_amount);

        $this->supplyings()->when($startDate, fn($q) => $q->where('created_at', '<', $startDate))
            ->get()
            ->each(fn($supply) => $openingBalance -= $supply->total_amount);

        $this->sales()->when($startDate, fn($q) => $q->where('created_at', '<', $startDate))
            ->get()
            ->each(fn($order) => $openingBalance += $order->total);

        // إضافة الرصيد المرحل كأول بند
        $ledger->push([
            'type' => 'opening_balance',
            'date' => $startDate ?? now(),
            'description' => 'رصيد مرحل',
            'amount_in' => 0,
            'amount_out' => 0,
            'balance' => $openingBalance,
        ]);

        // 🔹 بعدها نجيب كل التحركات بدءًا من startDate
        $this->expensesAsPayer()->when($startDate, fn($q) => $q->where('created_at', '>=', $startDate))
            ->get()
            ->each(fn($expense) => $ledger->push([
                'type' => 'expense_paid',
                'date' => $expense->created_at,
                'description' => 'دفع مصروف',
                'amount_in' => 0,
                'amount_out' => $expense->total_amount,
            ]));

        $this->expensesAsBeneficiary()->when($startDate, fn($q) => $q->where('created_at', '>=', $startDate))
            ->get()
            ->each(fn($expense) => $ledger->push([
                'type' => 'expense_received',
                'date' => $expense->created_at,
                'description' => 'استلام مصروف',
                'amount_in' => $expense->total_amount,
                'amount_out' => 0,
            ]));

        $this->supplyings()->when($startDate, fn($q) => $q->where('created_at', '>=', $startDate))
            ->get()
            ->each(fn($supply) => $ledger->push([
                'type' => 'supplying',
                'date' => $supply->created_at,
                'description' => 'توريد',
                'amount_in' => 0,
                'amount_out' => $supply->total_amount,
            ]));

        $this->sales()->when($startDate, fn($q) => $q->where('created_at', '>=', $startDate))
            ->get()
            ->each(fn($order) => $ledger->push([
                'type' => 'sale',
                'date' => $order->created_at,
                'description' => 'بيع',
                'amount_in' => $order->total,
                'amount_out' => 0,
            ]));

        // ترتيب حسب التاريخ
        $ledger = $ledger->sortBy('date')->values();

        // حساب الرصيد المتراكم
        $balance = $openingBalance;
        $ledger = $ledger->map(function ($item) use (&$balance) {
            $balance += $item['amount_in'] - $item['amount_out'];
            $item['balance'] = $balance;

            return $item;
        });

        return $ledger;
    }

    /**
     * 🔹 كل التحركات المالية للعميل بين تاريخين مع رصيد افتتاحي
     *
     * @param  \DateTime|string|null  $startDate
     * @param  \DateTime|string|null  $endDate
     * @return \Illuminate\Support\Collection
     */
    public function financialLedgerFromTo($startDate = null, $endDate = null)
    {
        $ledger = collect();

        $startDate = $startDate ? \Carbon\Carbon::parse($startDate) : null;
        $endDate = $endDate ? \Carbon\Carbon::parse($endDate) : null;

        // حساب الرصيد المرحل قبل الفترة
        $openingBalance = 0;

        $this->expensesAsPayer()->when($startDate, fn($q) => $q->where('created_at', '<', $startDate))
            ->get()
            ->each(fn($expense) => $openingBalance -= $expense->total_amount);

        $this->expensesAsBeneficiary()->when($startDate, fn($q) => $q->where('created_at', '<', $startDate))
            ->get()
            ->each(fn($expense) => $openingBalance += $expense->total_amount);

        $this->supplyings()->when($startDate, fn($q) => $q->where('created_at', '<', $startDate))
            ->get()
            ->each(fn($supply) => $openingBalance -= $supply->total_amount);

        $this->sales()->when($startDate, fn($q) => $q->where('created_at', '<', $startDate))
            ->get()
            ->each(fn($order) => $openingBalance += $order->total);

        // إضافة الرصيد المرحل كبند أول
        $ledger->push([
            'type' => 'opening_balance',
            'date' => $startDate ?? now(),
            'description' => 'رصيد مرحل',
            'amount_in' => 0,
            'amount_out' => 0,
            'balance' => $openingBalance,
        ]);

        // جلب كل التحركات بين startDate و endDate
        $this->expensesAsPayer()->when($startDate, fn($q) => $q->where('created_at', '>=', $startDate))
            ->when($endDate, fn($q) => $q->where('created_at', '<=', $endDate))
            ->get()
            ->each(fn($expense) => $ledger->push([
                'type' => 'expense_paid',
                'date' => $expense->created_at,
                'description' => 'دفع مصروف',
                'amount_in' => 0,
                'amount_out' => $expense->total_amount,
            ]));

        $this->expensesAsBeneficiary()->when($startDate, fn($q) => $q->where('created_at', '>=', $startDate))
            ->when($endDate, fn($q) => $q->where('created_at', '<=', $endDate))
            ->get()
            ->each(fn($expense) => $ledger->push([
                'type' => 'expense_received',
                'date' => $expense->created_at,
                'description' => 'استلام مصروف',
                'amount_in' => $expense->total_amount,
                'amount_out' => 0,
            ]));

        $this->supplyings()->when($startDate, fn($q) => $q->where('created_at', '>=', $startDate))
            ->when($endDate, fn($q) => $q->where('created_at', '<=', $endDate))
            ->get()
            ->each(fn($supply) => $ledger->push([
                'type' => 'supplying',
                'date' => $supply->created_at,
                'description' => 'توريد',
                'amount_in' => 0,
                'amount_out' => $supply->total_amount,
            ]));

        $this->sales()->when($startDate, fn($q) => $q->where('created_at', '>=', $startDate))
            ->when($endDate, fn($q) => $q->where('created_at', '<=', $endDate))
            ->get()
            ->each(fn($order) => $ledger->push([
                'type' => 'sale',
                'date' => $order->created_at,
                'description' => 'بيع',
                'amount_in' => $order->total,
                'amount_out' => 0,
            ]));

        // ترتيب حسب التاريخ
        $ledger = $ledger->sortBy('date')->values();

        // حساب الرصيد المتراكم
        $balance = $openingBalance;
        $ledger = $ledger->map(function ($item) use (&$balance) {
            $balance += $item['amount_in'] - $item['amount_out'];
            $item['balance'] = $balance;

            return $item;
        });

        return $ledger;
    }


    public function getCurrencyNetBalanceAttribute(): float
    {
        return ($this->total_orders + $this->total_received)
            - ($this->total_paid + $this->total_supplyings);
    }
}
