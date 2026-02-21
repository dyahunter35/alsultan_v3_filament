<div>
    {{-- قسم الفلترة - يختفي عند الطباعة --}}
    <div class="p-4 my-4 bg-white border border-gray-200 shadow-sm rounded-xl dark:bg-gray-800 no-print">
        {{ $this->form }}
    </div>

    @if ($truck)
        <div id="report-content" class="m-0 print:m-0">

            {{-- الهيدر الاحترافي --}}
            <x-report-header label="تقرير بيان شحنة رقم" :value="$truck->id" />

            {{-- 1. تفاصيل الشاحنة الأساسية --}}
            <x-filament::section class="mb-2 print:shadow-none print:border-slate-300">
                <dl class="grid grid-cols-3 gap-4 my-1 text-sm text-center md:grid-cols-3 print:my-0">
                    <div class="print:border-l print:border-slate-200 last:border-0">
                        <dt class="font-bold text-gray-500 print:text-[10px]">رقم اللوحة</dt>
                        <dd class="text-lg font-black text-gray-800 print:text-base">{{ $truck->car_number }}</dd>
                    </div>
                    <div class="print:border-l print:border-slate-200 last:border-0">
                        <dt class="font-bold text-gray-500 print:text-[10px]">الموديل</dt>
                        <dd class="text-lg font-black text-gray-800 print:text-base">{{ $truck->truck_model }}</dd>
                    </div>
                    <div>
                        <dt class="font-bold text-gray-500 print:text-[10px]">اسم الشركة</dt>
                        <dd class="text-lg font-black text-gray-800 print:text-base">
                            {{ $truck->companyId?->name ?? '-' }}</dd>
                    </div>
                </dl>
            </x-filament::section>

            {{-- 2. تفاصيل البضائع --}}
            <x-filament::section class="mb-4 print:shadow-none print:border-slate-300">
                <x-slot name="heading">
                    <span class="flex items-center gap-2">
                        <x-filament::icon icon="heroicon-m-shopping-bag" class="w-5 h-5 text-blue-600" />
                        تفاصيل البضائع المحملة
                    </span>
                </x-slot>

                <div class="overflow-x-auto">
                    <table class="w-full text-center border-collapse border border-slate-400 print:text-[11px]">
                        <thead>
                            <tr class="font-bold text-white bg-slate-800 print:bg-slate-800">
                                <th class="p-2 border border-slate-400">#</th>
                                <th class="p-2 text-right border border-slate-400">اسم المنتج</th>
                                <th class="p-2 text-right border border-slate-400">المقاس</th>
                                <th class="p-2 border border-slate-400">الوزن (جم)</th>
                                <th class="p-2 border border-slate-400">الوزن (طن)</th>
                                <th class="p-2 border border-slate-400">الكمية (طرد)</th>
                                <th class="p-2 border border-slate-400">الكمية (عدد)</th>
                                <th class="p-2 border border-slate-400">الكمية الفعلية</th>
                                <th class="p-2 border border-slate-400">الفرق</th>
                                <th class="p-2 border border-slate-400">ملاحظة</th>
                            </tr>
                        </thead>
                        <tbody class="tabular-nums">
                            @forelse($rows as $i => $row)
                                <tr class="border-b hover:bg-slate-50 border-slate-300">
                                    <td class="p-2 border border-slate-300 bg-slate-50">{{ $i + 1 }}</td>
                                    <td class="p-2 font-bold text-right border border-slate-300">
                                        {{ $row['product_name'] }}</td>
                                    <td class="p-2 border border-slate-300">{{ $row['size'] }}</td>
                                    <td class="p-2 border border-slate-300">{{ number_format($row['weight_grams'], 2) }}
                                    </td>
                                    <td class="p-2 font-bold border border-slate-300">
                                        {{ number_format($row['weight_ton'], 3) }}</td>
                                    <td class="p-2 border border-slate-300">{{ number_format($row['quantity'], 2) }} </td>
                                    <td class="p-2 border border-slate-300">{{ number_format($row['unit_quantity'], 2) }} </td>
                                    <td class="p-2 border border-slate-300">
                                        {{ number_format($row['real_quantity'], 2) }}</td>
                                    <td class="p-2 font-black border border-slate-300"
                                        style="color: {{ $row['dif'] >= 0 ? '#16a34a' : '#dc2626' }}">
                                        {{ $truck->is_converted ? number_format($row['dif'], 2) : '-' }}
                                    </td>
                                    <td class="p-2 border border-slate-300 text-[10px]">{{ $row['note'] }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="p-4 italic text-center text-gray-400">لا توجد بيانات بضائع
                                        مسجلة</td>
                                </tr>
                            @endforelse
                            <tr class="font-black border-t-2 bg-slate-100 text-slate-900 border-slate-400">
                                <td colspan="2"
                                    class="p-2 text-right uppercase border border-slate-400 bg-slate-200">الإجمالي العام
                                </td>
                                <td  colspan="2" class="p-2 border border-slate-400">
                                   </td>

                                <td class="p-2 text-blue-900 border border-slate-400 bg-yellow-50">
                                    {{ number_format(array_sum(array_column($rows, 'weight_ton')), 3) }}</td>
                                <td class="p-2 border border-slate-400">
                                    {{ number_format(array_sum(array_column($rows, 'quantity')), 2) }}</td>
                                <td class="p-2 border border-slate-400"> </td>
                                <td class="p-2 border border-slate-400">

                                    {{ number_format(array_sum(array_column($rows, 'real_quantity')), 2) }}</td>
                                <td class="p-2 border border-slate-400"
                                    style="color: {{ array_sum(array_column($rows, 'dif')) >= 0 ? '#16a34a' : '#dc2626' }}">
                                    {{ $truck->is_converted ? number_format(array_sum(array_column($rows, 'dif')), 2) : '-' }}
                                </td>
                                <td class="p-2 border border-slate-400 bg-slate-200"></td>
                            </tr>
                        </tbody>

                    </table>
                </div>
            </x-filament::section>

            {{-- 3. المنصرفات وحسابات الترحيل (جنباً إلى جنب في الطباعة) --}}
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 print:grid-cols-2">

                {{-- <x-filament::section class="print:shadow-none print:border-slate-300">
                    <x-slot name="heading">المنصرفات ( {{ $truck?->category?->name }} )</x-slot>
                    <table class="w-full text-xs text-center border-collapse border border-slate-400 print:text-[10px]">
                        <thead class="font-bold text-white bg-slate-800">
                            <tr>
                                <th class="p-2 border border-slate-400">#</th>
                                <th class="p-2 border border-slate-400">النوع</th>
                                <th class="p-2 border border-slate-400">المبلغ</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($truck->expenses as $i => $expense)
                                <tr class="border-b border-slate-300">
                                    <td class="p-1 border border-slate-300">{{ $i + 1 }}</td>
                                    <td class="p-1 px-2 text-right border border-slate-300">{{ $expense->type->label }}
                                    </td>
                                    <td class="p-1 font-bold border border-slate-300">
                                        {{ number_format($expense->total_amount, 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="p-2 italic text-center text-gray-400">لا توجد مصروفات</td>
                                </tr>
                            @endforelse
                        </tbody>
                        <tfoot>
                            <tr class="font-black bg-slate-100">
                                <td colspan="2" class="p-2 px-4 text-left uppercase border border-slate-400">إجمالي
                                    المصاريف</td>
                                <td class="p-2 text-red-700 border border-slate-400">
                                    {{ number_format($truck->expenses->sum('total_amount'), 2) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </x-filament::section> --}}

                {{-- <x-filament::section class="print:shadow-none print:border-slate-300">
                    <x-slot name="heading">📊 حسابات الترحيل </x-slot>
                    <div class="flex flex-col justify-between h-full">
                        <table class="w-full text-sm border border-slate-400 print:text-[11px]">
                            <tbody>
                                <tr class="border-b border-slate-300">
                                    <td class="p-3 font-semibold text-gray-700 border-l bg-slate-50 border-slate-300">
                                        النولون الأساسي</td>
                                    <td class="p-3 font-black text-left text-slate-900">
                                        {{ number_format($truck->truck_fare ?? 0, 2) }}</td>
                                </tr>
                                <tr class="border-b border-slate-300">
                                    <td class="p-3 font-semibold text-gray-700 border-l bg-slate-50 border-slate-300">
                                        تكلفة العطلات (Delay)</td>
                                    <td class="p-3 font-black text-left text-red-600">
                                        {{ number_format($truck->delay_value ?? 0, 2) }}</td>
                                </tr>
                                <tr class="bg-blue-50">
                                    <td
                                        class="p-4 text-lg font-black text-blue-900 uppercase border-l border-slate-400">
                                        إجمالي الترحيل</td>
                                    <td class="p-4 text-2xl font-black text-left text-blue-900 tabular-nums">
                                        {{ number_format($truck->truck_fare_sum, 2) }}
                                    </td>
                                </tr>
                            </tbody>
                        </table> 

                        
                    </div>
                </x-filament::section>--}}

                
            </div> 
            {{-- ملاحظة التوقيع للطباعة --}}
            <div class="hidden print:flex justify-between mt-8 px-4 italic text-slate-400 text-[10px]">
                <div>توقيع المسؤول: ............................</div>
                <div>ختم الشركة: ............................</div>
            </div>
        </div>

        {{-- زر الطباعة (يختفي في الطباعة) --}}
        <div class="fixed bottom-6 left-6 no-print">
            <x-print-button />
        </div>
    @endif

    {{-- 4. إعدادات الطباعة الاحترافية A3/A4 --}}
    <style>
        @media print {
            @page {
                /* size: A3 portrait; */
                /* أو A4 landscape حسب رغبتك */
                margin: 10mm;
            }

            /* إخفاء عناصر واجهة Filament */
            .no-print,
            .fi-sidebar,
            .fi-topbar,
            .fi-header,
            .fi-main-ctn>header {
                display: none !important;
            }

            .fi-main-ctn {
                padding: 0 !important;
                margin: 0 !important;
                width: 100% !important;
            }

            body {
                background: white !important;
                color: black !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            /* تحسين الجداول للطباعة */
            table {
                border-color: #94a3b8 !important;
                /* slate-400 */
                page-break-inside: auto;
            }

            tr {
                page-break-inside: avoid;
                page-break-after: auto;
            }

            /* تلوين الخلفيات في الطباعة */
            .bg-slate-800 {
                background-color: #1e293b !important;
                color: white !important;
            }

            .bg-slate-100 {
                background-color: #f1f5f9 !important;
            }

            .bg-slate-50 {
                background-color: #f8fafc !important;
            }

            .bg-yellow-50 {
                background-color: #fefce8 !important;
            }

            .bg-blue-50 {
                background-color: #eff6ff !important;
            }

            .tabular-nums {
                font-variant-numeric: tabular-nums;
            }
        }
    </style>
</div>
