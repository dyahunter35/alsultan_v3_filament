<x-filament-panels::page>
    {{-- اختيار الشاحنة/الشركة --}}
    <div class="no-print">
        {{ $this->form }}
    </div>

    @php $reports = $this->report_data; @endphp

    @if ($reports)
        {{-- المعادل العام --}}


        @foreach ($reports as $data)
            <div class="p-6 mb-10 bg-white border border-gray-200 rounded-lg shadow-md break-after-page" dir="rtl">

                {{-- هيدر الشاحنة --}}
                <div class="flex items-center justify-between pb-4 mb-6 border-b-2 border-gray-100">
                    <div>
                        <h2 class="text-2xl font-black text-gray-800 underline decoration-blue-500">بيان الشحنة رقم
                            {{ $data['truck']->id }}</h2>
                        <p class="mt-1 font-bold text-gray-600">السائق: {{ $data['truck']->driver_name }} | رقم اللوحة:
                            {{ $data['truck']->plate_number }}</p>
                    </div>
                    {{-- <div class="grid grid-cols-2 gap-4 text-xs">
                        <div class="p-2 border rounded bg-gray-50">
                            <span class="block text-gray-500">جمارك (سوداني)</span>
                            <span
                                class="font-bold">{{ number_format($data['truck']->expenses->sum('total_amount'), 2) }}</span>
                        </div>
                        <div class="p-2 border rounded bg-gray-50">
                            <span class="block text-gray-500">ترحيل (مصري)</span>
                            <span class="font-bold">{{ number_format($data['truck']->truck_fare_sum, 2) }}</span>
                        </div>
                    </div> --}}
                </div>

                {{-- الجدول --}}
                <div class="overflow-x-auto">
                    <table class="w-full text-[11px] text-center border-collapse border border-gray-400">
                        <thead>
                            <tr class="font-bold text-white bg-gray-800">
                                <th colspan="3" class="p-1 border border-gray-400">بيانات الصنف</th>
                                <th colspan="2" class="p-1 border border-gray-400">الكميات</th>
                                <th colspan="2" class="p-1 border border-gray-400">السعر الأساسي</th>
                                <th colspan="3" class="p-1 border border-gray-400">التكاليف المضافة</th>
                                <th colspan="3" class="p-1 border border-gray-400">المالية (بالمصري)</th>
                                <th colspan="2" class="p-1 border border-gray-400">سعر الطرد</th>
                                <th colspan="2" class="p-1 border border-gray-400">السوداني</th>
                            </tr>
                            <tr class="font-bold text-gray-700 bg-gray-100">
                                <th class="p-1 border border-gray-400">#</th>
                                <th class="w-32 p-1 border border-gray-400">الصنف</th>
                                <th class="p-1 border border-gray-400">المقاس</th>
                                <th class="p-1 border border-gray-400">وزن الطرد</th>
                                <th class="p-1 border border-gray-400">العدد</th>
                                <th class="p-1 border border-gray-400">الطن</th>
                                <th class="p-1 border border-gray-400">سعر الطن</th>
                                <th class="p-1 border border-gray-400">المجموع</th>
                                <th class="p-1 border border-gray-400">الترحيل</th>
                                <th class="p-1 border border-gray-400">الجمارك</th>
                                <th class="p-1 bg-yellow-100 border border-gray-400">التكلفة</th>
                                <th class="w-16 p-1 bg-blue-100 border border-gray-400">الربح %</th>
                                <th class="p-1 border border-gray-400">قيمة الربح</th>
                                <th class="p-1 font-bold text-green-700 border border-gray-400">سعر البيع</th>
                                <th class="p-1 border border-gray-400">طرد (م)</th>
                                <th class="p-1 font-bold text-blue-800 border border-gray-400">طرد (س)</th>
                                <th class="p-1 border border-gray-400">طن (س)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($data['rows'] as $row)
                                <tr class="border-b border-gray-400 hover:bg-gray-50">
                                    <td class="p-1 border border-gray-400">{{ $row->index }}</td>
                                    <td class="p-1 text-right border border-gray-400 whitespace-nowrap">
                                        {{ $row->product_name }}</td>
                                    <td class="p-1 border border-gray-400">{{ $row->size }}</td>
                                    <td class="p-1 border border-gray-400">{{ $row->unit_weight }}</td>
                                    <td class="p-1 border border-gray-400">{{ $row->quantity }}</td>
                                    <td class="p-1 border border-gray-400">{{ number_format($row->weight_ton, 3) }}
                                    </td>
                                    <td class="p-1 border border-gray-400">{{ number_format($row->unit_price, 2) }}
                                    </td>
                                    <td class="p-1 border border-gray-400">{{ number_format($row->base_total_egp, 2) }}
                                    </td>
                                    <td class="p-1 border border-gray-400">{{ number_format($row->transport_cost, 2) }}
                                    </td>
                                    <td class="p-1 border border-gray-400">{{ number_format($row->customs_cost, 2) }}
                                    </td>
                                    <td class="p-1 font-bold border border-gray-400 bg-yellow-50">
                                        {{ number_format($row->total_cost, 2) }}</td>

                                    <td class="p-0 border border-gray-400 bg-blue-50 no-print">
                                        <input type="number" step="0.5"
                                            wire:model.live.debounce.500ms="profit_percents.{{ $row->cargo_id }}"
                                            class="w-full h-full p-1 text-xs font-bold text-center text-blue-800 bg-transparent border-none focus:ring-0">
                                    </td>
                                    <td class="p-1 border border-gray-400 print-only">{{ $row->profit_percent }}%</td>

                                    <td class="p-1 border border-gray-400">{{ number_format($row->profit_value, 2) }}
                                    </td>
                                    <td class="p-1 font-bold text-green-700 border border-gray-400">
                                        {{ number_format($row->selling_price_egp, 2) }}</td>
                                    <td class="p-1 border border-gray-400">
                                        {{ number_format($row->package_price_egp, 2) }}</td>
                                    <td class="p-1 font-bold border border-gray-400 bg-gray-50">
                                        {{ number_format($row->package_price_sdg, 2) }}</td>
                                    <td class="p-1 italic text-gray-600 border border-gray-400">
                                        {{ number_format($row->ton_price_sdg, 0) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="font-bold text-white uppercase bg-gray-800">
                            <tr>
                                <td colspan="3" class="p-1 text-center border border-gray-400">المجاميع</td>
                                <td class="p-1 border border-gray-400"></td>
                                <td class="p-1 border border-gray-400">{{ $data['totals']['quantity'] }}</td>
                                <td class="p-1 border border-gray-400">
                                    {{ number_format($data['totals']['weight'], 3) }}</td>
                                <td class="p-1 border border-gray-400"></td>
                                <td class="p-1 border border-gray-400">
                                    {{ number_format($data['totals']['base_egp'], 2) }}</td>
                                <td class="p-1 border border-gray-400">
                                    {{ number_format($data['totals']['transport'], 2) }}</td>
                                <td class="p-1 border border-gray-400">
                                    {{ number_format($data['totals']['customs'], 2) }}</td>
                                <td class="p-1 border border-gray-400">
                                    {{ number_format($data['totals']['total_cost'], 2) }}</td>
                                <td class="p-1 border border-gray-400"></td>
                                <td class="p-1 border border-gray-400">
                                    {{ number_format($data['totals']['profit'], 2) }}</td>
                                <td class="p-1 border border-gray-400">
                                    {{ number_format($data['totals']['selling_egp'], 2) }}</td>
                                <td colspan="3" class="p-1 bg-gray-700 border border-gray-400"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        @endforeach
    @else
        <div class="p-20 text-center bg-white border-2 border-gray-300 border-dashed shadow rounded-xl">
            <h3 class="text-xl font-bold text-gray-400">الرجاء اختيار شاحنة أو شركة من القائمة أعلاه لعرض بيانات التسعير
            </h3>
        </div>
    @endif

    <style>
        @media print {
            .no-print {
                display: none !important;
            }

            .print-only {
                display: table-cell !important;
            }

            .break-after-page {
                page-break-after: always;
            }

            body {
                background: white !important;
            }

            .fi-main-ctn {
                padding: 0 !important;
            }
        }

        .print-only {
            display: none;
        }
    </style>
</x-filament-panels::page>
