<x-filament-panels::page>
    {{-- 1. قسم الفلترة (يختفي عند الطباعة) --}}
    <div class="mb-6 no-print">
        {{ $this->form }}
    </div>

    @php
        $reports = $this->report_data;
    @endphp

    @if ($reports)
        <div id='report-content' class="print:m-0 print:p-0">

            {{-- 2. الهيدر العام والمعادل --}}
            <x-filament::section class="mb-4 print:shadow-none print:border-slate-300 print:mb-2">
                @if ($truck_id)
                    <x-report-header label="تقرير الشحنة رقم :" :value="$reports[0]['truck']?->id ?? '-'" />
                @else
                    <x-report-header label="اسم الشركة:" :value="$_company?->name ?? '-'" />
                @endif

                <dl class="grid grid-cols-3 gap-4 my-2 text-sm text-center md:grid-cols-3 print:my-1 print:gap-2">
                    <div class="p-1 print:border print:border-slate-200 print:rounded-md">
                        <dt class="font-bold text-gray-600 print:text-[10px]">العملة الافتراضية</dt>
                        <dd class="font-black text-slate-800">{{ $currency_name ?? '' }}</dd>
                    </div>

                    <div>{{-- مساحة فارغة أو إضافية --}}</div>

                    <div class="p-1 print:border print:border-slate-200 print:rounded-md">
                        <dt class="font-bold text-gray-600 print:text-[10px]">المعادل (سعر الصرف)</dt>
                        <dd class="font-black text-slate-800">{{ number_format($exchange_rate, 2) }}</dd>
                    </div>
                </dl>
            </x-filament::section>

            {{-- 3. حلقة تكرار البيانات (الشاحنات) --}}
            @foreach ($reports as $data)
                <div class="p-0 mb-6 print:mb-4 print:break-inside-avoid">

                    <div class="p-4 bg-white border border-gray-200 rounded-lg shadow-md print:shadow-none print:border-slate-400 print:p-2"
                        dir="rtl">

                        {{-- معلومات الشاحنة --}}
                        <div
                            class="flex items-center justify-between pb-2 mb-4 border-b-2 border-gray-100 print:mb-2 print:pb-1">
                            <div>
                                <h2 class="flex items-center gap-2 text-lg font-black text-gray-800 print:text-sm">
                                    @if ($truck_id)
                                        <x-filament::icon icon="heroicon-m-building-office-2"
                                            class="w-5 h-5 text-blue-600" />
                                        <span>الشركة: {{ $data['truck']->companyId?->name ?? '' }}</span>
                                    @else
                                        <x-filament::icon icon="heroicon-m-truck" class="w-5 h-5 text-blue-600" />
                                        <span>بيان الشحنة رقم: {{ $data['truck']->id }}</span>
                                    @endif
                                </h2>
                                <p class="mt-1 font-bold text-gray-600 text-sm print:text-[10px]">
                                    رقم اللوحة: <span class="text-slate-900">{{ $data['truck']->car_number }}</span>
                                    | تاريخ الشحنة: <span
                                        class="text-slate-900">{{ $data['truck']->created_at->format('Y-m-d') }}</span>
                                </p>
                            </div>
                        </div>

                        {{-- 4. الجدول الرئيسي (تفاصيل التحليل) --}}
                        <div class="overflow-x-auto print:overflow-visible">
                            <table
                                class="w-full text-[12px] text-center border-collapse border border-gray-400 print:text-[9px]">
                                <thead>
                                    <tr class="font-bold text-white bg-slate-800 print:bg-slate-800">
                                        <th colspan="16" class="p-1 tracking-wider uppercase border border-gray-400">
                                            تفاصيل التحليل الفني والمالي</th>
                                        <th colspan="4" class="p-1 border border-gray-400 bg-slate-700">التسعير
                                            النهائي</th>
                                    </tr>
                                    <tr class="font-bold text-white bg-slate-700 print:bg-slate-700">
                                        <th colspan="4" class="p-1 border border-gray-400">بيانات الصنف</th>
                                        <th colspan="3" class="p-1 border border-gray-400">الكميات</th>
                                        <th colspan="2" class="p-1 border border-gray-400">سعر الشراء</th>
                                        <th colspan="4" class="p-1 border border-gray-400">حساب التكلفة</th>
                                        <th colspan="3" class="p-1 border border-gray-400">هامش الربح</th>
                                        <th colspan="2" class="p-1 border border-gray-400">سعر الطرد</th>
                                        <th colspan="2" class="p-1 border border-gray-400">سعر الطن</th>
                                    </tr>
                                    <tr
                                        class="font-bold text-gray-700 bg-slate-100 print:bg-slate-100 print:text-[8px]">
                                        <th class="p-1 border border-gray-400">#</th>
                                        <th class="w-32 p-1 border border-gray-400">الصنف</th>
                                        <th class="p-1 border border-gray-400">المقاس</th>
                                        <th class="p-1 border border-gray-400">وزن</th>
                                        <th class="p-1 border border-gray-400">الطرد</th>
                                        <th class="p-1 border border-gray-400">العدد</th>
                                        <th class="p-1 text-blue-800 border border-gray-400">الطن</th>
                                        <th class="p-1 border border-gray-400">الوحدة</th>
                                        <th class="p-1 border border-gray-400">الطن</th>
                                        <th class="p-1 border border-gray-400">المجموع</th>
                                        <th class="p-1 border border-gray-400">الترحيل</th>
                                        <th class="p-1 border border-gray-400">المنصرف</th>
                                        <th class="p-1 bg-yellow-100 border border-gray-400">التكلفة</th>
                                        <th class="w-12 p-1 bg-blue-100 border border-gray-400">الربح%</th>
                                        <th class="p-1 border border-gray-400">قيمة ر</th>
                                        <th class="p-1 font-bold text-green-700 border border-gray-400">سعر البيع</th>
                                        <th class="p-1 border border-gray-400">SDG</th>
                                        <th class="p-1 border border-gray-400">{{ $currency_name }}</th>
                                        <th class="p-1 font-bold text-blue-800 border border-gray-400">SDG</th>
                                        <th class="p-1 border border-gray-400">{{ $currency_name }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($data['rows'] as $row)
                                        <tr
                                            class="border-b border-gray-400 hover:bg-gray-50 text-[11px] print:text-[9px] tabular-nums">
                                            <td class="p-1 border border-gray-400 bg-gray-50">{{ $row->index }}</td>
                                            <td
                                                class="p-1 font-bold text-right border border-gray-400 whitespace-nowrap">
                                                {{ $row->product_name }}</td>
                                            <td class="p-1 border border-gray-400">{{ $row->size }}</td>
                                            <td class="p-1 border border-gray-400">{{ $row->unit_weight }}</td>
                                            <td class="p-1 border border-gray-400">{{ $row->quantity }}</td>
                                            <td class="p-1 border border-gray-400">{{ $row->unit_quantity }}</td>
                                            <td class="p-1 font-bold text-blue-800 border border-gray-400">
                                                {{ number_format($row->weight_ton, 3) }}</td>
                                            <td class="p-1 border border-gray-400">
                                                {{ number_format($row->unit_price, 2) }}</td>
                                            <td class="p-1 border border-gray-400">
                                                {{ number_format($row->ton_price, 2) }}</td>
                                            <td class="p-1 border border-gray-400">
                                                {{ number_format($row->base_total_foreign, 2) }}</td>
                                            <td class="p-1 border border-gray-400">
                                                {{ number_format($row->transport_cost, 2) }}</td>
                                            <td class="p-1 border border-gray-400">
                                                {{ number_format($row->customs_cost, 2) }}</td>
                                            <td class="p-1 font-bold border border-gray-400 bg-yellow-50">
                                                {{ number_format($row->total_cost, 2) }}</td>

                                            {{-- الربح - مدخل في النظام ومخفي في الطباعة --}}
                                            <td class="p-0 border border-gray-400 bg-blue-50 no-print">
                                                <input type="number" step="0.5"
                                                    wire:model.live.debounce.500ms="profit_percents.{{ $row->cargo_id }}"
                                                    class="w-full h-full p-1 text-xs font-bold text-center text-blue-800 bg-transparent border-none focus:ring-0">
                                            </td>
                                            <td class="p-1 text-[10px] border border-gray-400 print-only">
                                                {{ $row->profit_percent }}%</td>

                                            <td class="p-1 border border-gray-400">
                                                {{ number_format($row->profit_value, 2) }}</td>
                                            <td class="p-1 font-bold text-green-700 border border-gray-400 bg-green-50">
                                                {{ number_format($row->selling_price_foreign, 2) }}</td>
                                            <td
                                                class="p-1 font-bold text-green-900 bg-green-100 border border-gray-400">
                                                {{ number_format($row->package_price_sdg, 0) }}</td>
                                            <td class="p-1 font-bold border border-gray-400 bg-gray-50">
                                                {{ number_format($row->package_price_foreign, 2) }}</td>
                                            <td
                                                class="p-1 font-black text-orange-900 bg-yellow-100 border border-gray-400">
                                                {{ number_format($row->ton_price_sdg, 0) }}</td>
                                            <td class="p-1 font-bold text-gray-600 border border-gray-400">
                                                {{ number_format($row->ton_price_foreign, 0) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="font-bold text-white bg-slate-800 print:text-[9px]">
                                    <tr>
                                        <td colspan="4" class="p-1 border border-gray-400">الإجمالي العام</td>
                                        <td class="p-1 border border-gray-400">
                                            {{ number_format($data['totals']['quantity']) }}</td>
                                        <td class="p-1 border border-gray-400"></td>
                                        <td class="p-1 font-bold text-yellow-400 border border-gray-400">
                                            {{ number_format($data['totals']['weight'], 3) }}</td>
                                        <td colspan="2" class="p-1 border border-gray-400"></td>
                                        <td class="p-1 border border-gray-400">
                                            {{ number_format($data['totals']['base_foreign'], 2) }}</td>
                                        <td class="p-1 border border-gray-400">
                                            {{ number_format($data['totals']['transport'], 2) }}</td>
                                        <td class="p-1 border border-gray-400">
                                            {{ number_format($data['totals']['customs'], 2) }}</td>
                                        <td class="p-1 text-black bg-yellow-500 border border-gray-400">
                                            {{ number_format($data['totals']['total_cost'], 2) }}</td>
                                        <td class="p-1 border border-gray-400 no-print"></td>
                                        <td class="p-1 border border-gray-400">
                                            {{ number_format($data['totals']['profit'], 2) }}</td>
                                        <td class="p-1 bg-green-700 border border-gray-400">
                                            {{ number_format($data['totals']['selling_foreign'], 2) }}</td>
                                        <td colspan="4" class="p-1 border border-gray-400 bg-slate-700"></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                        {{-- فاصل بتدرج شفاف --}}
                        <div class="w-full h-px my-4 bg-gradient-to-r from-transparent via-slate-400 to-transparent">
                        </div>

                        {{-- 5. قسم المنصرفات وحسابات الطريق (جنباً إلى جنب في A3) --}}
                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2 print:grid-cols-2">

                            {{-- جدول المنصرفات --}}
                            <div class="overflow-x-auto">
                                <h3
                                    class="flex items-center gap-2 mb-2 text-sm font-black text-gray-800 print:text-xs">
                                    <x-filament::icon icon="heroicon-m-credit-card" class="w-4 h-4 text-red-600" />
                                    <span>المنصرفات ({{ $data['truck']->category?->name }})</span>
                                </h3>
                                <table
                                    class="w-full text-xs text-center border-collapse border border-gray-400 print:text-[10px]">
                                    <thead class="text-white bg-gray-800">
                                        <tr>
                                            <th class="p-1 border border-gray-400">#</th>
                                            <th class="p-1 border border-gray-400">النوع</th>
                                            <th class="p-1 border border-gray-400">المبلغ</th>
                                            <th class="p-1 border border-gray-400">ملاحظة</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($data['truck']->expenses as $i => $expense)
                                            <tr class="border-b border-gray-400 hover:bg-gray-50">
                                                <td class="p-1 border">{{ $i + 1 }}</td>
                                                <td class="p-1 px-2 text-right border">{{ $expense->type->label }}
                                                </td>
                                                <td class="p-1 font-bold border">
                                                    {{ number_format($expense->total_amount, 2) }}</td>
                                                <td class="p-1 border text-[10px] text-gray-500">{{ $expense->note }}
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4" class="p-2 text-center text-gray-400">لا توجد
                                                    مصروفات</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                    <tfoot class="font-bold text-white bg-gray-800">
                                        <tr>
                                            <td colspan="2" class="p-1 px-2 text-left border">الإجمالي</td>
                                            <td class="p-1 border" colspan="2">
                                                {{ number_format($data['truck']->expenses->sum('total_amount'), 2) }}
                                            </td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>

                            {{-- جدول حسابات الطريق --}}
                            <div class="overflow-x-auto">
                                <h3
                                    class="flex items-center gap-2 mb-2 text-sm font-black text-gray-800 print:text-xs">
                                    <x-filament::icon icon="heroicon-m-map-pin" class="w-4 h-4 text-orange-600" />
                                    <span>حسابات الطريق</span>
                                </h3>
                                <table
                                    class="w-full text-xs text-center border-collapse border border-gray-400 print:text-[10px]">
                                    <thead class="text-white bg-gray-800">
                                        <tr>
                                            <th class="p-1 border border-gray-400">البيان</th>
                                            <th class="p-1 border border-gray-400">المبلغ بالعملة</th>
                                        </tr>
                                    </thead>
                                    <tbody class="font-bold">
                                        <tr class="border-b border-gray-400">
                                            <td class="p-2 px-4 text-right border bg-gray-50">النولون</td>
                                            <td class="p-2 border">
                                                {{ number_format($data['truck']->truck_fare ?? 0, 2) }}</td>
                                        </tr>
                                        <tr class="border-b border-gray-400">
                                            <td class="p-2 px-4 text-right border bg-gray-50">العطلات</td>
                                            <td class="p-2 border">
                                                {{ number_format($data['truck']->delay_value ?? 0, 2) }}</td>
                                        </tr>
                                    </tbody>
                                    <tfoot class="font-bold text-white bg-gray-800">
                                        <tr>
                                            <td class="p-1 px-4 text-right border">إجمالي الطريق</td>
                                            <td class="p-1 border">
                                                {{ number_format($data['truck']?->truck_fare_sum, 2) }}</td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>

                    </div>
                </div>
            @endforeach
        </div>

        <x-print-button />
    @else
        <div class="p-20 text-center bg-white border-2 border-gray-300 border-dashed shadow rounded-xl">
            <x-filament::icon icon="heroicon-o-magnifying-glass" class="w-12 h-12 mx-auto mb-4 text-gray-300" />
            <h3 class="text-xl font-bold text-gray-400">الرجاء اختيار شاحنة أو شركة من القائمة أعلاه لعرض بيانات
                التسعير</h3>
        </div>
    @endif

    {{-- 6. قسم التنسيقات الخاصة بالطباعة A3 --}}
    <style>
        @media print {

            /* إعدادات الصفحة A3 بالعرض */
            @page {
                size: A3 landscape;
                margin: 8mm;
            }

            /* إخفاء واجهة المستخدم */
            .no-print,
            .fi-sidebar,
            .fi-topbar,
            .fi-header,
            .fi-main-ctn>header {
                display: none !important;
            }

            /* تمديد مساحة المحتوى */
            .fi-main-ctn {
                padding: 0 !important;
                margin: 0 !important;
                width: 100% !important;
            }

            body {
                background: white !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            /* تحسين الجداول للطباعة المكثفة */
            table {
                table-layout: auto !important;
                border-color: #64748b !important;
                /* slate-500 */
            }

            th,
            td {
                padding: 2px 3px !important;
                line-height: 1.1 !important;
                border: 1px solid #64748b !important;
            }

            /* إظهار العناصر المخفية في المتصفح والظاهرة في الطباعة */
            .print-only {
                display: table-cell !important;
            }

            /* تحسين الألوان لتبدو واضحة */
            .bg-slate-800 {
                background-color: #1e293b !important;
                color: white !important;
            }

            .bg-yellow-100 {
                background-color: #fef9c3 !important;
            }

            .bg-green-100 {
                background-color: #dcfce7 !important;
            }

            /* منع قطع البيانات بشكل سيء */
            tr {
                page-break-inside: avoid !important;
            }
        }

        .print-only {
            display: none;
        }

        /* تنسيقات الأرقام لتكون واضحة ومصطفة */
        .tabular-nums {
            font-variant-numeric: tabular-nums;
        }
    </style>
</x-filament-panels::page>
