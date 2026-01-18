<?php

namespace App\Services;

use App\Enums\CurrencyType;
use App\Models\Customer;
use App\Models\Currency;
use App\Models\CurrencyTransaction;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class CurrencyLogsService
{
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
        $rawPurchases = $this->getRawTransactions($customer, CurrencyType::SEND, $start, $end);
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
            $runningBalance += $tr->total;
            return [
                'id' => $tr->id,
                'index' => $index + 1,
                'date' => $tr->created_at->format('Y-m-d'),
                'currency_code' => $tr->currency?->code,
                'amount' => $tr->amount,
                'rate' => $tr->rate,
                'total' => $tr->total,
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
        $balance = $customer->net_balance;
        $customer->update(['balance' => $balance]);
        return (float) $balance;
    }
}