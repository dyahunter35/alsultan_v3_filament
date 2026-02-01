<?php

namespace App\Services;

use App\Enums\CurrencyType;
use App\Enums\ExpenseGroup;
use App\Enums\ExpenseType;
use App\Models\Customer;
use App\Models\Currency;
use App\Models\CurrencyTransaction;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class CurrencyLogsService
{

    public function generateLedger(Customer $customer, ?string $startDate = null, ?string $endDate = null): Collection
    {
        $start = $startDate ? Carbon::parse($startDate)->startOfDay() : null;
        $end = $endDate ? Carbon::parse($endDate)->endOfDay() : null;

        $ledger = collect();

        // 1. حساب الرصيد الافتتاحي (المعادل السوداني للمشتريات - الصرف) قبل تاريخ البداية
        $openingBalance = $this->calculateOpeningBalance($customer, $start);

        // إضافة سطر الرصيد الافتتاحي
        $ledger->push([
            'date' => $start ? $start->copy()->subSecond() : now(),
            'description' => 'رصيد مرحل (سجل العملات)',
            'type' => 'opening_balance',
            'currency' => '-',
            'amount' => 0,
            'rate' => 0,
            'amount_in' => 0, // زيادة في المديونية (شراء)
            'amount_out' => 0, // تخفيض (صرف للشركات)
            'balance' => $openingBalance,
        ]);

        // 2. جلب عمليات شراء العملات (التي تزيد الرصيد السوداني)
        $purchases = $this->getRawTransactions($customer, CurrencyType::Convert, $start, $end)

            ->toBase() // تحويل لمجموعة بيانات بسيطة لتجنب بحث النظام عن getKey()
            ->map(fn($tr) => [
                'date' => $tr->created_at,
                'description' => 'شراء عملة: ' . ($tr->currency?->name ?? 'غير معروف'),
                'type' => 'purchase',
                'currency' => $tr->currency?->code,
                'amount' => $tr->amount,
                'rate' => $tr->rate,
                'amount_in' => $tr->amount * $tr->rate,
                'amount_out' => 0,
            ]);

        // 3. جلب عمليات الصرف للشركات (التي تخفض الرصيد السوداني للعملة)
        $payments = $this->getRawTransactions($customer, CurrencyType::CompanyExpense, $start, $end)
            ->toBase() // تحويل لمجموعة بيانات بسيطة لتجنب بحث النظام عن getKey()
            ->map(fn($tr) => [
                'date' => $tr->created_at,
                'description' => 'صرف لشركة: ' . ($tr->party?->name ?? '-'),
                'type' => 'payment',
                'currency' => $tr->currency?->code,
                'amount' => $tr->amount,
                'rate' => $tr->rate,
                'amount_in' => 0,
                'amount_out' => $tr->amount * $tr->rate, // تحسب القيمة وقت الصرف
            ]);

        // 4. دمج الحركات، الترتيب، وحساب الرصيد المتراكم
        $currentBalance = $openingBalance;

        $sortedTransactions = $purchases->merge($payments)
            ->sortBy('date')
            ->values()
            ->map(function ($item) use (&$currentBalance) {
                $currentBalance += ($item['amount_in'] - $item['amount_out']);
                $item['balance'] = $currentBalance;
                return $item;
            });

        return $ledger->merge($sortedTransactions);
    }

    /**
     * حساب الرصيد الافتتاحي المعادل قبل تاريخ معين
     */
    private function calculateOpeningBalance(Customer $customer, ?Carbon $date): float
    {
        if (!$date)
            return 0;

        // مجموع المشتريات (التي دخلت الحساب)
        $totalPurchases = CurrencyTransaction::query()
            ->where('payer_id', $customer->id)
            ->where('payer_type', get_class($customer))
            ->where('type', CurrencyType::Convert)
            ->where('created_at', '<', $date)
            ->get()
            ->sum(fn($tr) => $tr->amount * $tr->rate);

        // مجموع الصرف (الذي خرج من الحساب)
        $totalPayments = CurrencyTransaction::query()
            ->where('payer_id', $customer->id)
            ->where('payer_type', get_class($customer))
            ->where('type', CurrencyType::CompanyExpense)
            ->where('created_at', '<', $date)
            ->get()
            ->sum(fn($tr) => $tr->amount * $tr->rate);

        return (float) ($totalPurchases - $totalPayments);
    }
    public function generateCurrencyLogsReport(int $customerId, ?string $startDate = null, ?string $endDate = null): array
    {
        $customer = Customer::find($customerId);
        if (!$customer)
            return ['customer' => null];

        $start = $startDate ? Carbon::parse($startDate)->startOfDay() : null;
        $end = $endDate ? Carbon::parse($endDate)->endOfDay() : null;

        // 1. الأرصدة الحالية
        $balances = $this->getCurrentBalances($customer);

        // 2. معالجة عمليات الشراء مع حساب الرصيد التراكمي
        $rawPurchases = $this->getRawTransactions($customer, CurrencyType::Convert, $start, $end);
        $processedPurchases = $this->processPurchases($rawPurchases);

        // 3. معالجة عمليات الصرف
        $rawPayments = $this->getRawTransactions($customer, CurrencyType::CompanyExpense, $start, $end);
        $processedPayments = $this->processPayments($rawPayments);

        return [
            'customer' => $customer,
            'balances' => $balances,
            'purchase_transactions' => $processedPurchases,
            'payment_transactions' => $processedPayments,
        ];
    }

    private function processPurchases(Collection $transactions): array
    {
        $runningBalance = 0;
        return $transactions->map(function ($tr, $index) use (&$runningBalance) {
            $runningBalance += $tr->amount * $tr->rate;
            return [
                'id' => $tr->id,
                'index' => $index + 1,
                'date' => $tr->created_at->format('Y-m-d'),
                'currency_code' => $tr->currency?->code,
                'amount' => $tr->amount ?? '0',
                'rate' => $tr->rate,
                'total' => $tr->amount * $tr->rate,
                'running_balance' => $runningBalance,
            ];
        })->toArray();
    }

    private function processPayments(Collection $transactions): array
    {
        return $transactions->map(function ($tr, $index) {
            return [
                'id' => $tr->id,
                'index' => $index + 1,
                'date' => $tr->created_at->format('Y-m-d'),
                'currency_code' => $tr->currency?->code,
                'amount' => $tr->amount,
                'company' => $tr->party?->name ?? '-',
                'note' => $tr->note,
                'total' => $tr->total,
            ];
        })->toArray();
    }

    private function getCurrentBalances(Customer $customer): array
    {
        $balances = ['sd' => $customer->balance ?? 0];
        foreach (Currency::all() as $currency) {
            $balances[$currency->code] = $customer->currencyValue($currency->id);
        }
        return $balances;
    }

    private function getRawTransactions($customer, $type, $start, $end): Collection
    {
        return CurrencyTransaction::query()
            ->where('payer_id', $customer->id)
            ->where('payer_type', get_class($customer))
            ->where('type', $type)
            ->when($start, fn($q) => $q->where('created_at', '>=', $start))
            ->when($end, fn($q) => $q->where('created_at', '<=', $end))
            ->with(['currency', 'party'])
            ->orderBy('created_at', 'asc')
            ->get();
    }

    public function updateCustomerBalance(Customer $customer): float
    {
        $balance = $this->calculateOpeningBalance($customer, now());
        $customer->update(['balance' => $balance]);
        return (float) $balance;
    }

    public function updateCurencyesBalance(): void
    {
        Customer::per(ExpenseGroup::CURRENCY)->each(fn($c) => $this->updateCustomerBalance($c));
    }
}