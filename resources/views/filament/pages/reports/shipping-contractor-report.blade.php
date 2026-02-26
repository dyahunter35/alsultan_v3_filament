<x-filament-panels::page>
    {{-- قسم الفلترة - يختفي عند الطباعة --}}
    <div class="no-print">
        <x-filament::section class="mb-6 border-slate-200 shadow-sm">
            {{ $this->form }}
        </x-filament::section>
    </div>

    @if ($contractorId)
        <div id="report-content" class="p-0 bg-white print:p-0">
            
            {{-- الهيدر الرسمي --}}
            <x-report-header label="كشف حساب مقاول نقل" :value="$contractor?->name" />

            {{-- 1. صناديق الحالة المالية (ملخص الحساب) --}}
            <div class="grid grid-cols-3 border-2 border-slate-950 mb-6 rounded-sm overflow-hidden shadow-sm print:shadow-none">
                <div class="flex flex-col items-center justify-center p-4 border-l-2 border-slate-950 bg-white">
                    <span class="text-[10px] font-bold text-slate-500 uppercase mb-1">إجمالي المطالبات | Claims</span>
                    <span class="text-xl font-black tabular-nums text-slate-900">{{ number_format($summary['total_claims'], 2) }}</span>
                </div>
                <div class="flex flex-col items-center justify-center p-4 border-l-2 border-slate-950 bg-slate-50">
                    <span class="text-[10px] font-bold text-slate-500 uppercase mb-1">إجمالي المسدد | Paid</span>
                    <span class="text-xl font-black tabular-nums text-emerald-700">{{ number_format($summary['total_paid'], 2) }}</span>
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
            <div class="overflow-x-auto border-t-2 border-slate-950">
                <table class="w-full text-center border-collapse text-[11px] print:text-[10px]">
                    <thead>
                        {{-- الرأس الأول --}}
                        <tr class="bg-slate-900 text-white">
                            <th colspan="7" class="p-2 border border-slate-700 uppercase tracking-widest">تفاصيل الرحلات والشحنات</th>
                            <th colspan="3" class="p-2 border border-slate-700 bg-slate-800 uppercase tracking-widest">المبالغ المستحقة</th>
                            <th colspan="3" class="p-2 border border-slate-700 bg-blue-900 uppercase tracking-widest">حالة السداد</th>
                        </tr>
                        {{-- الرأس الثاني --}}
                        <tr class="bg-slate-100 font-bold border-b-2 border-slate-400 text-slate-700">
                            <th class="p-2 border border-slate-300"># شحنة</th>
                            <th class="p-2 border border-slate-300">الشاحنة</th>
                            <th class="p-2 border border-slate-300">السائق</th>
                            <th class="p-2 border border-slate-300">تاريخ الشحن</th>
                            <th class="p-2 border border-slate-300">تاريخ التفريغ</th>
                            <th class="p-2 border border-slate-300">المدة</th>
                            <th class="p-2 border border-slate-300">المصنع</th>
                            
                            <th class="p-2 border border-slate-300">النولون</th>
                            <th class="p-2 border border-slate-300">العطلة</th>
                            <th class="p-2 border border-slate-300 bg-slate-200 font-black">الإجمالي</th>

                            <th class="p-2 border border-slate-300 bg-blue-50/50">تاريخ السداد</th>
                            <th class="p-2 border border-slate-300 bg-blue-50/50">البيان</th>
                            <th class="p-2 border border-slate-300 bg-blue-100 font-black text-rose-700">المبلغ</th>
                        </tr>
                    </thead>
                    <tbody class="tabular-nums">
                        @foreach ($rows as $row)
                            @if ($row['type'] === 'trip')
                                <tr class="hover:bg-slate-50 transition-colors border-b border-slate-200">
                                    <td class="p-2 border-x border-slate-200 font-bold">{{ $row['truck_id'] }}</td>
                                    <td class="p-2 border-x border-slate-200 text-slate-600">{{ $row['car_number'] }}</td>
                                    <td class="p-2 border-x border-slate-200 leading-tight">
                                        <div class="font-bold text-slate-800">{{ $row['driver_name'] }}</div>
                                        <div class="text-[9px] text-slate-500">{{ $row['driver_phone'] }}</div>
                                    </td>
                                    <td class="p-2 border-x border-slate-200">{{ $row['shipment_date'] }}</td>
                                    <td class="p-2 border-x border-slate-200">{{ $row['discharge_date'] }}</td>
                                    <td class="p-2 border-x border-slate-200">{{ $row['duration'] }} ي</td>
                                    <td class="p-2 border-x border-slate-200 text-[9px] font-bold italic">{{ $row['factory'] }}</td>
                                    
                                    <td class="p-2 border-x border-slate-200">{{ number_format($row['fare'], 2) }}</td>
                                    <td @class(['p-2 border-x border-slate-200', 'bg-amber-50 font-bold text-amber-700' => $row['delay'] > 0])>
                                        {{ number_format($row['delay'], 2) }}
                                    </td>
                                    <td class="p-2 border-x border-slate-300 bg-slate-50/50 font-black">{{ number_format($row['total_amount'], 2) }}</td>

                                    <td class="p-2 border-x border-slate-200 text-[9px] text-blue-700">{{ $row['settlement_date'] ?: '-' }}</td>
                                    <td class="p-2 border-x border-slate-200 text-[9px] text-slate-500">{{ $row['settlement_desc'] ?: '-' }}</td>
                                    <td class="p-2 border-x border-slate-200 font-bold text-rose-600">
                                        {{ $row['settlement_amount'] > 0 ? number_format($row['settlement_amount'], 2) : '-' }}
                                    </td>
                                </tr>
                            @else
                                {{-- سطر الدفع المنفصل --}}
                                <tr class="bg-blue-50/30 font-bold border-y-2 border-blue-100">
                                    <td class="p-3 text-right text-blue-900 italic" colspan="10">
                                        <div class="flex items-center gap-2">
                                            <span class="w-2 h-2 bg-blue-600 rounded-full"></span>
                                            سند صرف نقدية / تحويل بنكي: {{ $row['description'] }}
                                        </div>
                                    </td>
                                    <td class="p-2 border-x border-blue-100 text-[9px] text-blue-800 uppercase">{{ $row['settlement_date'] }}</td>
                                    <td class="p-2 border-x border-blue-100 text-[9px] text-blue-800">{{ $row['settlement_desc'] }}</td>
                                    <td class="p-2 border-x border-blue-200 bg-blue-100 text-rose-800 text-base font-black">
                                        {{ number_format($row['settlement_amount'], 2) }}
                                    </td>
                                </tr>
                            @endif
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="bg-slate-900 text-white font-black border-t-2 border-slate-950">
                            <td colspan="7" class="p-3 text-left uppercase tracking-tighter">Grand Totals | الإجماليات النهائية</td>
                            <td colspan="2" class="p-3 border-l border-slate-700 bg-slate-800"></td>
                            <td class="p-3 border-l border-slate-700 bg-slate-800 text-lg tabular-nums">
                                {{ number_format($summary['total_claims'], 2) }}
                            </td>
                            <td colspan="2" class="p-3 border-l border-slate-700 bg-blue-950 text-left">إجمالي المسدد</td>
                            <td class="p-3 bg-rose-900 text-lg tabular-nums">{{ number_format($summary['total_paid'], 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            {{-- 3. التوقيعات --}}
            <div class="mt-12 hidden print:grid grid-cols-3 gap-12 text-center">
                <div class="border-t-2 border-slate-200 pt-2">
                    <span class="text-[10px] font-bold text-slate-400 uppercase">المحاسب المسؤول</span>
                </div>
                <div class="border-t-2 border-slate-200 pt-2">
                    <span class="text-[10px] font-bold text-slate-400 uppercase">مراجعة المقاول</span>
                </div>
                <div class="border-t-2 border-slate-900 pt-2 bg-slate-50">
                    <span class="text-[10px] font-black text-slate-900 uppercase">اعتماد الإدارة المالية</span>
                </div>
            </div>

            <div class="mt-6 flex justify-between items-center text-[8px] text-slate-400 hidden print:flex uppercase">
                <span>تاريخ استخراج التقرير: {{ now()->format('Y-m-d H:i') }}</span>
                <span>نظام إدارة أسطول النقل - الإصدار 4.0</span>
            </div>

            <x-print-button/>
        </div>
    @else
        <div class="flex flex-col items-center justify-center p-20 bg-slate-50 border-2 border-dashed border-slate-300 rounded-sm">
            <x-filament::icon icon="heroicon-o-user-group" class="w-16 h-16 text-slate-300 mb-4" />
            <h2 class="text-xl font-bold text-slate-400 italic">يرجى اختيار مقاول الشحن لاستعراض السجل المالي</h2>
        </div>
    @endif

    <style>
        #report-content { font-family: 'FlatJooza', sans-serif; }
        @media print {
            @page { size: A4 landscape; margin: 10mm; }
            .no-print { display: none !important; }
            body { background: white !important; }
            .fi-main-ctn { padding: 0 !important; margin: 0 !important; }
            table { border-collapse: collapse !important; width: 100% !important; }
            th, td { border: 1px solid #000 !important; }
            .bg-slate-900 { background-color: #0f172a !important; color: white !important; -webkit-print-color-adjust: exact; }
            .bg-blue-900 { background-color: #1e3a8a !important; color: white !important; -webkit-print-color-adjust: exact; }
            .bg-slate-800 { background-color: #1e293b !important; -webkit-print-color-adjust: exact; }
            .bg-rose-900 { background-color: #7f1d1d !important; color: white !important; -webkit-print-color-adjust: exact; }
            .bg-blue-100 { background-color: #dbeafe !important; -webkit-print-color-adjust: exact; }
        }
    </style>
</x-filament-panels::page>