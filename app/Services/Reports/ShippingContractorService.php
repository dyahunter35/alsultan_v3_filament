<?php

namespace App\Services\Reports;

use App\Models\Company;
use App\Models\CurrencyTransaction;
use App\Models\Truck;
use Illuminate\Support\Carbon;

class ShippingContractorService
{
    public function getReportData(int $contractorId, ?string $dateRange = null): array
    {
        [$start, $end] = $this->parseDateRange($dateRange);

        // 1. Fetch Trucks for this contractor
        $trucks = Truck::query()
            ->with(['cargos', 'from'])
            ->where('contractor_id', $contractorId)
            ->when($start && $end, function ($q) use ($start, $end) {
                $q->whereBetween('created_at', [$start, $end]);
            })
            ->orderBy('created_at', 'asc')
            ->get();

        // 2. Fetch standalone payments (no truck_id)
        $standalonePayments = CurrencyTransaction::query()
            ->where('party_id', $contractorId)
            ->where('party_type', Company::class)
            ->whereNull('truck_id')
            ->when($start && $end, function ($q) use ($start, $end) {
                $q->whereBetween('created_at', [$start, $end]);
            })
            ->orderBy('created_at', 'asc')
            ->get();

        // 3. Prepare data rows
        $rows = [];
        $totalClaims = 0;
        $totalPaid = 0;

        // Add trucks
        foreach ($trucks as $truck) {
            $fare = (float) ($truck->truck_fare ?? 0);
            $delay = (float) ($truck->delay_value ?? 0);
            $totalAmount = $fare + $delay;

            // Fetch payments linked to this truck
            $truckPayments = CurrencyTransaction::query()
                ->where('truck_id', $truck->id)
                ->where('party_id', $contractorId)
                ->where('party_type', Company::class)
                ->get();

            $paidAmount = $truckPayments->sum('total');
            $totalClaims += $totalAmount;
            $totalPaid += $paidAmount;

            $rows[] = [
                'type' => 'trip',
                'date' => $truck->created_at, // Use created_at or shipment_date
                'truck_id' => $truck->id,
                'car_number' => $truck->car_number,
                'shipment_date' => $truck->created_at?->format('Y-m-d'), // Placeholder for shipment date
                'discharge_date' => $truck->arrive_date?->format('Y-m-d'),
                'duration' => $truck->trip_days,
                'factory' => $truck->from?->name ?? 'N/A',
                'items' => $truck->cargos->map(fn($c) => $c->note)->filter()->implode(', '),
                'fare' => $fare,
                'delay' => $delay,
                'total_amount' => $totalAmount,
                'settlement_desc' => $truckPayments->first()?->note ?? '',
                'settlement_date' => $truckPayments->first()?->created_at?->format('Y-m-d') ?? '-',
                'settlement_amount' => $paidAmount,
                'created_at' => $truck->created_at,
            ];
        }

        // Add standalone payments
        foreach ($standalonePayments as $payment) {
            $totalPaid += $payment->total;

            $rows[] = [
                'type' => 'payment',
                'date' => $payment->created_at,
                'description' => $payment->note ?? '',
                'amount' => $payment->total,
                'settlement_desc' => $payment->note ?? '',
                'settlement_date' => $payment->created_at?->format('Y-m-d'),
                'settlement_amount' => $payment->total,
                'created_at' => $payment->created_at,
            ];
        }

        // Sort rows by date
        usort($rows, fn($a, $b) => $a['created_at'] <=> $b['created_at']);

        // Recalculate balances if needed (though not strictly shown as a running balance column in the image, but good for summary)

        return [
            'rows' => $rows,
            'summary' => [
                'total_claims' => $totalClaims,
                'total_paid' => $totalPaid,
                'balance' => $totalClaims - $totalPaid,
            ]
        ];
    }

    protected function parseDateRange(?string $dateRange): array
    {
        if (!$dateRange || !str_contains($dateRange, ' - ')) {
            return [null, null];
        }

        [$start, $end] = explode(' - ', $dateRange);
        return [
            Carbon::parse($start)->startOfDay(),
            Carbon::parse($end)->endOfDay(),
        ];
    }
}
