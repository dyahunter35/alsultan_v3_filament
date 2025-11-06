<?php

namespace App\Services;

use App\Models\Customer;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class CustomerService
{
    /**
     * 🔹 توليد تقرير مالي كامل للعميل
     */
    public function generateLedger(Customer $customer, ?string $startDate = null, ?string $endDate = null): Collection
    {
        $ledger = collect();

        $startDate = $startDate ? Carbon::parse($startDate)->startOfDay() : null;
        $endDate   = $endDate ? Carbon::parse($endDate)->endOfDay() : null;

        // ✅ الرصيد المرحل قبل startDate
        $openingBalance = $this->calculateOpeningBalance($customer, $startDate);

        $ledger->push([
            'type' => 'opening_balance',
            'date' => $startDate?->copy()->subDay() ?? Carbon::now()->subDay(),
            'description' => 'رصيد مرحل',
            'amount_in' => 0,
            'amount_out' => 0,
            'balance' => $openingBalance,
        ]);

        // ✅ نحدد الفترة المطلوبة:
        if ($startDate && !$endDate) {
            // في حالة تحديد startDate فقط ⇒ نجيب اليوم المحدد فقط
            $rangeStart = $startDate;
            $rangeEnd   = $startDate->copy()->endOfDay();
        } elseif ($startDate && $endDate) {
            // في حالة تحديد تاريخين ⇒ نجيب المدى بينهما
            $rangeStart = $startDate;
            $rangeEnd   = $endDate;
        } else {
            // بدون تواريخ ⇒ كل السجل
            $rangeStart = null;
            $rangeEnd   = null;
        }

        // 🔹 كل الحركات المالية
        $transactions = collect();

        // مصروفات دافع
        $transactions = $transactions->merge(
            $customer->expensesAsPayer()
                ->when($rangeStart, fn($q) => $q->where('created_at', '>=', $rangeStart))
                ->when($rangeEnd, fn($q) => $q->where('created_at', '<=', $rangeEnd))
                ->get()
                ->map(fn($e) => [
                    'type' => 'expense_paid',
                    'date' => $e->created_at?->format('Y-m-d'),
                    'description' => 'دفع مصروف',
                    'amount_in' => 0,
                    'amount_out' => $e->total_amount,
                ])
        );

        // مصروفات مستلمة
        $transactions = $transactions->merge(
            $customer->expensesAsBeneficiary()
                ->when($rangeStart, fn($q) => $q->where('created_at', '>=', $rangeStart))
                ->when($rangeEnd, fn($q) => $q->where('created_at', '<=', $rangeEnd))
                ->get()
                ->map(fn($e) => [
                    'type' => 'expense_received',
                    'date' => $e->created_at?->format('Y-m-d'),
                    'description' => 'استلام مصروف',
                    'amount_in' => $e->total_amount,
                    'amount_out' => 0,
                ])
        );

        // التوريدات
        $transactions = $transactions->merge(
            $customer->supplyings()
                ->when($rangeStart, fn($q) => $q->where('created_at', '>=', $rangeStart))
                ->when($rangeEnd, fn($q) => $q->where('created_at', '<=', $rangeEnd))
                ->get()
                ->map(fn($s) => [
                    'type' => 'supplying',
                    'date' => $s->created_at?->format('Y-m-d'),
                    'description' => 'توريد',
                    'amount_in' => 0,
                    'amount_out' => $s->total_amount,
                ])
        );

        // المبيعات
        $transactions = $transactions->merge(
            $customer->sales()
                ->when($rangeStart, fn($q) => $q->where('created_at', '>=', $rangeStart))
                ->when($rangeEnd, fn($q) => $q->where('created_at', '<=', $rangeEnd))
                ->get()
                ->map(fn($o) => [
                    'type' => 'sale',
                    'date' => $o->created_at?->format('Y-m-d'),
                    'description' => 'بيع',
                    'amount_in' => $o->total,
                    'amount_out' => 0,
                ])
        );

        // ✅ ترتيب حسب التاريخ
        $transactions = $transactions->sortBy('date')->values();

        // ✅ حساب الرصيد المتراكم
        $balance = $openingBalance;
        foreach ($transactions as $t) {
            $balance += $t['amount_in'] - $t['amount_out'];
            $t['balance'] = $balance;
            $ledger->push($t);
        }

        return $ledger;
    }

    /**
     * 🔸 حساب الرصيد الافتتاحي قبل تاريخ معين
     */
    public function calculateOpeningBalance(Customer $customer, ?string $startDate): float
    {
        if (!$startDate) return 0;

        $date = Carbon::parse($startDate)->startOfDay();
        $balance = 0;

        // مصروفات قبل التاريخ
        $balance -= $customer->expensesAsPayer()->where('created_at', '<', $date)->sum('total_amount');
        $balance += $customer->expensesAsBeneficiary()->where('created_at', '<', $date)->sum('total_amount');
        $balance -= $customer->supplyings()->where('created_at', '<', $date)->sum('total_amount');
        $balance += $customer->sales()->where('created_at', '<', $date)->sum('total');

        return $balance;
    }

    /**
     * 🔹 تحديث الرصيد النهائي للعميل في قاعدة البيانات
     */
    public function updateCustomerBalance(Customer $customer): float
    {
        $balance =
            ($customer->expensesAsBeneficiary()->sum('total_amount') + $customer->sales()->sum('total'))
            - ($customer->expensesAsPayer()->sum('total_amount') + $customer->supplyings()->sum('total_amount'));

        $customer->update(['balance' => $balance]);

        return $balance;
    }

    public function updateCustomersBalance(): void
    {
        Customer::all()->each(fn($c) => $this->updateCustomerBalance($c));
    }
}
