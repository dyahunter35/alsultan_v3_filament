<x-filament-panels::page>
    <div class="no-print">
        <x-filament::section class="mb-4">
            {{ $this->form }}
        </x-filament::section>
    </div>

    @if ($contractorId)
        <div id="report-content" class="p-4 bg-white shadow rounded-xl">
            {{-- Header --}}
            <x-report-header label="تقرير مقاول شحن" :value="$contractor?->name"/>
            <div class="flex flex-col items-center justify-between mb-6 md:flex-row">
                

                    <div class="p-4 text-center border rounded-lg bg-red-50 min-w-32">
                        <p class="text-xs text-red-600">الرصيد</p>
                        <p class="text-xl font-bold {{ $summary['balance'] < 0 ? 'text-red-700' : 'text-slate-800' }}">
                            {{ number_format($summary['balance'], 2) }}
                        </p>
                    </div>
                    <div class="p-4 text-center border rounded-lg bg-green-50 min-w-32">
                        <p class="text-xs text-green-600">المدفوعات</p>
                        <p class="text-xl font-bold text-green-700">{{ number_format($summary['total_paid'], 2) }}</p>
                    </div>
                    <div class="p-4 text-center border rounded-lg bg-blue-50 min-w-32">
                        <p class="text-xs text-blue-600">المطالبات</p>
                        <p class="text-xl font-bold text-blue-700">{{ number_format($summary['total_claims'], 2) }}</p>
                    </div>
            </div>

            {{-- Table --}}
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-center border-collapse border border-slate-400">
                    <thead>
                        <tr class="bg-gray-100 font-bold border-b border-slate-400">
                            <th class="p-2 border border-slate-300" rowspan="2">رقم الشحنة</th>
                            <th class="p-2 border border-slate-300" rowspan="2">رقم اللوحة</th>
                            <th class="p-2 border border-slate-300" rowspan="2">تاريخ الشحن</th>
                            <th class="p-2 border border-slate-300" rowspan="2">تاريخ التفريغ</th>
                            <th class="p-2 border border-slate-300" rowspan="2">المدة بالايام</th>
                            <th class="p-2 border border-slate-300" rowspan="2">المصنع</th>
                            <th class="p-2 border border-slate-300" rowspan="2">النولون</th>
                            <th class="p-2 border border-slate-300" rowspan="2">العطلة</th>
                            <th class="p-2 border border-slate-300" rowspan="2">المبلغ الكلي</th>
                            <th class="p-2 border border-slate-300 bg-blue-50" colspan="3">السداد</th>
                        </tr>
                        <tr class="bg-gray-100 font-bold border-b border-slate-400">
                            <th class="p-2 border border-slate-300 bg-blue-50">التاريخ</th>
                            <th class="p-2 border border-slate-300 bg-blue-50">البيان</th>
                            <th class="p-2 border border-slate-300 bg-blue-50">المبلغ</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($rows as $index => $row)
                            @if ($row['type'] === 'trip')
                                <tr class="hover:bg-slate-50 transition-colors bg-white">
                                    <td class="p-2 border border-slate-300">{{ $row['truck_id'] }}</td>
                                    <td class="p-2 border border-slate-300">{{ $row['car_number'] }}</td>
                                    <td class="p-2 border border-slate-300">{{ $row['shipment_date'] }}</td>
                                    <td class="p-2 border border-slate-300">{{ $row['discharge_date'] }}</td>
                                    <td class="p-2 border border-slate-300">{{ $row['duration'] }} يوم</td>
                                    <td class="p-2 border border-slate-300 text-xs">{{ $row['factory'] }}</td>
                                    <td class="p-2 border border-slate-300 tabular-nums">{{ number_format($row['fare'], 2) }}</td>
                                    <td
                                        class="p-2 border border-slate-300 tabular-nums {{ $row['delay'] > 0 ? 'bg-yellow-50 font-bold' : '' }}">
                                        {{ number_format($row['delay'], 2) }}
                                    </td>
                                    <td class="p-2 border border-slate-300 font-bold tabular-nums">
                                        {{ number_format($row['total_amount'], 2) }}</td>

                                    {{-- Settlement columns for trip --}}
                                    <td class="p-2 border border-slate-300 bg-blue-50/20 text-xs">{{ $row['settlement_date'] }}</td>
                                    <td class="p-2 border border-slate-300 bg-blue-50/20">{{ $row['settlement_desc'] }}</td>
                                    <td class="p-2 border border-slate-300 bg-blue-50/20 font-bold text-red-600 tabular-nums">
                                        {{ $row['settlement_amount'] > 0 ? number_format($row['settlement_amount'], 2) : '-' }}
                                    </td>
                                </tr>
                            @else
                                {{-- Standalone payment row --}}
                                <tr class="bg-orange-100 font-bold">
                                    <td class="p-2 border border-slate-300 italic" colspan="9">
                                        {{ $row['description'] }}
                                    </td>
                                    <td class="p-2 border border-slate-300 bg-orange-100 text-orange-900 text-xs">
                                        {{ $row['settlement_date'] }}</td>
                                    <td class="p-2 border border-slate-300 bg-orange-100 text-orange-900">
                                        {{ $row['settlement_desc'] }}</td>
                                    
                                    <td class="p-2 border border-slate-300 bg-orange-200 text-red-800 tabular-nums">
                                        {{ number_format($row['settlement_amount'], 2) }}
                                    </td>
                                </tr>
                            @endif
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="bg-slate-800 text-white font-bold">
                            <td colspan="6" class="p-2 text-right">الإجمالي الكلي</td>
                            <td colspan="1" class="p-2 border border-slate-600">-</td>
                            <td colspan="1" class="p-2 border border-slate-600">-</td>
                            <td class="p-2 border border-slate-600 tabular-nums">
                                {{ number_format($summary['total_claims'], 2) }}</td>
                            <td colspan="2" class="p-2 text-left">إجمالي المسدد</td>
                            <td class="p-2 bg-red-900 tabular-nums">{{ number_format($summary['total_paid'], 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            <x-print-button/>
        </div>
    @else
        <div
            class="flex flex-col items-center justify-center p-20 bg-white border-2 border-dashed border-gray-300 rounded-xl">
            <x-filament::icon icon="heroicon-o-user-group" class="w-16 h-16 text-gray-300 mb-4" />
            <h2 class="text-xl font-bold text-gray-400">يرجى اختيار مقاول من القائمة أعلاه لبدء العرض</h2>
        </div>
    @endif

    <style>
        @media print {
            .no-print {
                display: none !important;
            }

            body {
                background-color: white !important;
            }

            .fi-main-ctn {
                padding: 0 !important;
                margin: 0 !important;
            }

            table {
                font-size: 10px !important;
            }

            #report-content {
                box-shadow: none !important;
                border: none !important;
            }
        }
    </style>
</x-filament-panels::page>