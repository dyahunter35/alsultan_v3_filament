<x-filament-panels::page>
    {{-- اختيار الشاحنة/الشركة --}}
    <div class="no-print">
        {{ $this->form }}
    </div>

    @php
        $reports = $this->report_data;
    @endphp

    @if ($reports)
        {{-- المعادل العام --}}

        <div id='report-content'>
            <x-filament::section class="mb-4">
                <header class="clearfix">
                    <div id="logo" style="text-align:center; margin-top:10px;">
                        <img width="80" src="{{ asset('asset/logo.png') }}" alt="logo" class="mx-auto" />
                        <h2 class="text-bold">{{ __('app.name') }}</h2>
                        <h3>{{ __('app.address') }}</h3>
                    </div>

                    <div class="border row" style="border:1px dashed #999; padding:6px;">
                        <div style="display:flex; justify-content:space-between;">
                            <div>
                                @if ($truck_id)
                                    <div>📍 <b>تقرير الشحنة رقم :</b> {{ $reports[0]['truck']?->id ?? '-' }}</div>
                                @else
                                    <div>📍 <b>اسم الشركة:</b> {{ $_company?->name ?? '-' }}</div>
                                @endif
                            </div>
                            <div style="text-align:left;">
                                <div><b>تاريخ التقرير:</b> {{ now()->format('Y/m/d') }}</div>
                            </div>
                        </div>
                    </div>
                </header>

                <dl class="grid grid-cols-3 gap-4 my-2 text-center text-l md:grid-cols-3">
                    <div>
                        <dt class="font-bold text-gray-600">العملة الافتراضية</dt>
                        <dd>{{ $currency_name ?? '' }}</dd>
                    </div>

                    <div>
                        {{-- <dt class="font-bold text-gray-600">تكلفة العطلات</dt> --}}
                        {{-- <dd>{{ number_format($truck->delay_value, 2) }}</dd> --}}
                    </div>

                    <div>
                        <dt class="font-bold text-gray-600">المعادل</dt>
                        <dd>{{ $exchange_rate }}</dd>
                    </div>

                </dl>
            </x-filament::section>

            @foreach ($reports as $data)
                <div class="p-6 mb-10 bg-white border border-gray-200 rounded-lg shadow-md" dir="rtl">

                    {{-- هيدر الشاحنة --}}
                    <div class="flex items-center justify-between pb-4 mb-6 border-b-2 border-gray-100">
                        <div>
                            <h2 class="text-xl font-black text-gray-800 decoration-blue-500">بيان الشحنة رقم
                                {{ $data['truck']->id }}</h2>
                            <p class="mt-1 font-bold text-gray-600"> رقم
                                اللوحة:
                                {{ $data['truck']->car_number }}
                                | تاريخ الشحنة
                                :
                                {{ $data['truck']->created_at->format('Y-m-d') }}</p>
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
                        <table class="w-full text-[13px] text-center border-collapse border border-gray-400">
                            <thead>
                                <tr class="font-bold text-white bg-gray-800">
                                    <th colspan="3" class="p-1 border border-gray-400">بيانات الصنف</th>
                                    <th colspan="2" class="p-1 border border-gray-400">الكميات</th>
                                    <th colspan="2" class="p-1 border border-gray-400">السعر الأساسي</th>
                                    <th colspan="3" class="p-1 border border-gray-400">التكاليف المضافة</th>
                                    <th colspan="3" class="p-1 border border-gray-400">المالية
                                        ({{ $currency_name }})
                                    </th>
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
                                    <th class="p-1 border border-gray-400">المنصرفات</th>
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
                                        <td class="p-1 border border-gray-400">
                                            {{ number_format($row->base_total_egp, 2) }}
                                        </td>
                                        <td class="p-1 border border-gray-400">
                                            {{ number_format($row->transport_cost, 2) }}
                                        </td>
                                        <td class="p-1 border border-gray-400">
                                            {{ number_format($row->customs_cost, 2) }}
                                        </td>
                                        <td class="p-1 font-bold border border-gray-400 bg-yellow-50">
                                            {{ number_format($row->total_cost, 2) }}</td>

                                        <td class="p-0 border border-gray-400 bg-blue-50 no-print">
                                            <input type="number" step="0.5"
                                                wire:model.live.debounce.500ms="profit_percents.{{ $row->cargo_id }}"
                                                class="w-full h-full p-1 text-xs font-bold text-center text-blue-800 bg-transparent border-none focus:ring-0">
                                        </td>
                                        <td class="p-1 border border-gray-400 print-only">{{ $row->profit_percent }}%
                                        </td>

                                        <td class="p-1 border border-gray-400">
                                            {{ number_format($row->profit_value, 2) }}
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

                    <div class="overflow-x-auto">
                        <h2 class="text-xl font-black text-gray-800 decoration-blue-500">المنصرفات</h2>
                        <table class="w-full text-[11px] text-center border-collapse border border-gray-400 mt-2">
                            <table class="w-full text-sm border">
                                <thead class="bg-gray-100">
                                    <tr class="font-bold text-white bg-gray-800">
                                        <th class="p-1 border border-gray-400">#</th>
                                        <th class="p-1 border border-gray-400">النوع</th>
                                        <th class="p-1 border border-gray-400">المبلغ</th>
                                        <th class="p-1 border border-gray-400">ملاحظة</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($data['truck']->expenses as $i => $expense)
                                        <tr class="border-b border-gray-400 hover:bg-gray-50">
                                            <td class="p-2 border">{{ $i + 1 }}</td>
                                            <td class="p-2 border">{{ $expense->type->label }}</td>
                                            <td class="p-2 border">{{ number_format($expense->total_amount, 2) }}</td>
                                            <td class="p-2 border">{{ $expense->note }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="p-3 text-center text-gray-500">لا توجد مصروفات
                                                مسجلة
                                            </td>
                                        </tr>
                                    @endforelse
                                    <tr class="font-bold text-white uppercase bg-gray-800">
                                        <td colspan="2" class="p-2 text-right border">الإجمالي</td>
                                        <td class="p-2 border" colspan="2">
                                            {{ number_format($data['truck']->expenses->sum('total_amount'), 2) }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                    </div>

                </div>
            @endforeach
        </div>
        <x-print-button />
    @else
        <div class="p-20 text-center bg-white border-2 border-gray-300 border-dashed shadow rounded-xl">
            <h3 class="text-xl font-bold text-gray-400">الرجاء اختيار شاحنة أو شركة من القائمة أعلاه لعرض بيانات
                التسعير
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
                /* page-break-after: always; */
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
