<x-filament-panels::page>
    {{-- 1. الفلترة - تختفي عند الطباعة --}}
    <div class="mb-6 no-print">
        {{ $this->form }}
    </div>

    @if ($companyId)
        <div id='report-content' class="print:m-0 print:p-0">

            {{-- 2. الهيدر الرسمي --}}
            <x-report-header label="كشف حساب شركة: " :value="$_company?->name" />

            <div class="p-1 mt-2">
                {{-- 3. ملخص الحساب (Cards) --}}
                <div class="grid grid-cols-3 gap-4 mb-6 font-bold text-center print:gap-2 print:mb-4">
                    <div class="p-4 border-2 border-green-500 bg-green-50 rounded-xl print:p-2">
                        <p class="text-sm text-green-700 print:text-[10px]">الرصيد المتبقي (صافي)</p>
                        <h2
                            class="text-2xl print:text-lg tabular-nums {{ $summary['balance'] < 0 ? 'text-red-600' : 'text-green-700' }}">
                            {{ number_format($summary['balance'], 2) }}
                        </h2>
                    </div>
                    <div class="p-4 border-2 border-slate-200 rounded-xl bg-slate-50 print:p-2">
                        <p class="text-sm text-slate-500 print:text-[10px]">إجمالي المطالبات (الفواتير)</p>
                        <h2 class="text-2xl print:text-lg tabular-nums text-slate-800">
                            {{ number_format($summary['total_claims'], 2) }}</h2>
                    </div>
                    <div class="p-4 border-2 border-slate-200 rounded-xl bg-slate-50 print:p-2">
                        <p class="text-sm text-slate-500 print:text-[10px]">إجمالي المدفوعات (السداد)</p>
                        <h2 class="text-2xl print:text-lg tabular-nums text-slate-800">
                            {{ number_format($summary['total_paid'], 2) }}</h2>
                    </div>
                </div>

                {{-- 4. الجدول الرئيسي المصمم لـ A3 --}}
                <div class="overflow-x-auto">
                    <table
                        class="w-full text-[12px] text-center border-collapse border border-slate-800 print:text-[10px]">
                        <thead>
                            {{-- الصف الأول: عناوين رئيسية --}}
                            <tr class="font-bold border bg-slate-200 text-slate-800 border-slate-800">
                                <th class="p-2 border border-slate-800" rowspan="3">التاريخ / الشحنة</th>
                                <th class="p-2 border border-slate-800 bg-slate-100" colspan="10">بيان الفاتورة
                                    (الطلبية)</th>
                                <th class="p-2 bg-blue-100 border border-slate-800" colspan="3" rowspan="2">بيان
                                    السداد المالي</th>
                            </tr>
                            {{-- الصف الثاني: تصنيفات فرعية --}}
                            <tr class="font-bold text-white border bg-slate-800 border-slate-800">
                                <th colspan="4" class="p-1 border border-slate-600">بيانات الصنف</th>
                                <th colspan="3" class="p-1 border border-slate-600">الكميات</th>
                                <th colspan="3" class="p-1 border border-slate-600">التسعير</th>
                            </tr>
                            {{-- الصف الثالث: العناوين التفصيلية --}}
                            <tr class="font-bold bg-slate-50 text-slate-700">
                                <th class="w-6 p-1 border border-slate-400">#</th>
                                <th class="w-32 p-1 text-right border border-slate-400">الصنف</th>
                                <th class="p-1 border border-slate-400">المقاس</th>
                                <th class="p-1 border border-slate-400">و.الوحدة</th>
                                <th class="p-1 border border-slate-400">الطرد</th>
                                <th class="p-1 border border-slate-400">العدد</th>
                                <th class="p-1 border border-slate-400">الطن</th>
                                <th class="p-1 border border-slate-400">س.الوحدة</th>
                                <th class="p-1 border border-slate-400">س.الطن</th>
                                <th class="p-1 border border-slate-400 bg-slate-100">المجموع</th>
                                <th class="p-1 border border-slate-400 bg-blue-50">تاريخ السداد</th>
                                <th class="p-1 border border-slate-400 bg-blue-50">التفاصيل / المعامل</th>
                                <th class="p-1 border border-slate-400 bg-blue-50">المبلغ المسدد</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($groups as $group)
                                @php
                                    $rowsCount = max($group['cargos']->count(), $group['payments']->count());
                                    if ($rowsCount == 0) {
                                        $rowsCount = 1;
                                    }
                                @endphp

                                @for ($i = 0; $i < $rowsCount; $i++)
                                    <tr class="transition-colors hover:bg-slate-50">
                                        {{-- عمود التاريخ المدمج لكل مجموعة --}}
                                        @if ($i === 0)
                                            <td class="border border-slate-800 bg-white font-bold print:text-[9px]"
                                                rowspan="{{ $rowsCount }}">
                                                <div class="flex flex-col items-center">
                                                    <span>{{ $group['date'] }}</span>
                                                    <span
                                                        class="mt-1 px-2 py-0.5 bg-blue-600 text-white rounded text-[10px] print:text-[8px]">
                                                        شحنة #{{ $group['truck_id'] }}
                                                    </span>
                                                </div>
                                            </td>
                                        @endif

                                        {{-- بيانات البضاعة --}}
                                        @php $cargo = $group['cargos']->get($i); @endphp
                                        <td class="p-1 border border-slate-300 text-slate-500 bg-slate-50/30">
                                            {{ $i + 1 }}</td>
                                        <td class="p-1 font-medium text-right border border-slate-300">
                                            {{ $cargo?->product_name ?? '-' }}</td>
                                        <td class="p-1 border border-slate-300">{{ $cargo?->size ?? '-' }}</td>
                                        <td class="p-1 text-gray-500 border border-slate-300 tabular-nums">
                                            {{ $cargo ? number_format($cargo->unit_weight, 2) : '-' }}</td>
                                        <td class="p-1 border border-slate-300 tabular-nums">
                                            {{ $cargo ? number_format($cargo->quantity, 2) : '-' }}</td>
                                        <td class="p-1 border border-slate-300 tabular-nums">
                                            {{ $cargo ? number_format($cargo->unit_quantity, 2) : '-' }}</td>
                                        <td class="p-1 font-bold text-blue-800 border border-slate-300 tabular-nums">
                                            {{ $cargo ? number_format($cargo->weight_ton, 3) : '-' }}</td>
                                        <td class="p-1 text-gray-500 border border-slate-300 tabular-nums">
                                            {{ $cargo ? number_format($cargo->unit_price, 2) : '-' }}</td>
                                        <td class="p-1 text-gray-500 border border-slate-300 tabular-nums">
                                            {{ $cargo ? number_format($cargo->ton_price, 2) : '-' }}</td>
                                        <td class="p-1 font-bold border border-slate-300 tabular-nums bg-slate-100">
                                            {{ $cargo ? number_format($cargo->base_total_foreign, 2) : '-' }}</td>

                                        {{-- بيانات السداد --}}
                                        @php $payment = $group['payments']->get($i); @endphp
                                        <td class="p-1 border border-slate-300 bg-blue-50/30 text-[10px]">
                                            {{ $payment ? $payment->created_at->format('Y-m-d') : '-' }}
                                        </td>
                                        <td class="p-1 border border-slate-300 bg-blue-50/30 text-[10px] text-right">
                                            @if ($payment)
                                                <span class="text-blue-700">م: {{ $payment->rate }}</span> |
                                                <span class="text-slate-500">ح:
                                                    {{ number_format($payment->amount, 2) }}</span>
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td
                                            class="p-1 font-bold text-red-700 border border-slate-300 bg-blue-50/50 tabular-nums">
                                            {{ $payment ? number_format($payment->total, 2) : '-' }}
                                        </td>
                                    </tr>
                                @endfor

                                {{-- صف ملخص المجموعة (Sub-total) --}}
                                <tr class="font-bold bg-slate-800 text-white print:text-[10px]">
                                    <td class="bg-white border border-slate-800"></td> {{-- موازنة لعمود الـ rowspan --}}
                                    <td colspan="9" class="p-1 px-4 italic text-right border border-slate-700">إجمالي
                                        المجموعة (الشحنة)</td>
                                    <td class="p-1 border border-slate-700 bg-slate-900 tabular-nums">
                                        {{ number_format($group['total_invoice'], 2) }}</td>
                                    <td colspan="2" class="p-1 px-4 italic text-left border border-slate-700">المدفوع
                                    </td>
                                    <td class="p-1 bg-red-900 border border-slate-700 tabular-nums">
                                        {{ number_format($group['total_paid'], 2) }}</td>
                                </tr>
                                <tr class="font-bold bg-yellow-50">
                                    <td class="border-x border-slate-800"></td>
                                    <td colspan="10"
                                        class="p-1 border border-slate-300 text-left px-4 text-slate-500 text-[11px]">
                                        صافي فرق المجموعة:</td>
                                    <td colspan="3"
                                        class="p-1 border border-slate-800 text-center tabular-nums {{ $group['balance'] < 0 ? 'text-red-600' : 'text-green-700' }}">
                                        {{ number_format($group['balance'], 2) }}
                                    </td>
                                </tr>
                                {{-- سطر شفاف للفصل بين المجموعات --}}
                                <tr class="h-4 no-print">
                                    <td colspan="15"></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- 5. ذيل التقرير وتوقيعات --}}
                <div class="hidden grid-cols-2 gap-20 px-10 mt-12 print:grid">
                    <div class="pt-2 text-center border-t-2 border-slate-300">
                        <p class="font-bold text-slate-800">توقيع المحاسب</p>
                        <p class="mt-8 text-xs italic text-slate-400">حرر بتاريخ: {{ now()->format('Y-m-d') }}</p>
                    </div>
                    <div class="pt-2 text-center border-t-2 border-slate-300">
                        <p class="font-bold text-slate-800">ختم الشركة</p>
                    </div>
                </div>
            </div>

            {{-- زر الطباعة العائم --}}
            <div class="fixed bottom-6 left-6 no-print">
                <x-print-button />
            </div>
        </div>
    @else
        <div class="p-20 text-center bg-white border-2 border-gray-300 border-dashed shadow rounded-xl">
            <x-filament::icon icon="heroicon-o-magnifying-glass" class="w-12 h-12 mx-auto mb-4 text-gray-300" />
            <h3 class="text-xl font-bold text-gray-400">الرجاء اختيار شركة من القائمة لعرض التفاصيل المراجعة</h3>
        </div>
    @endif

    <style>
        @media print {
            @page {
                size: A3 landscape;
                margin: 10mm;
            }

            .no-print {
                display: none !important;
            }

            body {
                background: white !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            .fi-main-ctn {
                padding: 0 !important;
                margin: 0 !important;
                width: 100% !important;
            }

            tr {
                page-break-inside: avoid !important;
            }

            .tabular-nums {
                font-variant-numeric: tabular-nums;
            }
        }
    </style>
</x-filament-panels::page>
