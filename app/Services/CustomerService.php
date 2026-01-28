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
        $endDate = $endDate ? Carbon::parse($endDate)->endOfDay() : null;

        // ✅ الرصيد الافتتاحي قبل startDate
        $openingBalance = $this->calculateOpeningBalance($customer, $startDate);

        $ledger->push([
            'type' => 'opening_balance',
            'date' => $startDate?->copy()->subDay() ?? Carbon::now()->subDay(),
            'description' => 'رصيد مرحل',
            'data' => '-',
            'amount_in' => 0,
            'amount_out' => 0,
            'balance' => $openingBalance,
        ]);

        // تحديد الفترة
        $rangeStart = $startDate;
        $rangeEnd = $endDate;

        $transactions = collect();

        // 🔹 مصروفات دافع
        $transactions = $transactions->merge(
            $customer->expensesAsPayer()
                ->when($rangeStart, fn($q) => $q->where('created_at', '>=', $rangeStart))
                ->when($rangeEnd, fn($q) => $q->where('created_at', '<=', $rangeEnd))
                ->get()
                ->map(fn($e) => [
                    'type' => 'expense_paid',
                    'date' => $e->created_at,
                    'description' => 'توريدة',#TODO : توريده ام دفع مصروف
                    'data' => $e->notes ?? '-',
                    'amount_in' => 0,
                    'amount_out' => $e->total_amount,
                ])
        );

        // 🔹 مصروفات مستلمة
        $transactions = $transactions->merge(
            $customer->expensesAsBeneficiary()
                ->when($rangeStart, fn($q) => $q->where('created_at', '>=', $rangeStart))
                ->when($rangeEnd, fn($q) => $q->where('created_at', '<=', $rangeEnd))
                ->get()
                ->map(fn($e) => [
                    'type' => 'expense_received',
                    'date' => $e->created_at,
                    'description' => 'استلام مصروف',
                    'data' => $e->notes ?? '-',
                    'amount_in' => $e->total_amount,
                    'amount_out' => 0,
                ])
        );

        // 🔹 التوريدات
        $transactions = $transactions->merge(
            $customer->supplyings()
                ->when($rangeStart, fn($q) => $q->where('created_at', '>=', $rangeStart))
                ->when($rangeEnd, fn($q) => $q->where('created_at', '<=', $rangeEnd))
                ->get()
                ->map(fn($s) => [
                    'type' => 'supplying',
                    'date' => $s->created_at,
                    'description' => 'توريد',
                    'data' => $s->statement ?? 'توريد',
                    'amount_in' => 0,
                    'amount_out' => $s->total_amount,
                ])
        );

        // 🔹 المبيعات
        $transactions = $transactions->merge(
            $customer->sales()
                ->when($rangeStart, fn($q) => $q->where('created_at', '>=', $rangeStart))
                ->when($rangeEnd, fn($q) => $q->where('created_at', '<=', $rangeEnd))
                ->get()
                ->map(fn($o) => [
                    'type' => 'sale',
                    'date' => $o->created_at,
                    'description' => 'بيع',
                    'data' => $o->items,
                    'amount_in' => $o->total,
                    'amount_out' => 0,
                ])
        );

        // 🔹 تحويل العملات (فقط لو النوع convert)
        $transactions = $transactions->merge(
            $customer->currencyConversion()
                ->when($rangeStart, fn($q) => $q->where('created_at', '>=', $rangeStart))
                ->when($rangeEnd, fn($q) => $q->where('created_at', '<=', $rangeEnd))
                ->where('type', 'convert')
                ->get()
                ->map(fn($c) => [
                    'type' => 'currency_conversion',
                    'date' => $c->created_at,
                    'description' => 'تحويل عملة (' . optional($c->currency)->name . ')',
                    'data' => '-',
                    'amount_in' => 0,
                    'amount_out' => $c->total,
                ])
        );

        // ترتيب حسب التاريخ وحساب الرصيد المتراكم
        $balance = $openingBalance;

        return $ledger
            ->merge($transactions->sortBy('date')->values())
            ->map(function ($item) use (&$balance) {
                $balance += $item['amount_in'] - $item['amount_out'];
                $item['balance'] = $balance;

                return $item;
            });
    }

    /**
     * 🔸 حساب الرصيد الافتتاحي قبل تاريخ معين
     */
    public function calculateOpeningBalance(Customer $customer, ?string $startDate): float
    {
        if (!$startDate) {
            return 0;
        }

        $date = Carbon::parse($startDate)->startOfDay();
        $balance = 0;

        $balance -= $customer->expensesAsPayer()->where('created_at', '<', $date)->sum('total_amount');
        $balance += $customer->expensesAsBeneficiary()->where('created_at', '<', $date)->sum('total_amount');
        $balance -= $customer->supplyings()->where('created_at', '<', $date)->sum('total_amount');
        $balance += $customer->sales()->where('created_at', '<', $date)->sum('total');

        // تحويل العملات فقط لو النوع convert
        $balance -= $customer->currencyConversion()
            ->where('created_at', '<', $date)
            ->where('type', 'convert')
            ->sum('total');

        return $balance;
    }

    /**
     * تحديث الرصيد الفعلي للعميل بالسوداني (Source of Truth)
     * الرصيد = (المبيعات + المصاريف المستلمة) - (التوريدات + المصاريف المدفوعة + مشتريات العملات)
     */

    public function updateCustomerBalance(Customer $customer): float
    {
        //$balance = $customer->net_balance;

        // 1. المبالغ التي (تزيد) مديونية العميل (له/عليه حسب طبيعة الحساب)
        $totalIn = 0;
        $totalIn += $customer->sales()->sum('total'); // إجمالي فواتير البيع
        $totalIn += $customer->expensesAsBeneficiary()->sum('total_amount'); // مصاريف استلمها

        // 2. المبالغ التي (تخفض) المديونية
        $totalOut = 0;
        $totalOut += $customer->supplyings()->sum('total_amount'); // مبالغ وردها نقداً
        $totalOut += $customer->expensesAsPayer()->sum('total_amount'); // مصاريف دفعها نيابة عنا

        // 3. مشتريات العملات (المعادل السوداني)
        // العميل عندما يشتري عملة، فإن القيمة المعادلة بالسوداني هي مبلغ "خارج" من حسابه
        $totalOut += \App\Models\CurrencyTransaction::where('payer_id', $customer->id)
            ->where('payer_type', get_class($customer))
            ->where('type', \App\Enums\CurrencyType::SEND)
            ->sum('total'); // مجموع عمود total (المعادل)

        // الحساب النهائي
        $finalBalance = $totalIn - $totalOut;

        // تحديث قاعدة البيانات
        $customer->update([
            'balance' => $finalBalance
        ]);

        return (float) $finalBalance;
    }

    public function updateCustomersBalance(): void
    {
        Customer::all()->each(fn($c) => $this->updateCustomerBalance($c));
    }
}
