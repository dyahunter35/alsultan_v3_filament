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
     * تم إصلاح الحسبة لتشمل كافة العمليات (سلف، رواتب، مصاريف) لضمان دقة الرصيد المرحل
     */
    public function calculateUnifiedOpeningBalances(User $delegate, ?string $startDate): array
    {
        if (!$startDate) {
            return ['treasury_opening' => 0, 'customer_opening' => 0];
        }

        $date = Carbon::parse($startDate)->startOfDay();

        // 1. رصيد الخزينة (السيولة الفعلية مع المندوب قبل تاريخ البداية)
        $treasuryIn = 0;
        $treasuryOut = 0;

        // مبالغ دخلت الخزينة
        $treasuryIn += $delegate->supplyingsAsRepresentative()->where('created_at', '<', $date)->sum('total_amount');
        $treasuryIn += $delegate->expensesAsBeneficiary()->where('created_at', '<', $date)->sum('total_amount');

        // مبالغ خرجت من الخزينة
        $treasuryOut += $delegate->expensesAsPayer()->where('created_at', '<', $date)->sum('total_amount');
        //$treasuryOut += $delegate->salaryAdvancesAsPayer()->where('created_at', '<', $date)->sum('amount');
        //$treasuryOut += $delegate->salaryPaymentsAsPayer()->where('payment_date', '<', $date)->sum('net_pay');

        // ملاحظة: expensesAsRepresentative لا تؤثر على صافي الخزينة لأنها (Amount In = Amount Out)

        // 2. رصيد العملاء (مديونية العملاء للمندوب قبل تاريخ البداية)
        $customerSales = $delegate->ordersAsRepresentative()->where('created_at', '<', $date)->sum('total');
        $customerPayments = 0;

        // تحصيلات نقدية
        $customerPayments += $delegate->supplyingsAsRepresentative()->where('created_at', '<', $date)->sum('total_amount');
        // تحصيلات تمت عبر دفع مصروف مباشر (رصيد عابر)
        $customerPayments += $delegate->expensesAsRepresentative()->where('created_at', '<', $date)->sum('total_amount');

        return [
            'treasury_opening' => (float) ($treasuryIn - $treasuryOut),
            'customer_opening' => (float) ($customerSales - $customerPayments),
        ];
    }

    public function calculateUserBalances(User $delegate): float
    {
        // استخدام نفس منطق الرصيد الافتتاحي حتى تاريخ الغد لضمان حساب الرصيد اللحظي الحالي
        $balances = $this->calculateUnifiedOpeningBalances($delegate, Carbon::tomorrow()->toDateString());
        $treasury = $balances['treasury_opening'] ?? 0;

        $delegate->update([
            'balance' => $treasury
        ]);

        return (float) $treasury;
    }

    /**
     * 🔹 توليد البيانات الموحدة للتقرير
     */
    public function generateUnifiedLedger(User $delegate, ?string $startDate = null, ?string $endDate = null): Collection
    {
        $formattedStartDate = $startDate;
        $formattedEndDate = $endDate;

        // جلب الأرصدة الافتتاحية
        $openings = $this->calculateUnifiedOpeningBalances($delegate, $formattedStartDate);

        $runTreasury = $openings['treasury_opening'];
        $runCustomer = $openings['customer_opening'];
        $runTotal = $runTreasury + $runCustomer;

        $transactions = collect();

        // دمج كافة العمليات
        $transactions = $transactions->merge($this->getOrdersTransactions($delegate, $formattedStartDate, $formattedEndDate));
        $transactions = $transactions->merge($this->getSupplyingsTransactions($delegate, $formattedStartDate, $formattedEndDate));
        $transactions = $transactions->merge($this->getBeneficiaryExpenseTransactions($delegate, $formattedStartDate, $formattedEndDate));
        $transactions = $transactions->merge($this->getPayerExpenseTransactions($delegate, $formattedStartDate, $formattedEndDate));
        $transactions = $transactions->merge($this->getRepresentativeExpenseTransactions($delegate, $formattedStartDate, $formattedEndDate));
        //$transactions = $transactions->merge($this->getSalaryAdvanceTransactions($delegate, $formattedStartDate, $formattedEndDate));
        //$transactions = $transactions->merge($this->getSalaryPaymentTransactions($delegate, $formattedStartDate, $formattedEndDate));

        $sorted = $transactions->sortBy('date')->values();
        $ledger = collect();

        // سطر الرصيد المرحل
        $ledger->push([
            'date' => $formattedStartDate ? Carbon::parse($formattedStartDate)->subDay()->format('Y-m-d') : Carbon::now()->subDay()->format('Y-m-d'),
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
            'balance' => $runTreasury,
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

    private function getOrdersTransactions(User $delegate, $startDate, $endDate): Collection
    {
        return $delegate->ordersAsRepresentative()
            ->when($startDate, fn($q) => $q->where('created_at', '>=', $startDate))
            ->when($endDate, fn($q) => $q->where('created_at', '<=', $endDate))
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
                'amount_in' => 0,
                'amount_out' => 0,
            ]);
    }

    private function getSupplyingsTransactions(User $delegate, $startDate, $endDate): Collection
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

    private function getBeneficiaryExpenseTransactions(User $delegate, $startDate, $endDate): Collection
    {
        return $delegate->expensesAsBeneficiary()
            ->when($startDate, fn($q) => $q->where('created_at', '>=', $startDate))
            ->when($endDate, fn($q) => $q->where('created_at', '<=', $endDate))
            ->with('type')
            ->get()
            ->map(fn($e) => [
                'date' => $e->created_at,
                'transaction_name' => 'استلام مصروف ' . ($e->type?->label ?? ''),
                'description' => 'استلام عهدة (سلفة)',
                'details' => $e->notes ?? 'استلام نقدية من الإدارة',
                'customer_name' => '-',
                'treasury_debit' => $e->total_amount,
                'treasury_credit' => 0,
                'customer_sales' => 0,
                'customer_payment' => 0,
                'amount_in' => $e->total_amount,
                'amount_out' => 0,
            ]);
    }

    private function getPayerExpenseTransactions(User $delegate, $startDate, $endDate): Collection
    {
        return $delegate->expensesAsPayer()
            ->when($startDate, fn($q) => $q->where('created_at', '>=', $startDate))
            ->when($endDate, fn($q) => $q->where('created_at', '<=', $endDate))
            ->with('type', 'beneficiary')
            ->get()
            ->map(fn($e) => [
                'date' => $e->created_at,
                'transaction_name' => 'دفع مصروف [ ' . ($e->type?->label ?? '') . ' ]',
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

    private function getRepresentativeExpenseTransactions(User $delegate, $startDate, $endDate): Collection
    {
        return $delegate->expensesAsRepresentative()
            ->when($startDate, fn($q) => $q->where('created_at', '>=', $startDate))
            ->when($endDate, fn($q) => $q->where('created_at', '<=', $endDate))
            ->with('type', 'beneficiary')
            ->get()
            ->map(fn($e) => [
                'date' => $e->created_at,
                'transaction_name' => 'رصيد عابر [ ' . ($e->type?->label ?? '') . ' ]',
                'description' => 'تحصيل وصرف فوري',
                'details' => $e->notes ?? 'تم الصرف مباشرة من تحصيل العميل',
                'customer_name' => $e->beneficiary?->name ?? '-',
                'treasury_debit' => $e->total_amount,
                'treasury_credit' => $e->total_amount,
                'customer_sales' => 0,
                'customer_payment' => $e->total_amount,
                'amount_in' => $e->total_amount,
                'amount_out' => $e->total_amount,
            ]);
    }

    private function getSalaryAdvanceTransactions(User $delegate, $startDate, $endDate): Collection
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

    private function getSalaryPaymentTransactions(User $delegate, $startDate, $endDate): Collection
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