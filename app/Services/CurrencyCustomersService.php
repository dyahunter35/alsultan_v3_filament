<?php

namespace App\Services;

use App\Enums\ExpenseGroup;
use App\Models\Customer;
use App\Models\Currency;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class CurrencyCustomersService
{
    /**
     * توليد تقرير عملاء العملة الشامل
     */
    public function generateCurrencyCustomersReport(?string $startDate = null, ?string $endDate = null, ?string $customerType = null): array
    {
        $formattedStartDate = $startDate ? Carbon::parse($startDate)->startOfDay() : null;
        $formattedEndDate = $endDate ? Carbon::parse($endDate)->endOfDay() : null;

        // حساب الأرصدة الافتتاحية
        $openingBalances = $this->calculateOpeningBalances($formattedStartDate);

        // جلب جميع المعاملات
        $transactions = $this->getAllTransactions($formattedStartDate, $formattedEndDate, $customerType);

        // حساب الأرصدة الجارية
        $ledger = $this->calculateRunningBalances($transactions, $openingBalances);

        // حساب الملخص
        $summary = $this->calculateSummary($ledger, $openingBalances);

        // ملخص الحسابات
        $accountsSummary = $this->getAccountsSummary($ledger);

        return [
            'summary' => $summary,
            'ledger' => $ledger,
            'accounts_summary' => $accountsSummary,
            'opening_balances' => $openingBalances,
        ];
    }

    /**
     * حساب الأرصدة الافتتاحية لكل نوع حساب
     */
    private function calculateOpeningBalances(?Carbon $startDate): array
    {
        if (!$startDate) {
            return [
                'debtors' => 0,
                'usd' => 0,
                'debtors_others' => 0,
            ];
        }

        $date = $startDate->copy()->startOfDay();

        // 1. حساب المدينون (عملاء المبيعات)
        $debtors = Customer::per(ExpenseGroup::SALE)->get()->sum(function ($customer) use ($date) {
            $balance = 0;
            $balance += $customer->sales()->where('created_at', '<', $date)->sum('total');
            $balance -= $customer->supplyings()->where('created_at', '<', $date)->sum('total_amount');
            $balance += $customer->expensesAsBeneficiary()->where('created_at', '<', $date)->sum('total_amount');
            $balance -= $customer->expensesAsPayer()->where('created_at', '<', $date)->sum('total_amount');
            return $balance;
        });

        // 2. حساب الدولار (من CurrencyBalance)
        $usdCurrency = Currency::where('code', '=', 'USD')->first();
        $usd = 0;
        if ($usdCurrency) {
            $customers = Customer::all();
            foreach ($customers as $customer) {
                $usd += $customer->currencyBalance()
                    ->where('currency_id', $usdCurrency->id)
                    ->first()?->amount ?? 0;
            }
        }

        // 3. حساب الدائنون (موردون وغيرهم)
        $debtorsOthers = Customer::per(ExpenseGroup::DEBTORS)->get()->sum(function ($customer) use ($date) {
            $balance = 0;
            $balance -= $customer->expensesAsPayer()->where('created_at', '<', $date)->sum('total_amount');
            $balance += $customer->expensesAsBeneficiary()->where('created_at', '<', $date)->sum('total_amount');
            return $balance;
        });

        return [
            'debtors' => (float) $debtors,
            'usd' => (float) $usd,
            'debtors_others' => (float) $debtorsOthers,
        ];
    }

    /**
     * جلب جميع المعاملات من جميع الأنواع
     */
    private function getAllTransactions(?Carbon $startDate, ?Carbon $endDate, ?string $customerType): Collection
    {
        $transactions = collect();

        $customersQuery = Customer::query();
        if ($customerType) {
            $customersQuery->where('permanent', $customerType);
        }
        $customers = $customersQuery->get();

        foreach ($customers as $customer) {
            // 1. المبيعات
            $sales = $customer->sales()
                ->when($startDate, fn($q) => $q->where('created_at', '>=', $startDate))
                ->when($endDate, fn($q) => $q->where('created_at', '<=', $endDate))
                ->get()
                ->map(fn($order) => [
                    'date' => $order->created_at,
                    'type' => 'sale',
                    'customer_id' => $customer->id,
                    'customer_name' => $customer->name,
                    'customer_type' => $customer->permanent,
                    'description' => 'فاتورة مبيعات #' . $order->number,
                    'details' => $order->items,
                    'debtors_debit' => $order->total,
                    'debtors_credit' => 0,
                    'usd_debit' => 0,
                    'usd_credit' => 0,
                    'debtors_others_debit' => 0,
                    'debtors_others_credit' => 0,
                ]);

            $transactions = $transactions->merge($sales);

            // 2. التوريدات
            $supplyings = $customer->supplyings()
                ->when($startDate, fn($q) => $q->where('created_at', '>=', $startDate))
                ->when($endDate, fn($q) => $q->where('created_at', '<=', $endDate))
                ->get()
                ->map(fn($supply) => [
                    'date' => $supply->created_at,
                    'type' => 'supplying',
                    'customer_id' => $customer->id,
                    'customer_name' => $customer->name,
                    'customer_type' => $customer->permanent,
                    'description' => 'توريد نقدية',
                    'details' => $supply->statement ?? 'سداد مديونية',
                    'debtors_debit' => 0,
                    'debtors_credit' => $supply->total_amount,
                    'usd_debit' => 0,
                    'usd_credit' => 0,
                    'debtors_others_debit' => 0,
                    'debtors_others_credit' => 0,
                ]);

            $transactions = $transactions->merge($supplyings);

            // 3. المصروفات كدافع
            $expensesPaid = $customer->expensesAsPayer()
                ->when($startDate, fn($q) => $q->where('created_at', '>=', $startDate))
                ->when($endDate, fn($q) => $q->where('created_at', '<=', $endDate))
                ->with('type')
                ->get()
                ->map(function ($expense) use ($customer) {
                    $accountType = $this->getAccountTypeFromCustomerType($customer->permanent);
                    return [
                        'date' => $expense->created_at,
                        'type' => 'expense_paid',
                        'customer_id' => $customer->id,
                        'customer_name' => $customer->name,
                        'customer_type' => $customer->permanent,
                        'description' => 'دفع مصروف - ' . ($expense->type?->label ?? ''),
                        'details' => $expense->notes ?? '-',
                        'debtors_debit' => 0,
                        'debtors_credit' => $accountType === 'debtors' ? $expense->total_amount : 0,
                        'usd_debit' => 0,
                        'usd_credit' => 0,
                        'debtors_others_debit' => 0,
                        'debtors_others_credit' => $accountType === 'debtors_others' ? $expense->total_amount : 0,
                    ];
                });

            $transactions = $transactions->merge($expensesPaid);

            // 4. المصروفات كمستفيد
            $expensesReceived = $customer->expensesAsBeneficiary()
                ->when($startDate, fn($q) => $q->where('created_at', '>=', $startDate))
                ->when($endDate, fn($q) => $q->where('created_at', '<=', $endDate))
                ->with('type')
                ->get()
                ->map(function ($expense) use ($customer) {
                    $accountType = $this->getAccountTypeFromCustomerType($customer->permanent);
                    return [
                        'date' => $expense->created_at,
                        'type' => 'expense_received',
                        'customer_id' => $customer->id,
                        'customer_name' => $customer->name,
                        'customer_type' => $customer->permanent,
                        'description' => 'استلام مصروف - ' . ($expense->type?->label ?? ''),
                        'details' => $expense->notes ?? '-',
                        'debtors_debit' => $accountType === 'debtors' ? $expense->total_amount : 0,
                        'debtors_credit' => 0,
                        'usd_debit' => 0,
                        'usd_credit' => 0,
                        'debtors_others_debit' => $accountType === 'debtors_others' ? $expense->total_amount : 0,
                        'debtors_others_credit' => 0,
                    ];
                });

            $transactions = $transactions->merge($expensesReceived);

            // 5. معاملات العملات (الدولار)
            $currencyTransactions = $customer->currencyAsPayer()
                ->when($startDate, fn($q) => $q->where('created_at', '>=', $startDate))
                ->when($endDate, fn($q) => $q->where('created_at', '<=', $endDate))
                ->with('currency')
                ->get()
                ->map(fn($ct) => [
                    'date' => $ct->created_at,
                    'type' => 'currency_sent',
                    'customer_id' => $customer->id,
                    'customer_name' => $customer->name,
                    'customer_type' => $customer->permanent,
                    'description' => 'إرسال ' . ($ct->currency?->name ?? 'عملة'),
                    'details' => $ct->note ?? '-',
                    'debtors_debit' => 0,
                    'debtors_credit' => 0,
                    'usd_debit' => 0,
                    'usd_credit' => $ct->amount,
                    'debtors_others_debit' => 0,
                    'debtors_others_credit' => 0,
                ]);

            $transactions = $transactions->merge($currencyTransactions);

            $currencyReceived = $customer->currencyAsParty()
                ->when($startDate, fn($q) => $q->where('created_at', '>=', $startDate))
                ->when($endDate, fn($q) => $q->where('created_at', '<=', $endDate))
                ->with('currency')
                ->get()
                ->map(fn($ct) => [
                    'date' => $ct->created_at,
                    'type' => 'currency_received',
                    'customer_id' => $customer->id,
                    'customer_name' => $customer->name,
                    'customer_type' => $customer->permanent,
                    'description' => 'استلام ' . ($ct->currency?->name ?? 'عملة'),
                    'details' => $ct->note ?? '-',
                    'debtors_debit' => 0,
                    'debtors_credit' => 0,
                    'usd_debit' => $ct->amount,
                    'usd_credit' => 0,
                    'debtors_others_debit' => 0,
                    'debtors_others_credit' => 0,
                ]);

            $transactions = $transactions->merge($currencyReceived);
        }

        return $transactions->sortBy('date')->values();
    }

    private function getAccountTypeFromCustomerType($customerType): string
    {
        return match ($customerType) {
            ExpenseGroup::SALE => 'debtors',
            ExpenseGroup::DEBTORS => 'debtors_others',
            default => 'debtors',
        };
    }

    private function calculateRunningBalances(Collection $transactions, array $openingBalances): Collection
    {
        $runningDebtors = $openingBalances['debtors'];
        $runningUsd = $openingBalances['usd'];
        $runningDebtorsOthers = $openingBalances['debtors_others'];

        $ledger = collect();

        $ledger->push([
            'date' => Carbon::now()->subDay(),
            'type' => 'opening',
            'customer_id' => null,
            'customer_name' => '-',
            'customer_type' => null,
            'description' => 'رصيد مرحل',
            'details' => '-',
            'debtors_debit' => 0,
            'debtors_credit' => 0,
            'debtors_balance' => $runningDebtors,
            'usd_debit' => 0,
            'usd_credit' => 0,
            'usd_balance' => $runningUsd,
            'debtors_others_debit' => 0,
            'debtors_others_credit' => 0,
            'debtors_others_balance' => $runningDebtorsOthers,
        ]);

        foreach ($transactions as $transaction) {
            $runningDebtors += ($transaction['debtors_debit'] - $transaction['debtors_credit']);
            $runningUsd += ($transaction['usd_debit'] - $transaction['usd_credit']);
            $runningDebtorsOthers += ($transaction['debtors_others_debit'] - $transaction['debtors_others_credit']);

            $transaction['debtors_balance'] = $runningDebtors;
            $transaction['usd_balance'] = $runningUsd;
            $transaction['debtors_others_balance'] = $runningDebtorsOthers;

            $ledger->push($transaction);
        }

        return $ledger;
    }

    /**
     * حساب الملخص النهائي - تم إصلاح مشكلة المفاتيح المفقودة
     */
    private function calculateSummary(Collection $ledger, array $openingBalances): array
    {
        $lastRow = $ledger->last();

        // جلب الأرصدة الحالية من آخر سطر في الـ Ledger، أو استخدام الرصيد الافتتاحي إذا لم توجد معاملات
        $debtorsBalance = $lastRow['debtors_balance'] ?? $openingBalances['debtors'];
        $usdBalance = $lastRow['usd_balance'] ?? $openingBalances['usd'];
        $othersBalance = $lastRow['debtors_others_balance'] ?? $openingBalances['debtors_others'];

        return [
            'debtors_balance' => $debtorsBalance,
            'usd_balance' => $usdBalance,
            'debtors_others_balance' => $othersBalance,
            'total_balance' => $debtorsBalance + $usdBalance + $othersBalance,
        ];
    }

    private function getAccountsSummary(Collection $ledger): Collection
    {
        $lastRow = $ledger->last();

        return collect([
            [
                'account_name' => 'حساب المدينون (عملاء المبيعات)',
                'balance' => $lastRow['debtors_balance'] ?? 0,
            ],
            [
                'account_name' => 'رصيد الدولار',
                'balance' => $lastRow['usd_balance'] ?? 0,
            ],
            [
                'account_name' => 'حساب الدائنون (موردون وآخرون)',
                'balance' => $lastRow['debtors_others_balance'] ?? 0,
            ],
        ]);
    }
}