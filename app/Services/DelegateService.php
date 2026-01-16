<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class DelegateService
{
    /**
     * 🔹 حساب الأرصدة الافتتاحية الموحدة
     */
    public function calculateUnifiedOpeningBalances(User $delegate, ?string $startDate): array
    {
        if (!$startDate)
            return ['treasury_opening' => 0, 'customer_opening' => 0];
        $date = Carbon::parse($startDate)->startOfDay();

        // 1. رصيد الخزينة (السيولة الفعلية مع المندوب)
        $treasury = 0;
        $treasury += $delegate->supplyingsAsRepresentative()->where('created_at', '<', $date)->sum('total_amount');
        $treasury += $delegate->expensesAsBeneficiary()->where('created_at', '<', $date)->sum('total_amount');
        //$treasury += $delegate->ordersAsRepresentative()->where('status', OrderStatus::Payed)->where('created_at', '<', $date)->sum('total');
        $treasury += $delegate->expensesAsRepresentative()->where('created_at', '<', $date)
            ->sum('total_amount');
        $treasury -= $delegate->expensesAsPayer()->where('created_at', '<', $date)->sum('total_amount');

        // 2. رصيد العملاء (الديون الخارجية)
        $customerDebt = 0;
        $customerDebt += $delegate->ordersAsRepresentative()->where('created_at', '<', $date)->sum('total');
        $customerDebt -= $delegate->supplyingsAsRepresentative()->where('created_at', '<', $date)->sum('total_amount');
        $customerDebt -= $delegate->expensesAsRepresentative()
            ->where('created_at', '<', $date)
            ->sum('total_amount');

        return [
            'treasury_opening' => (float) $treasury,
            'customer_opening' => (float) $customerDebt,
        ];
    }

    public function calculateUserBalances(User $delegate): float
    {
        // 1. رصيد الخزينة (السيولة الفعلية مع المندوب)
        $treasury = 0;
        $treasury += $delegate->supplyingsAsRepresentative()->sum('total_amount');
        //$treasury += $delegate->ordersAsRepresentative()->where('status', OrderStatus::Payed)->sum('total');
        $treasury += $delegate->expensesAsBeneficiary()->sum('total_amount');
        $treasury -= $delegate->expensesAsPayer()->sum('total_amount');

        $delegate->update([
            'balance' => $treasury
        ]);
        return (float) $treasury;
    }

    /**
     * 🔹 توليد البيانات الموحدة للتقريرين (العادي والمتقدم)
     */
    public function generateUnifiedLedger(User $delegate, ?string $startDate = null, ?string $endDate = null): Collection
    {
        $formattedStartDate = $startDate ? Carbon::parse($startDate)->startOfDay() : null;
        $formattedEndDate = $endDate ? Carbon::parse($endDate)->endOfDay() : null;

        // جلب الأرصدة الافتتاحية
        $openings = $this->calculateUnifiedOpeningBalances($delegate, $formattedStartDate ? $formattedStartDate->toDateString() : null);

        $runTreasury = $openings['treasury_opening'];
        $runCustomer = $openings['customer_opening'];
        $runTotal = $runTreasury + $runCustomer;

        $transactions = collect();

        // 1. المبيعات
        $transactions = $transactions->merge($this->getOrdersTransactions($delegate, $formattedStartDate, $formattedEndDate));

        // 2. التحصيلات
        $transactions = $transactions->merge($this->getSupplyingsTransactions($delegate, $formattedStartDate, $formattedEndDate));

        // 3. استلام عهدة
        $transactions = $transactions->merge($this->getBeneficiaryExpenseTransactions($delegate, $formattedStartDate, $formattedEndDate));

        // 4. دفع مصروف
        $transactions = $transactions->merge($this->getPayerExpenseTransactions($delegate, $formattedStartDate, $formattedEndDate));

        // 5. رصيد عابر
        $transactions = $transactions->merge($this->getRepresentativeExpenseTransactions($delegate, $formattedStartDate, $formattedEndDate));

        // 6. سلف الموظفين
        $transactions = $transactions->merge($this->getSalaryAdvanceTransactions($delegate, $formattedStartDate, $formattedEndDate));

        // 7. رواتب الموظفين
        $transactions = $transactions->merge($this->getSalaryPaymentTransactions($delegate, $formattedStartDate, $formattedEndDate));

        $sorted = $transactions->sortBy('date')->values();
        $ledger = collect();

        // سطر الرصيد الافتتاحي الموحد
        $ledger->push([
            'date' => $formattedStartDate?->copy()->subDay()->format('Y-m-d') ?? Carbon::now()->subDay()->format('Y-m-d'),
            'transaction_name' => 'رصيد مرحل',
            'description' => 'رصيد مرحل من فترة سابقة',
            'details' => '-',
            'customer_name' => '-',
            'treasury_debit' => 0,
            'treasury_credit' => 0,
            'treasury_balance' => $runTreasury,
            'customer_sales' => 0,
            'customer_payment' => 0,
            'customer_balance' => $runCustomer,
            'amount_in' => 0,
            'amount_out' => 0,
            'balance' => $runTotal,
        ]);

        foreach ($sorted as $item) {
            $runTreasury += ($item['treasury_debit'] - $item['treasury_credit']);
            $runCustomer += ($item['customer_sales'] - $item['customer_payment']);
            $runTotal += ($item['amount_in'] - $item['amount_out']);

            $item['treasury_balance'] = $runTreasury;
            $item['customer_balance'] = $runCustomer;
            $item['balance'] = $runTotal;

            $ledger->push($item);
        }

        return $ledger;
    }

    private function getOrdersTransactions(User $delegate, ?Carbon $startDate, ?Carbon $endDate): Collection
    {
        return $delegate->ordersAsRepresentative()
            ->when($startDate, fn($q) => $q->where('created_at', '>=', $startDate))
            ->when($endDate, fn($q) => $q->where('created_at', '<=', $endDate))
            //->where('status', OrderStatus::Payed)
            ->get()
            ->map(fn($o) => [
                'date' => $o->created_at,
                'description' => 'فاتورة مبيعات [ #' . $o->number . ' ]',
                'transaction_name' => 'فاتورة مبيعات [ #' . $o->number . ' ]',
                'details' => $o->items,
                'customer_name' => $o->customer?->name ?? 'عميل نقدي',
                'treasury_debit' => 0,
                'treasury_credit' => 0,
                'customer_sales' => $o->total,
                'customer_payment' => 0,
                'amount_in' => 0, #TODO : insure correction
                'amount_out' => 0,
            ]);
    }

    private function getSupplyingsTransactions(User $delegate, ?Carbon $startDate, ?Carbon $endDate): Collection
    {
        return $delegate->supplyingsAsRepresentative()
            ->when($startDate, fn($q) => $q->where('created_at', '>=', $startDate))
            ->when($endDate, fn($q) => $q->where('created_at', '<=', $endDate))
            ->with('customer')->get()
            ->map(fn($s) => [
                'date' => $s->created_at,
                'transaction_name' => 'تحصيل / توريد',
                'description' => 'توريد نقدية من عميل',
                'details' => $s->statement ?? 'سداد مديونية',
                'customer_name' => $s->customer?->name ?? '-',
                'treasury_debit' => $s->total_amount,
                'treasury_credit' => 0,
                'customer_sales' => 0,
                'customer_payment' => $s->total_amount,
                'amount_in' => $s->total_amount,
                'amount_out' => 0,
            ]);
    }

    private function getBeneficiaryExpenseTransactions(User $delegate, ?Carbon $startDate, ?Carbon $endDate): Collection
    {
        return $delegate->expensesAsBeneficiary()
            ->when($startDate, fn($q) => $q->where('created_at', '>=', $startDate))
            ->when($endDate, fn($q) => $q->where('created_at', '<=', $endDate))
            ->with('type')
            ->get()
            ->map(fn($e) => [
                'date' => $e->created_at,
                'transaction_name' => 'استلام مصروف' . $e->type?->label ?? '',
                'description' => 'استلام عهدة (سلفة)',
                'details' => $e->notes ?? 'استلام نقدية من الإدارة',
                'customer_name' => '-', // Fixed: $s is undefined in original code, likely meant '-' or unrelated
                'treasury_debit' => $e->total_amount,
                'treasury_credit' => 0,
                'customer_sales' => 0,
                'customer_payment' => 0,
                'amount_in' => $e->total_amount,
                'amount_out' => 0,
            ]);
    }

    private function getPayerExpenseTransactions(User $delegate, ?Carbon $startDate, ?Carbon $endDate): Collection
    {
        return $delegate->expensesAsPayer()
            ->when($startDate, fn($q) => $q->where('created_at', '>=', $startDate))
            ->when($endDate, fn($q) => $q->where('created_at', '<=', $endDate))
            ->with('type')
            ->get()
            ->map(fn($e) => [
                'date' => $e->created_at,
                'transaction_name' => 'دفع مصروف [ ' . $e->type?->label . ' ]' ?? '',
                'description' => 'صرف من العهدة',
                'details' => $e->notes ?? 'دفع مصروف خارجي',
                'customer_name' => $e->beneficiary?->name ?? '-',
                'treasury_debit' => 0,
                'treasury_credit' => $e->total_amount,
                'customer_sales' => 0,
                'customer_payment' => 0,
                'amount_in' => 0,
                'amount_out' => $e->total_amount,
            ]);
    }

    private function getRepresentativeExpenseTransactions(User $delegate, ?Carbon $startDate, ?Carbon $endDate): Collection
    {
        return $delegate->expensesAsRepresentative()
            ->when($startDate, fn($q) => $q->where('created_at', '>=', $startDate))
            ->when($endDate, fn($q) => $q->where('created_at', '<=', $endDate))
            ->with('type')
            ->get()
            ->map(fn($e) => [
                'date' => $e->created_at,
                'transaction_name' => 'رصيد عابر [ ' . $e->type?->label . ' ]' ?? '',
                'description' => 'استلام عهدة (سلفة)',
                'details' => $e->notes ?? 'استلام نقدية من الإدارة',
                'customer_name' => $e->beneficiary?->name ?? '-',
                'treasury_debit' => $e->total_amount,
                'treasury_credit' => $e->total_amount,
                'customer_sales' => 0,
                'customer_payment' => $e->total_amount,
                'amount_in' => $e->total_amount,
                'amount_out' => $e->total_amount,
            ]);
    }

    private function getSalaryAdvanceTransactions(User $delegate, ?Carbon $startDate, ?Carbon $endDate): Collection
    {
        return $delegate->salaryAdvancesAsPayer()
            ->when($startDate, fn($q) => $q->where('created_at', '>=', $startDate))
            ->when($endDate, fn($q) => $q->where('created_at', '<=', $endDate))
            ->with('employee')
            ->get()
            ->map(fn($item) => [
                'date' => $item->created_at,
                'transaction_name' => 'سلفة موظف',
                'description' => 'صرف سلفة للموظف: ' . ($item->employee?->name ?? '-'),
                'details' => $item->notes ?? 'سلفة نقدية',
                'customer_name' => '-',
                'treasury_debit' => 0,
                'treasury_credit' => $item->amount,
                'customer_sales' => 0,
                'customer_payment' => 0,
                'amount_in' => 0,
                'amount_out' => $item->amount,
            ]);
    }

    private function getSalaryPaymentTransactions(User $delegate, ?Carbon $startDate, ?Carbon $endDate): Collection
    {
        return $delegate->salaryPaymentsAsPayer()
            ->when($startDate, fn($q) => $q->where('payment_date', '>=', $startDate))
            ->when($endDate, fn($q) => $q->where('payment_date', '<=', $endDate))
            ->with('employee')
            ->get()
            ->map(fn($item) => [
                'date' => Carbon::parse($item->payment_date),
                'transaction_name' => 'صرف راتب',
                'description' => 'راتب شهر: ' . $item->for_month . ' - للموظف: ' . ($item->employee?->name ?? '-'),
                'details' => $item->notes ?? 'صرف راتب شهري',
                'customer_name' => '-',
                'treasury_debit' => 0,
                'treasury_credit' => $item->net_pay,
                'customer_sales' => 0,
                'customer_payment' => 0,
                'amount_in' => 0,
                'amount_out' => $item->net_pay,
            ]);
    }
}