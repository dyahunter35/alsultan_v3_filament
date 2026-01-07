<x-filament-panels::page>
    <div class="no-print">
        {{ $this->form }}
    </div>
    <x-filament::section>

        <div class="p-6 bg-white shadow-sm rounded-xl">
            <div class="grid grid-cols-3 gap-4 mb-6 font-bold text-center">
                <div class="p-4 bg-green-100 border border-green-500 rounded">
                    <p>الرصيد المتبقي</p>
                    <h2 class="text-2xl">{{ number_format($summary['balance'], 2) }}</h2>
                </div>
                <div class="p-4 border border-gray-300 rounded bg-gray-50">
                    <p>إجمالي المطالبات</p>
                    <h2 class="text-2xl">{{ number_format($summary['total_claims'], 2) }}</h2>
                </div>
                <div class="p-4 border border-gray-300 rounded bg-gray-50">
                    <p>إجمالي المدفوعات</p>
                    <h2 class="text-2xl">{{ number_format($summary['total_paid'], 2) }}</h2>
                </div>
            </div>

            <table class="w-full text-sm text-center border border-collapse border-gray-800">
                <thead class="bg-gray-200">
                    <tr>
                        <th class="p-2 border border-gray-800" rowspan="2">التاريخ</th>
                        <th class="p-2 border border-gray-800" colspan="5">بيان الفاتورة (الطلبية)</th>
                        <th class="p-2 border border-gray-800" colspan="3">بيان السداد</th>
                    </tr>
                    <tr class="bg-gray-100">
                        <th>الوصف</th>
                        <th>الكمية</th>
                        <th>الوزن (طن)</th>
                        <th>سعر الوحدة</th>
                        <th>القيمة</th>
                        <th>التاريخ</th>
                        <th>الوصف</th>
                        <th>المبلغ</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($groups as $group)
                        @php
                            $rowsCount = max($group['cargos']->count(), $group['payments']->count()) + 1;
                        @endphp

                        @for ($i = 0; $i < $rowsCount; $i++)
                            <tr>
                                @if ($i === 0)
                                    <td class="border border-gray-800" rowspan="{{ $rowsCount }}">
                                        {{ $group['date'] }} <br>
                                        <span class="text-xs text-blue-600">شحنة #{{ $group['truck_id'] }}</span>
                                    </td>
                                @endif

                                @php $cargo = $group['cargos']->get($i); @endphp
                                <td class="p-1 border border-gray-400">{{ $cargo?->product?->name ?? '-' }}</td>
                                <td class="p-1 border border-gray-400">{{ $cargo?->quantity ?? '-' }}</td>
                                <td class="p-1 border border-gray-400">{{ $cargo?->weight ?? '-' }}</td>
                                <td class="p-1 border border-gray-400">{{ number_format($cargo?->unit_price ?? 0, 2) }}
                                </td>
                                <td class="p-1 border border-gray-400">
                                    {{ number_format($cargo?->weight * $cargo?->unit_price ?? 0, 2) }}</td>

                                @php $payment = $group['payments']->get($i); @endphp
                                <td class="p-1 border border-gray-400">
                                    {{ $payment?->created_at->format('Y-m-d') ?? '-' }}</td>
                                    
                                <td class="p-1 border border-gray-400">
                                    @if ($payment)
                                        
                                    المعامل : {{ $payment?->rate ?? '-' }} <br/>التحويله : {{ $payment?->amount ?? '-' }}
                                    @else
                                    -
                                    @endif

                                </td>
                                <td class="p-1 font-bold text-red-600 border border-gray-400">
                                    {{ $payment ? number_format($payment->total, 2) : '-' }}
                                </td>
                            </tr>
                        @endfor
                        <tr class="font-bold bg-blue-50">
                            <td colspan="1" class="px-4 text-right border border-gray-800">مطلوبات الشحنة:</td>
                            <td colspan="2" class="border border-gray-800">{{ number_format($group['balance'] , 2) }}</td>
                            <td colspan="2" class="border border-gray-800"></td>
                            <td class="border border-gray-800">{{ number_format($group['total_invoice'], 2) }}</td>
                            <td colspan="2" class="border border-gray-800"></td>
                            <td class="border border-gray-800">{{ number_format($group['total_paid'], 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

    </x-filament::section>
</x-filament-panels::page>
