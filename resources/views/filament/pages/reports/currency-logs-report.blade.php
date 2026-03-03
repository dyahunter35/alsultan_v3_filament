<x-filament-panels::page>
    {{-- منطقة الفلاتر والتحكم --}}
    <x-filament::section class="mb-6 no-print border-none shadow-sm bg-gray-50/50">
        <div class="flex flex-col gap-4 md:flex-row md:items-end justify-between" dir="rtl">
            <div class="flex-1 max-w-2xl">{{ $this->form }}</div>
            <x-filament::button wire:click="refreshBalance" 
                color="gray" 
                variant="outline"
                icon="heroicon-m-arrow-path" 
                class="shadow-sm">
                تحديث الحسابات
            </x-filament::button>
        </div>
    </x-filament::section>

    @if (!empty($reportData) && $reportData['customer'])
        @php 
            $customer = $reportData['customer'];
            $balances = $reportData['balances'];
            $purchases = $reportData['purchase_transactions'];
            $payments = $reportData['payment_transactions'];
        @endphp

        <div id="report-content" class="p-8 bg-white text-slate-900 mx-auto max-w-[210mm] shadow-lg print:shadow-none print:p-0" dir="rtl">
            
            {{-- ترويسة احترافية 
            <div class="flex justify-between items-start border-b-2 border-slate-800 pb-4 mb-6">
                <div>
                    <h1 class="text-2xl font-black text-slate-800 mb-1">كشف حساب سجلات العملات</h1>
                    <p class="text-sm text-slate-500 italic">تقرير مالي تفصيلي لحركة الحساب</p>
                </div>
                <div class="text-left text-sm">
                    <div class="font-bold text-lg text-primary-600">{{ $customer->name }}</div>
                    <div class="text-slate-400">تاريخ الاستخراج: {{ now()->format('Y/m/d H:i') }}</div>
                </div>
            </div>--}}
            <x-report-header title="كشف حساب سجلات العملات" :value="$customer->name . ' - ' . now()->format('Y/m/d H:i')" />

            {{-- ملخص الأرصدة (بطاقات بدلاً من جدول ضخم) --}}
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
                @foreach ($currencys as $c)
                <div class="border border-slate-200 p-3 rounded-lg bg-slate-50/50 text-center">
                    <span class="block text-[10px] text-slate-500 uppercase font-bold">{{ $c->name }}</span>
                    <span class="text-lg font-black tabular-nums">{{ number_format($balances[$c->code] ?? 0, 2) }}</span>
                </div>
                @endforeach
                <div class="border-2 border-slate-800 p-3 rounded-lg bg-slate-800 text-white text-center">
                    <span class="block text-[10px] opacity-80 font-bold">الرصيد الإجمالي (سوداني)</span>
                    <span class="text-lg font-black tabular-nums">{{ number_format($balances['sd'] ?? 0, 2) }}</span>
                </div>
            </div>

            {{-- جدول المشتريات --}}
            <div class="mb-8">
                <h3 class="flex items-center gap-2 font-bold mb-3 text-slate-700">
                    <span class="w-1 h-5 bg-emerald-600"></span>
                    سجل العملات المكتسبة (المشتريات)
                </h3>
                <table class="w-full text-[11px] border-collapse">
                    <thead>
                        <tr class="bg-slate-100 border-y border-slate-300">
                            <th class="p-2 text-right">#</th>
                            <th class="p-2 text-right">التاريخ</th>
                            <th class="p-2 text-center bg-slate-200/50 italic">الرصيد التراكمي (SDG)</th>
                            <th class="p-2 text-center font-bold">المبلغ (SDG)</th>
                            @foreach ($currencys as $c)
                                <th class="p-2 text-center border-r border-slate-200">{{ $c->code }}</th>
                                <th class="p-2 text-center text-[9px] text-slate-400">سعر الصرف</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 border-b border-slate-300">
                        @foreach ($purchases as $tr)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="p-2 text-slate-500">{{ $tr['index'] }}</td>
                            <td class="p-2 whitespace-nowrap">{{ $tr['date'] }}</td>
                            <td class="p-2 text-center font-medium bg-slate-50/50 tabular-nums">{{ number_format($tr['running_balance'], 2) }}</td>
                            <td class="p-2 text-center font-bold tabular-nums text-emerald-700">{{ number_format($tr['total'], 2) }}</td>
                            @foreach($currencys as $c)
                                <td class="p-2 text-center tabular-nums border-r border-slate-100">
                                    {{ $tr['currency_code'] == $c->code ? number_format($tr['amount'], 2) : '-' }}
                                </td>
                                <td class="p-2 text-center text-slate-400 tabular-nums">
                                    {{ $tr['currency_code'] == $c->code ? number_format($tr['rate'], 2) : '-' }}
                                </td>
                            @endforeach
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- جدول الصرف --}}
            <div class="mb-8">
                <h3 class="flex items-center gap-2 font-bold mb-3 text-slate-700">
                    <span class="w-1 h-5 bg-rose-600"></span>
                    سجل العملات المنفقة (المدفوعات)
                </h3>
                <table class="w-full text-[11px] border-collapse">
                    <thead class="bg-slate-800 text-white">
                        <tr>
                            <th class="p-2 text-right">#</th>
                            <th class="p-2 text-right">التاريخ</th>
                            <th class="p-2 text-right w-1/3">البيان / الملاحظات</th>
                            <th class="p-2 text-right">الجهة / الشركة</th>
                            @foreach($currencys as $c)
                                <th class="p-2 text-center">{{ $c->code }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 border-b border-slate-300">
                        @foreach ($payments as $tr)
                        <tr class="odd:bg-white even:bg-slate-50/50">
                            <td class="p-2 text-slate-500">{{ $tr['index'] }}</td>
                            <td class="p-2 tabular-nums">{{ $tr['date'] }}</td>
                            <td class="p-2 text-right leading-tight text-slate-600">{{ $tr['note'] }}</td>
                            <td class="p-2 font-bold">{{ $tr['company'] }}</td>
                            @foreach($currencys as $c)
                                <td class="p-2 text-center tabular-nums {{ $tr['currency_code'] == $c->code ? 'font-bold text-rose-700' : 'text-slate-300' }}">
                                    {{ $tr['currency_code'] == $c->code ? number_format($tr['amount'], 2) : '-' }}
                                </td>
                            @endforeach
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- ملخص مالي ختامي --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 items-start mt-10">
                <div>
                    <table class="w-full text-[11px] border border-slate-300">
                        <tr class="bg-slate-50 font-bold border-b border-slate-300 text-slate-700">
                            <td class="p-2">مقارنة العملات</td>
                            <td class="p-2 text-center">إجمالي الداخل</td>
                            <td class="p-2 text-center">إجمالي الخارج</td>
                            <td class="p-2 text-center">الصافي</td>
                        </tr>
                        @foreach ($currencys as $c)
                            @php
                                $totalPurchased = collect($purchases)->where('currency_code', $c->code)->sum('amount');
                                $totalSpent = collect($payments)->where('currency_code', $c->code)->sum('amount');
                                $net = $totalPurchased - $totalSpent;
                            @endphp
                            <tr class="border-b border-slate-200">
                                <td class="p-2 font-bold">{{ $c->name }}</td>
                                <td class="p-2 text-center tabular-nums">{{ number_format($totalPurchased, 2) }}</td>
                                <td class="p-2 text-center tabular-nums">{{ number_format($totalSpent, 2) }}</td>
                                <td class="p-2 text-center tabular-nums {{ $net < 0 ? 'text-rose-600' : 'text-emerald-600' }}">
                                    {{ number_format($net, 2) }}
                                </td>
                            </tr>
                        @endforeach
                    </table>
                </div>

                {{-- التوقيعات --}}
                <div class="grid grid-cols-3 gap-4 text-center text-[10px] font-bold mt-4">
                    <div class="space-y-8 italic">
                        <p>توقيع المحاسب</p>
                        <div class="border-b border-slate-400 w-24 mx-auto"></div>
                    </div>
                    <div class="space-y-8 italic">
                        <p>المدير المالي</p>
                        <div class="border-b border-slate-400 w-24 mx-auto"></div>
                    </div>
                    <div class="space-y-8">
                        <p>ختم المؤسسة</p>
                        <div class="w-16 h-16 border-2 border-dashed border-slate-300 rounded-full mx-auto flex items-center justify-center text-[8px] text-slate-300 uppercase">
                            Official Stamp
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="no-print mt-6 flex justify-center">
            <x-filament::button 
                onclick="window.print()" 
                icon="heroicon-m-printer" 
                size="lg"
                class="shadow-xl">
                طباعة التقرير الرسمي
            </x-filament::button>
        </div>
    @endif

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Amiri:wght@400;700&family=Inter:wght@400;700&display=swap');

        #report-content {
            font-family: 'Inter', 'Amiri', serif;
        }

        @media print {
            @page { 
                size: A4 landscape; 
            }
            body { background: white !important; }
            .no-print { display: none !important; }
            #report-content { 
                border: none !important; 
                box-shadow: none !important; 
                width: 100% !important;
                max-width: none !important;
            }
            .bg-slate-100 { background-color: #f1f5f9 !important; -webkit-print-color-adjust: exact; }
            .bg-slate-800 { background-color: #1e293b !important; color: white !important; -webkit-print-color-adjust: exact; }
            .text-emerald-700 { color: #047857 !important; }
            .text-rose-700 { color: #be123c !important; }
        }
    </style>
</x-filament-panels::page>