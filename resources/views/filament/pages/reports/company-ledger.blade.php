<x-filament-panels::page>
    <div class="no-print">
        {{ $this->form }}
    </div>
    <x-filament::section id='report-content'>

        @php
            $label = 'تقرير شركة : ' . $_company?->name;
        @endphp
        <x-report-header :label="$label" />

        <div class="p-1 mt-2 rounded-xl">
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
                        <th class="p-2 border border-gray-800" rowspan="3">التاريخ</th>

                        <th class="p-2 border border-gray-800" colspan="10">بيان الفاتورة (الطلبية)</th>
                        <th class="p-2 border border-gray-800" colspan="3" rowspan="2">بيان السداد</th>
                    </tr>
                    <tr class="font-bold text-white bg-gray-800">
                        <th colspan="4" class="p-1 border border-gray-400">بيانات الصنف</th>
                        <th colspan="3" class="p-1 border border-gray-400">الكميات</th>
                        <th colspan="3" class="p-1 border border-gray-400">سعر الشراء </th </tr>
                    <tr class="bg-gray-100">
                        <th class="p-1 border border-gray-400">#</th>
                        <th class="w-32 p-1 border border-gray-400">الصنف</th>
                        <th class="p-1 border border-gray-400">المقاس</th>
                        <th class="p-1 border border-gray-400">وزن الوحدة</th>
                        <th class="p-1 border border-gray-400">الطرد</th>
                        <th class="p-1 border border-gray-400">العدد</th>
                        <th class="p-1 border border-gray-400">الطن</th>
                        <th class="p-1 border border-gray-400">الوحدة</th>
                        <th class="p-1 border border-gray-400">الطن</th>
                        <th class="p-1 border border-gray-400">المجموع</th>
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
                                <td class="p-1 border border-gray-400">{{ $i + 1 }}</td>
                                <td class="p-1 border border-gray-400">{{ $cargo?->product_name ?? '-' }}</td>
                                <td class="p-1 border border-gray-400">{{ $cargo?->size ?? '-' }}</td>
                                <td class="p-1 border border-gray-400">
                                    {{ number_format($cargo?->unit_weight, 2) ?? '-' }}</td>
                                <td class="p-1 border border-gray-400">{{ number_format($cargo?->quantity, 2) ?? '-' }}
                                </td>
                                <td class="p-1 border border-gray-400">
                                    {{ number_format($cargo?->unit_quantity, 2) ?? '-' }}</td>
                                <td class="p-1 border border-gray-400">
                                    {{ number_format($cargo?->weight_ton, 2) ?? '-' }}</td>
                                <td class="p-1 border border-gray-400">{{ number_format($cargo?->unit_price ?? 0, 2) }}
                                </td>
                                <td class="p-1 border border-gray-400">
                                    {{ number_format($cargo?->ton_price ?? 0, 2) }}
                                </td>
                                <td class="p-1 border border-gray-400">
                                    {{ number_format($cargo?->base_total_foreign ?? 0, 2) }}
                                </td>

                                @php $payment = $group['payments']->get($i); @endphp
                                <td class="p-1 border border-gray-400">
                                    {{ $payment?->created_at->format('Y-m-d') ?? '-' }}
                                </td>

                                <td class="p-1 border border-gray-400">
                                    @if ($payment)
                                        المعامل : {{ $payment?->rate ?? '-' }} <br />التحويله :
                                        {{ $payment?->amount ?? '-' }}
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
                            <td rowspan="2"></td>
                            <td colspan="3" rowspan="2" class="border border-gray-800">المجموع</td>
                            <td colspan="6" class="border border-gray-800"></td>
                            <td class="border border-gray-800">{{ number_format($group['total_invoice'], 2) }}</td>
                            <td colspan="2" class="border border-gray-800"></td>
                            <td class="border border-gray-800">{{ number_format($group['total_paid'], 2) }}</td>
                        </tr>
                        <tr class="font-bold bg-blue-50">
                            <td colspan="9" class=""></td>
                            <td colspan="" class="font-bold border border-gray-800"
                                style="color : @if ($group['balance'] > 0) green @else red @endif">
                                {{ number_format($group['balance'], 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

    </x-filament::section>
</x-filament-panels::page>
