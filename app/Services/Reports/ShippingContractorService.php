<?php

namespace App\Services\Reports;

use App\Models\Company;
use App\Models\CurrencyTransaction;
use App\Models\Truck;
use Illuminate\Support\Collection;

class ShippingContractorService
{
    public function getReportData(int $contractorId, ?string $dateRange = null): array
    {
        [$start, $end] = parseDateRange($dateRange);

        $carriedBalance = 0;
        if ($start) {
            $prevClaims = (float) Truck::query()
                ->where('contractor_id', $contractorId)
                ->where('created_at', '<', $start)
                ->sum(\Illuminate\Support\Facades\DB::raw('COALESCE(truck_fare, 0) + COALESCE(delay_value, 0)'));

            $prevPaid = (float) CurrencyTransaction::query()
                ->where('party_type', Company::class)
                ->where('party_id', $contractorId)
                ->where('created_at', '<', $start)
                ->sum('total');

            $carriedBalance = $prevPaid - $prevClaims;
        }

        // 1. جلب بيانات المقاول مع شاحناته ومدفوعاته في آن واحد (Eager Loading)
        $contractor = Company::query()
            ->with([
                    'trucksAsContractor' => function ($q) use ($start, $end) {
                        $q->with(['cargos', 'companyId']) // companyId هنا هو المصنع/الشركة المرتبطة بالرحلة
                            ->when($start && $end, fn($q) => $q->whereBetween('created_at', [$start, $end]))
                            ->orderBy('created_at', 'asc');
                    },
                    'currencyTransactions' => function ($q) use ($start, $end) {
                        $q->when($start && $end, fn($q) => $q->whereBetween('created_at', [$start, $end]))
                            ->orderBy('created_at', 'asc');
                    }
                ])
            ->findOrFail($contractorId);

        $rows = new Collection();
        $totalClaims = 0;
        $totalPaid = 0;

        // 2. معالجة الشاحنات (المطالبات المالية)
        foreach ($contractor->trucksAsContractor as $truck) {
            $fare = (float) $truck->truck_fare;
            $delay = (float) $truck->delay_value;
            $totalAmount = $fare + $delay;

            $totalClaims += $totalAmount;

            $rows->push([
                'type' => 'trip',
                'id' => $truck->id, // للربط لاحقاً إذا لزم الأمر
                'date' => $truck->created_at,
                'truck_code' => $truck->code,
                'car_number' => $truck->car_number,
                'driver_name' => $truck->driver_name,
                'driver_phone' => $truck->driver_phone,
                'shipment_date' => $truck->pack_date?->format('Y-m-d'),
                'discharge_date' => $truck->arrive_date?->format('Y-m-d'),
                'factory' => $truck->companyId?->name ?? 'N/A',
                'items' => $truck->category?->name,
                'fare' => $fare,
                'delay' => $delay,
                'duration' => $truck->pack_date?->diffInDays($truck->arrive_date, false),
                'total_amount' => $totalAmount,
                'created_at' => $truck->created_at,
                // الحقول التالية سيتم ملؤها إذا كانت المدفوعة مرتبطة برقم الشاحنة
                'settlement_desc' => '',
                'settlement_date' => '-',
                'settlement_amount' => 0,
            ]);
        }

        // 3. معالجة المدفوعات وربطها بالشاحنات إذا وُجد truck_id
        foreach ($contractor->currencyTransactions as $payment) {
            $amount = (float) $payment->total;
            $totalPaid += $amount;

            // إذا كانت المدفوعة مرتبطة بشاحنة معينة، نبحث عنها في الـ Rows ونحدثها
            if ($payment->truck_id) {
                $truckRow = $rows->where('type', 'trip')->where('id', $payment->truck_id)->first();
                if ($truckRow) {
                    // تحديث بيانات السداد داخل صف الشاحنة
                    $index = $rows->search($truckRow);
                    $truckRow['settlement_desc'] = $payment->note ?? 'سداد شحنة';
                    $truckRow['settlement_date'] = $payment->created_at?->format('Y-m-d');
                    $truckRow['settlement_amount'] += $amount; // نستخدم += في حال وجود أكثر من دفعة للشاحنة
                    $rows[$index] = $truckRow;
                    continue; // ننتقل للمدفوعة التالية ولا نضيفها كصف منفصل
                }
            }

            // إذا لم تكن مرتبطة بشاحنة (أو لم نجد الشاحنة في النطاق الزمني)، تضاف كصف "دفعة مستقلة"
            $rows->push([
                'type' => 'payment',
                'date' => $payment->created_at,
                'description' => $payment->note ?? 'دفعة مالية',
                'total_amount' => 0,
                'settlement_desc' => $payment->note ?? '',
                'settlement_date' => $payment->created_at?->format('Y-m-d'),
                'settlement_amount' => $amount,
                'created_at' => $payment->created_at,
            ]);
        }

        // 4. الترتيب النهائي وحساب الرصيد
        $finalRows = $rows->sortBy('created_at')->values();
        $runningBalance = $carriedBalance;

        $finalRows = $finalRows->map(function ($row) use (&$runningBalance) {
            // الرصيد = (المدفوعات) - (المطالبات/الرحلات)
            $runningBalance += ($row['settlement_amount'] - ($row['total_amount'] ?? 0));
            $row['balance'] = $runningBalance;
            return $row;
        });

        return [
            'rows' => $finalRows->toArray(),
            'summary' => [
                'total_claims' => $totalClaims,
                'total_paid' => $totalPaid,
                'carried_balance' => $carriedBalance,
                'balance' => $runningBalance,
            ]
        ];
    }
}