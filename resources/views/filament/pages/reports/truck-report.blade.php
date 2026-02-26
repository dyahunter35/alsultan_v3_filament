<div class="bg-slate-50 min-h-screen pb-10">
    {{-- قسم الفلترة - يختفي عند الطباعة --}}
    <div class="w-full mx-auto p-4 no-print">
        <x-filament::section class="shadow-sm border-slate-200">
            {{ $this->form }}
        </x-filament::section>
    </div>

    @if ($truckId)
        <div id="report-content" class="w-full mx-auto bg-white p-6 md:p-10 shadow-sm print:shadow-none print:p-0">
            
            {{-- الهيدر الاحترافي الموحد --}}
            <x-report-header label="بيان شحنة بضائع" :value="'No. ' . $truck->code" />

            {{-- 1. تفاصيل الشاحنة (نمط الصناديق الرسمية) --}}
            <div class="grid grid-cols-3 gap-0 border-2 border-slate-950 mb-6 overflow-hidden rounded-sm">
                <div class="border-l-2 border-slate-950 p-3 bg-white text-center">
                    <span class="block text-[10px] font-bold text-slate-500 uppercase mb-1">رقم اللوحة / Plate No.</span>
                    <span class="text-xl font-black text-slate-900">{{ $truck->car_number }}</span>
                </div>
                <div class="border-l-2 border-slate-950 p-3 bg-white text-center">
                    <span class="block text-[10px] font-bold text-slate-500 uppercase mb-1">الموديل / Truck Model</span>
                    <span class="text-xl font-black text-slate-900">{{ $truck->truck_model }}</span>
                </div>
                <div class="p-3 bg-slate-50 text-center">
                    <span class="block text-[10px] font-bold text-slate-500 uppercase mb-1">الشركة الناقلة / Carrier</span>
                    <span class="text-lg font-bold text-slate-800">{{ $truck->companyId?->name ?? '-' }}</span>
                </div>
            </div>

            {{-- 2. جدول تفاصيل البضائع (نمط الجداول الحسابية) --}}
            <div class="mb-6">
                <h3 class="text-xs font-black text-slate-900 mb-2 flex items-center gap-2 uppercase tracking-widest">
                    <span class="w-3 h-3 bg-slate-900"></span>
                    تفاصيل الحمولة | Cargo Manifest
                </h3>
                <div class="overflow-x-auto border-t-2 border-slate-900">
                    <table class="w-full text-center border-collapse text-[13px] print:text-[10px]">
                        <thead>
                            <tr class="bg-slate-900 text-white">
                                <th class="p-2 border border-slate-700 w-8">#</th>
                                <th class="p-2 text-right border border-slate-700">الصنف / Product</th>
                                <th class="p-2 border border-slate-700">المقاس</th>
                                <th class="p-2 border border-slate-700">الوزن (جم)</th>
                                <th class="p-2 border border-slate-700 bg-slate-800">الوزن (طن)</th>
                                <th class="p-2 border border-slate-700">الكمية (طرد)</th>
                                <th class="p-2 border border-slate-700">الكمية (عدد)</th>
                                <th class="p-2 border border-slate-700">الفعلي</th>
                                <th class="p-2 border border-slate-700">الفرق</th>
                                <th class="p-2 border border-slate-700">ملاحظات</th>
                            </tr>
                        </thead>
                        <tbody class="tabular-nums text-slate-900 font-medium">
                            @foreach($rows as $i => $row)
                                <tr class="border-b border-slate-300 hover:bg-slate-50 transition-colors">
                                    <td class="p-2 border-x border-slate-200 bg-slate-50/50 text-slate-500">{{ $i + 1 }}</td>
                                    <td class="p-2 font-bold text-right border-x border-slate-200">{{ $row['product_name'] }}</td>
                                    <td class="p-2 border-x border-slate-200">{{ $row['size'] }}</td>
                                    <td class="p-2 border-x border-slate-200">{{ number_format($row['weight_grams'], 2) }}</td>
                                    <td class="p-2 font-black border-x border-slate-300 bg-slate-50/50">{{ number_format($row['weight_ton'], 3) }}</td>
                                    <td class="p-2 border-x border-slate-200">{{ number_format($row['quantity'], 2) }}</td>
                                    <td class="p-2 border-x border-slate-200 text-slate-600">{{ number_format($row['unit_quantity'], 2) }}</td>
                                    <td class="p-2 border-x border-slate-200">{{ number_format($row['real_quantity'], 2) }}</td>
                                    <td @class([
                                        'p-2 font-black border-x border-slate-200',
                                        'text-emerald-700' => $row['dif'] >= 0,
                                        'text-rose-700' => $row['dif'] < 0,
                                    ])>
                                        {{ $truck->is_converted ? number_format($row['dif'], 2) : '-' }}
                                    </td>
                                    <td class="p-2 border-x border-slate-200 text-[9px] text-slate-500 italic leading-tight">{{ $row['note'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="font-black bg-slate-900 text-white border-2 border-slate-900">
                                <td colspan="4" class="p-3 text-left uppercase tracking-tighter">TOTALS | الإجماليات</td>
                                <td class="p-3 border-l border-slate-700 bg-slate-800 text-base">
                                    {{ number_format(array_sum(array_column($rows, 'weight_ton')), 3) }}
                                </td>
                                <td class="p-3 border-l border-slate-700">{{ number_format(array_sum(array_column($rows, 'quantity')), 2) }}</td>
                                <td class="p-3 border-l border-slate-700"></td>
                                <td class="p-3 border-l border-slate-700">{{ number_format(array_sum(array_column($rows, 'real_quantity')), 2) }}</td>
                                <td class="p-3 border-l border-slate-700 text-yellow-400">
                                    {{ $truck->is_converted ? number_format(array_sum(array_column($rows, 'dif')), 2) : '-' }}
                                </td>
                                <td class="p-3 bg-slate-800"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            {{-- 3. تذييل التوقيعات (بشكل رسمي) --}}
            <div class="mt-16 hidden print:block">
                <div class="grid grid-cols-3 gap-12">
                    <div class="text-center border-t-2 border-slate-200 pt-3">
                        <span class="text-[10px] font-bold text-slate-400 uppercase block mb-8">إعداد / Prepared By</span>
                        <div class="border-b border-dotted border-slate-400 w-3/4 mx-auto"></div>
                    </div>
                    <div class="text-center border-t-2 border-slate-200 pt-3">
                        <span class="text-[10px] font-bold text-slate-400 uppercase block mb-8">المراجعة / Verified By</span>
                        <div class="border-b border-dotted border-slate-400 w-3/4 mx-auto"></div>
                    </div>
                    <div class="text-center border-t-2 border-slate-900 pt-3 bg-slate-50">
                        <span class="text-[10px] font-bold text-slate-900 uppercase block mb-8">اعتماد الإدارة / Approval</span>
                        <span class="text-[9px] text-slate-400 italic">ختم الشركة الرسمي</span>
                    </div>
                </div>
                
                {{-- 
                <div class="mt-10 pt-4 border-t border-slate-100 flex justify-between items-center text-[9px] text-slate-400">
                    <span>تاريخ الطباعة: {{ now()->format('Y-m-d H:i') }}</span>
                    <span>نظام الإدارة الإلكتروني - شركة {{ $truck->companyId?->name }}</span>
                    <span>صفحة 1 من 1</span>
                </div>
                 --}}
            </div>
        </div>

        <div class="fixed bottom-6 left-6 no-print">
            <x-print-button />
        </div>
    @endif

    <style>
        #report-content { font-family: 'FlatJooza', sans-serif; }
        @media print {
            @page { size: A4 portrait; margin: 12mm; }
            body { background: white !important; }
            .no-print { display: none !important; }
            table { border-collapse: collapse !important; width: 100% !important; }
            th, td { border: 1px solid #000 !important; }
            .bg-slate-900 { background-color: #0f172a !important; color: white !important; -webkit-print-color-adjust: exact; }
            .bg-slate-50 { background-color: #f8fafc !important; -webkit-print-color-adjust: exact; }
            .bg-slate-800 { background-color: #1e293b !important; -webkit-print-color-adjust: exact; }
        }
    </style>
</div>