<?php

namespace App\Services;

use App\Models\Customer;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class CustomerService
{
    public function generateLedger(Customer $customer, ?string $startDate = null, ?string $endDate = null): Collection
    {
        $ledger = collect();
        $startDate = $startDate ? Carbon::parse($startDate)->startOfDay() : null;
        $endDate = $endDate ? Carbon::parse($endDate)->endOfDay() : null;

        $openingBalance = $this->calculateOpeningBalance($customer, $startDate);

        $ledger->push([
            'type' => 'opening_balance',
            'date' => $startDate ? $startDate->copy()->subSecond()->format('Y-m-d H:i') : now()->subDay()->format('Y-m-d H:i'),
            'description' => 'رصيد مرحل (Opening Balance)',
            'amount_in' => 0,
            'amount_out' => 0,
            'balance' => $openingBalance,
        ]);

        $rangeStart = $startDate;
        $rangeEnd = $endDate ?? ($startDate ? $startDate->copy()->endOfDay() : null);

        $transactions = collect();

        // تجميع كل الحركات
        $transactions = $transactions->merge($customer->expensesAsPayer()->when($rangeStart, fn($q) => $q->where('created_at', '>=', $rangeStart))->when($rangeEnd, fn($q) => $q->where('created_at', '<=', $rangeEnd))->get()->map(fn($e) => ['date' => $e->created_at, 'description' => 'دفع مصروف: ' . $e->notes, 'amount_in' => 0, 'amount_out' => $e->total_amount]));
        $transactions = $transactions->merge($customer->expensesAsBeneficiary()->when($rangeStart, fn($q) => $q->where('created_at', '>=', $rangeStart))->when($rangeEnd, fn($q) => $q->where('created_at', '<=', $rangeEnd))->get()->map(fn($e) => ['date' => $e->created_at, 'description' => 'استلام مصروف: ' . $e->notes, 'amount_in' => $e->total_amount, 'amount_out' => 0]));
        $transactions = $transactions->merge($customer->supplyings()->when($rangeStart, fn($q) => $q->where('created_at', '>=', $rangeStart))->when($rangeEnd, fn($q) => $q->where('created_at', '<=', $rangeEnd))->get()->map(fn($s) => ['date' => $s->created_at, 'description' => 'عملية توريد', 'amount_in' => 0, 'amount_out' => $s->total_amount]));
        $transactions = $transactions->merge($customer->sales()->when($rangeStart, fn($q) => $q->where('created_at', '>=', $rangeStart))->when($rangeEnd, fn($q) => $q->where('created_at', '<=', $rangeEnd))->get()->map(fn($o) => ['date' => $o->created_at, 'description' => 'عملية بيع رقم ' . $o->id, 'amount_in' => $o->total, 'amount_out' => 0]));

        // الترتيب بالوقت الفعلي لضمان تسلسل الرصيد
        $transactions = $transactions->sortBy('date')->values();

        $currentBalance = $openingBalance;
        foreach ($transactions as $t) {
            $currentBalance += ($t['amount_in'] - $t['amount_out']);
            $t['balance'] = $currentBalance;
            $t['date'] = $t['date']->format('Y-m-d H:i'); // تحويل للعرض بعد الحساب
            $ledger->push($t);
        }

        return $ledger;
    }

    public function calculateOpeningBalance(Customer $customer, ?string $startDate): float
    {
        if (!$startDate) return 0;
        $date = Carbon::parse($startDate)->startOfDay();

        $in = $customer->expensesAsBeneficiary()->where('created_at', '<', $date)->sum('total_amount') + $customer->sales()->where('created_at', '<', $date)->sum('total');
        $out = $customer->expensesAsPayer()->where('created_at', '<', $date)->sum('total_amount') + $customer->supplyings()->where('created_at', '<', $date)->sum('total_amount');

        return $in - $out;
    }

    public function updateCustomerBalance(Customer $customer): float
    {
        $balance = ($customer->expensesAsBeneficiary()->sum('total_amount') + $customer->sales()->sum('total')) - ($customer->expensesAsPayer()->sum('total_amount') + $customer->supplyings()->sum('total_amount'));
        $customer->update(['balance' => $balance]);
        return $balance;
    }
}
