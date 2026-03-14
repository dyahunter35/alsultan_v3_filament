<x-filament-panels::page>
    <div class="no-print">
        <x-filament::section class="mb-6 border-slate-200 shadow-sm">
            {{ $this->form }}
        </x-filament::section>
    </div>

    @if ($contractorId)
        <div id="report-content" class="p-0 bg-white print:p-0">

            <x-report-header label="كشف حساب مقاول نقل" :value="$contractor?->name" />

            {{-- 1. ملخص الحالة المالية - تصميم مطور --}}
            <div
                class="grid grid-cols-3 border-2 border-slate-950 mb-6 rounded-sm overflow-hidden shadow-sm print:shadow-none">

                <div class="flex flex-col items-center justify-center p-4 border-l-2 border-slate-950 bg-white">

                    <span class="text-[10px] font-bold text-slate-500 uppercase mb-1">إجمالي المطالبات | Claims</span>

                    <span
                        class="text-xl font-black tabular-nums text-slate-900">{{ number_format($summary['total_claims'], 2) }}</span>

                </div>

                <div class="flex flex-col items-center justify-center p-4 border-l-2 border-slate-950 bg-slate-50">

                    <span class="text-[10px] font-bold text-slate-500 uppercase mb-1">إجمالي المسدد | Paid</span>

                    <span
                        class="text-xl font-black tabular-nums text-emerald-700">{{ number_format($summary['total_paid'], 2) }}</span>

                </div>

                <div @class([

                    'flex flex-col items-center justify-center p-4',

                    'bg-rose-50' => $summary['balance'] < 0,

                    'bg-emerald-50' => $summary['balance'] >= 0,

                ])>

                    <span class="text-[10px] font-bold text-slate-500 uppercase mb-1">الرصيد النهائي | Balance</span>

                    <span @class([

                        'text-2xl font-black tabular-nums',

                        'text-rose-700' => $summary['balance'] < 0,

                        'text-emerald-700' => $summary['balance'] >= 0,

                    ])>

                        {{ number_format($summary['balance'], 2) }}

                    </span>

                </div>

            </div>

            {{-- 2. جدول البيانات --}}
            <div class="overflow-x-auto border-t-2 border-slate-950 shadow-sm">
                <table class="w-full text-center border-collapse text-[11px] print:text-[10px]">
                    <thead>
                        {{-- الرأس الرئيسي --}}
                        <tr class="bg-slate-900 text-white uppercase tracking-widest">
                            <th colspan="8" class="p-3 border border-slate-700 text-xs">تفاصيل الرحلة والشحنة</th>
                            <th colspan="3" class="p-3 border border-slate-700 bg-slate-800 text-xs">المبالغ المستحقة</th>
                            <th rowspan="2" class="p-3 border border-slate-700 bg-emerald-900 text-xs text-white">المسدد
                            </th>
                            <th rowspan="2" class="p-3 border border-slate-700 bg-blue-900 text-xs text-white">الرصيد</th>
                        </tr>
                        {{-- الرأس الفرعي --}}
                        <tr class="bg-slate-100 font-bold border-b-2 border-slate-400 text-slate-700">
                            <th class="p-2 border border-slate-300"># شحنة</th>
                            <th class="p-2 border border-slate-300">رقم اللوحة</th>
                            <th class="p-2 border border-slate-300 w-32">السائق</th>
                            <th class="p-2 border border-slate-300">شحن</th>
                            <th class="p-2 border border-slate-300">تفريغ</th>
                            <th class="p-2 border border-slate-300">المدة</th>
                            <th class="p-2 border border-slate-300">المصنع</th>
                            <th class="p-2 border border-slate-300">نوع الشحنة</th>

                            <th class="p-2 border border-slate-300">النولون</th>
                            <th class="p-2 border border-slate-300">العطلة</th>
                            <th class="p-2 border border-slate-300 bg-slate-200 text-slate-900 font-black">الإجمالي</th>
                        </tr>
                    </thead>
                    <tbody class="tabular-nums">
                        @if($this->dateRange && isset($summary['carried_balance']))
                            <tr class="bg-amber-50 font-bold border-b-2 border-amber-200">
                                <td colspan="12" class="p-3 text-right text-amber-900 border-x border-amber-200">
                                    <div class="flex items-center gap-2 px-2">
                                        <span class="w-2 h-2 bg-amber-600 rounded-full shadow-sm"></span>
                                        <span>رصيد مرحل حتى تاريخ: {{ explode(' - ', $this->dateRange)[0] }}</span>
                                    </div>
                                </td>
                                <td @class([
                                    'p-3 border-x border-amber-200 font-black text-[11px]',
                                    'text-rose-700' => $summary['carried_balance'] < 0,
                                    'text-emerald-700' => $summary['carried_balance'] >= 0,
                                ])>
                                    {{ number_format(abs($summary['carried_balance']), 2) }}
                                </td>
                            </tr>
                        @endif
                        @foreach ($rows as $row)
                            @if ($row['type'] === 'trip')
                                <tr class="hover:bg-slate-50 transition-colors border-b border-slate-200 group">
                                    <td class="p-2 border-x border-slate-200 font-bold bg-slate-50/50">{{ $row['truck_code'] }}</td>
                                    <td class="p-2 border-x border-slate-200">{{ $row['car_number'] }}</td>
                                    <td class="p-2 border-x border-slate-200 leading-tight text-right px-3">
                                        <div class="font-bold text-slate-800">{{ $row['driver_name'] }}</div>
                                        <div class="text-[9px] text-slate-500 italic">{{ $row['driver_phone'] }}</div>
                                    </td>
                                    <td class="p-2 border-x border-slate-200 font-medium">{{ $row['shipment_date'] }}</td>
                                    <td class="p-2 border-x border-slate-200 font-medium">{{ $row['discharge_date'] }}</td>
                                    <td class="p-2 border-x border-slate-200 bg-slate-50/30">{{ $row['duration'] }} ي</td>
                                    <td class="p-2 border-x border-slate-200 font-bold text-blue-800 uppercase text-[9px]">
                                        {{ $row['factory'] }}</td>
                                    <td
                                        class="p-2 border-x border-slate-200 text-[9px] italic text-slate-600 truncate max-w-[100px]">
                                        {{ $row['items'] }}</td>

                                    <td class="p-2 border-x border-slate-200">{{ number_format($row['fare'], 2) }}</td>
                                    <td @class(['p-2 border-x border-slate-200', 'text-amber-600 font-bold' => $row['delay'] > 0])>
                                        {{ $row['delay'] > 0 ? number_format($row['delay'], 2) : '-' }}
                                    </td>
                                    <td class="p-2 border-x border-slate-300 bg-slate-100 font-black text-slate-900">
                                        {{ number_format($row['total_amount'], 2) }}
                                    </td>

                                    <td class="p-2 border-x border-slate-200 font-bold text-emerald-700 bg-emerald-50/20">
                                        {{ $row['settlement_amount'] > 0 ? number_format($row['settlement_amount'], 2) : '-' }}
                                    </td>
                                    <td @class([
                                        'p-2 border-x border-blue-200 bg-blue-50/50 font-black text-[11px]',
                                        'text-rose-700' => $row['balance'] < 0,
                                        'text-emerald-700' => $row['balance'] >= 0,
                                    ])>
                                        {{ number_format(abs($row['balance']), 2) }}
                                    </td>
                                </tr>
                            @else
                                {{-- سطر السندات --}}
                                <tr class="bg-blue-50/40 font-bold border-y-2 border-blue-200">
                                    <td class="p-3 text-right text-blue-900 italic bg-blue-100/50" colspan="3">
                                        <div class="flex items-center gap-2 px-2">
                                            <span class=" bg-blue-600 rounded-full shadow-sm"></span>
                                            <span>سند صرف / تحويل بنكي</span>
                                        </div>
                                    </td>
                                    <td class="p-3 text-center text-blue-900 font-bold bg-blue-50" colspan="3">
                                        {{ $row['settlement_date'] }}
                                    </td>
                                    <td class="p-3 text-right text-blue-800 px-4" colspan="5">
                                        {{ $row['description'] }}
                                    </td>
                                    <td
                                        class="p-3 border-x border-blue-200 bg-emerald-100 text-emerald-900 font-black  shadow-inner">
                                        {{ number_format($row['settlement_amount'], 2) }}
                                    </td>
                                    <td class="p-3 border-x border-slate-200 bg-blue-50 font-black text-blue-900 ">
                                        {{ number_format(abs($row['balance']), 2) }}
                                    </td>
                                </tr>
                            @endif
                        @endforeach
                        <tr class="bg-slate-900 text-white font-black border-t-2 border-slate-950">
                            <td colspan="8" class="p-4 text-left uppercase tracking-widest">إجمالي أرصدة الكشف | Grand
                                Totals</td>
                            <td colspan="2" class="p-4 bg-slate-800 border-l border-slate-700"></td>
                            <td class="p-4 bg-slate-800 tabular-nums border-l border-slate-700 shadow-inner">
                                {{ number_format($summary['total_claims'], 2) }}
                            </td>
                            <td class="p-4 bg-emerald-800 tabular-nums border-l border-slate-700 shadow-inner">
                                {{ number_format($summary['total_paid'], 2) }}
                            </td>
                            <td @class([
                                'p-4 tabular-nums shadow-inner',
                                'bg-rose-900' => $summary['balance'] < 0,
                                'bg-blue-900' => $summary['balance'] >= 0,
                            ])>
                                {{ number_format(abs($summary['balance']), 2) }}
                            </td>
                        </tr>
                    </tbody>

                </table>
            </div>

            {{-- 3. التوقيعات --}}
            <div class="mt-16 hidden print:grid grid-cols-3 gap-16 text-center">
                <div class="flex flex-col gap-2">
                    <div class="h-16 border-b border-slate-300 border-dashed"></div>
                    <span class="text-[10px] font-bold text-slate-500 uppercase italic">توقيع المحاسب المسؤول</span>
                </div>
                <div class="flex flex-col gap-2">
                    <div class="h-16 border-b border-slate-300 border-dashed"></div>
                    <span class="text-[10px] font-bold text-slate-500 uppercase italic">توقيع مراجعة المقاول</span>
                </div>
                <div class="flex flex-col gap-2 bg-slate-50 p-2 rounded-sm border border-slate-200">
                    <div class="h-14"></div>
                    <span
                        class="text-[10px] font-black text-slate-950 uppercase italic border-t-2 border-slate-950 pt-2">ختم
                        واعتماد الإدارة</span>
                </div>
            </div>

            <div
                class="mt-8 flex justify-between items-center text-[8px] text-slate-400 hidden print:flex uppercase tracking-tighter italic">
                <span>تاريخ التقرير: {{ now()->format('Y-m-d H:i') }}</span>
                <span>بواسطة: {{ auth()->user()->name }}</span>
                <span>Integrated Logistics System - v4.0</span>
            </div>

            <x-print-button />
        </div>
    @endif

    <style>
        #report-content {
            font-family: 'FlatJooza', 'Inter', sans-serif;
        }

        @media print {
            @page {
                size: A4 landscape;
                margin: 8mm;
            }

            .no-print {
                display: none !important;
            }

            body {
                background: white !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            table {
                border: 1px solid #000 !important;
            }

            th,
            td {
                border: 1px solid #ccc !important;
            }
        }

        /* تحسين مظهر الأرقام */
        .tabular-nums {
            font-variant-numeric: tabular-nums;
        }
    </style>
</x-filament-panels::page>