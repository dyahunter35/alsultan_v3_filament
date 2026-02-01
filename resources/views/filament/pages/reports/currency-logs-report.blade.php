<x-filament-panels::page>
    <x-filament::section class="mb-4 no-print">
        <div class="flex flex-col gap-4 md:flex-row md:items-end" dir="rtl">
            <div class="flex-1">{{ $this->form }}</div>
            <x-filament::button wire:click="refreshBalance" color="primary" icon="heroicon-m-arrow-path">
                تحديث حسابات الأرصدة
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

        <div id="report-content" class="p-6 bg-white text-black" dir="rtl">
            {{-- ترويسة احترافية --}}
            <x-report-header label="كشف حساب سجلات العملات" :value='$customer->name'/>

            {{-- ملخص الأرصدة العلوية --}}
            <table class="w-full text-center border-collapse border-2 border-black mb-6">
                <tr class="bg-black text-white font-black text-[11px]">
                    <td class="border border-black p-2 w-24">البيان</td>
                    @foreach ($currencys as $c)
                        <td class="border border-black p-2">{{ $c->name }} ({{ $c->code }})</td>
                    @endforeach
                    <td class="border border-black p-2 bg-gray-600">سوداني (SDG)</td>
                </tr>
                <tr class="font-bold text-lg bg-gray-50">
                    <td class="border border-black p-2 bg-gray-200 text-sm">الرصيد الحالي</td>
                    @foreach ($currencys as $c)
                        <td class="border border-black p-2 tabular-nums">{{ number_format($balances[$c->code] ?? 0, 2) }}</td>
                    @endforeach
                    <td class="border border-black p-2 tabular-nums">{{ number_format($balances['sd'] ?? 0, 2) }}</td>
                </tr>
            </table>

            {{-- جدول الشراء --}}
            <div class="text-right font-bold my-2 px-2 text-sm border-r-4 border-yellow-500 mr-1">| سجل مشتريات العملات (المكتسبة)</div>
            <table class="w-full text-[10px] text-center border-collapse border-2 border-black mb-8">
                <thead>
                    <tr class="bg-gray-200 font-bold border-b-2 border-black">
                        <th class="border border-black" rowspan="2">#</th>
                        <th class="border border-black px-4" rowspan="2">التاريخ</th>
                        <!-- <th class="border border-black px-2 bg-gray-300" rowspan="2">الرصيد التراكمي</th> -->
                        <!-- <th class="border border-black px-2 bg-yellow-100" rowspan="2">المعادل (سوداني)</th> -->
                            <th class="border border-black" colspan="2">سوداني</th>

                        @foreach ($currencys as $c)
                            <th class="border border-black" colspan="2">{{ $c->name }}</th>
                        @endforeach
                    </tr>
                    <tr class="bg-gray-100 text-[9px]">
                            <th class="border border-black">الرصيد</th><th class="border border-black">مبلغ. الشراء</th>

                        @foreach($currencys as $c)
                            <th class="border border-black">مبلغ</th><th class="border border-black">سعر الصرف</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach ($purchases as $tr)
                        <tr class="hover:bg-gray-50">
                            <td class="border border-black">{{ $tr['index'] }}</td>
                            <td class="border border-black whitespace-nowrap">{{ $tr['date'] }}</td>
                            <td class="border border-black font-black text-blue-800 bg-gray-100 tabular-nums">{{ number_format($tr['running_balance'], 2) }}</td>
                            <td class="border border-black font-black bg-yellow-50 tabular-nums">{{ number_format($tr['total'], 2) }}</td>
                            @foreach($currencys as $c)
                                <td class="border border-black tabular-nums">{{ $tr['currency_code'] == $c->code ? number_format($tr['amount'], 2) : '-' }}</td>
                                <td class="border border-black text-gray-400 tabular-nums">{{ $tr['currency_code'] == $c->code ? number_format($tr['rate'], 2) : '-' }}</td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>

            {{-- جدول الصرف --}}
            <div class="text-right font-bold my-4 px-2 text-sm border-r-4 border-red-600 mr-1">| سجل صرف العملات (المنفقة)</div>
            <table class="w-full text-[10px] text-center border-collapse border-2 border-black mb-8">
                <thead>
                    <tr class="bg-gray-200 font-bold border-b-2 border-black">
                        <th class="border border-black p-1">#</th>
                        <th class="border border-black p-1">التاريخ</th>
                        <th class="border border-black p-1 w-1/3 text-right pr-4">البيان</th>
                        <th class="border border-black p-1">اسم الشركة</th>
                        @foreach($currencys as $c)
                            <th class="border border-black p-1">{{ $c->name }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach ($payments as $tr)
                        <tr class="odd:bg-white even:bg-gray-50">
                            <td class="border border-black">{{ $tr['index'] }}</td>
                            <td class="border border-black tabular-nums">{{ $tr['date'] }}</td>
                            <td class="border border-black text-right px-2 leading-tight">{{ $tr['note'] }}</td>
                            <td class="border border-black font-bold">{{ $tr['company'] }}</td>
                            @foreach($currencys as $c)
                                <td class="border border-black tabular-nums">{{ $tr['currency_code'] == $c->code ? number_format($tr['amount'], 2) : '-' }}</td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>

            {{-- جديد: جدول مقارنة العملات (الملخص المالي) --}}
            <div class="text-right font-bold my-4 px-2 text-sm border-r-4 border-blue-900 mr-1">| مقارنة العملات المكتسبة والمنفقة (صافي الحركة)</div>
            <table class="w-full text-center border-collapse border-2 border-black mb-8">
                <thead>
                    <tr class="bg-blue-900 text-white font-bold text-xs">
                        <th class="border border-white p-2">العملة</th>
                        <th class="border border-white p-2">إجمالي المكتسب (شراء)</th>
                        <th class="border border-white p-2">إجمالي المنفق (صرف)</th>
                        <th class="border border-white p-2">الصافي (خلال الفترة)</th>
                        <th class="border border-white p-2 bg-blue-800">حالة الرصيد</th>
                    </tr>
                </thead>
                <tbody class="text-sm font-bold">
                    @foreach ($currencys as $c)
                        @php
                            $totalPurchased = collect($purchases)->where('currency_code', $c->code)->sum('amount');
                            $totalSpent = collect($payments)->where('currency_code', $c->code)->sum('amount');
                            $net = $totalPurchased - $totalSpent;
                        @endphp
                        <tr>
                            <td class="border border-black bg-gray-100 p-2">{{ $c->name }}</td>
                            <td class="border border-black p-2 tabular-nums text-green-700">{{ number_format($totalPurchased, 2) }}</td>
                            <td class="border border-black p-2 tabular-nums text-red-700">{{ number_format($totalSpent, 2) }}</td>
                            <td class="border border-black p-2 tabular-nums {{ $net < 0 ? 'text-red-600' : 'text-blue-700' }}">
                                {{ number_format($net, 2) }}
                            </td>
                            <td class="border border-black p-2 text-[10px]">
                                @if($net > 0)
                                    <span class="text-green-600">زيادة في المخزون (+)</span>
                                @elseif($net < 0)
                                    <span class="text-red-600">عجز/سحب من الرصيد (-)</span>
                                @else
                                    <span class="text-gray-500">متعادل</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="bg-gray-200 font-black">
                        <td class="border border-black p-2" colspan="1">المعادل الكلي (سوداني)</td>
                        <td class="border border-black p-2 tabular-nums" colspan="1">
                            {{ number_format(collect($purchases)->sum('total'), 2) }}
                        </td>
                        <td class="border border-black p-2 text-gray-400 italic text-[10px]" colspan="3">
                            * الإجمالي المكتسب محسوب بالمعادل السوداني لحظة العملية
                        </td>
                    </tr>
                </tfoot>
            </table>

            {{-- التوقيعات --}}
            <div class="mt-12 hidden print:flex justify-between px-10 font-bold text-xs">
                <div class="text-center border-t border-black pt-2 w-32">توقيع المحاسب</div>
                <div class="text-center border-t border-black pt-2 w-32">المدير المالي</div>
                <div class="text-center border-t border-black pt-2 w-32">ختم الشركة</div>
            </div>
        </div>
        
        <div class="no-print mt-4">
            <x-print-button/>
        </div>
    @endif

    <style>
        #report-content table td { tabular-nums: true; }
        @media print {
            @page { size: A4 landscape; margin: 8mm; }
            .no-print { display: none !important; }
            #report-content { border: 2px solid black !important; padding: 5mm !important; }
            table { border: 1.5px solid black !important; width: 100% !important; }
            th, td { border: 1px solid black !important; }
            .bg-gray-100, .bg-gray-200, .bg-gray-300 { background-color: #f3f4f6 !important; -webkit-print-color-adjust: exact; }
            .bg-yellow-100 { background-color: #fef9c3 !important; -webkit-print-color-adjust: exact; }
            .bg-black { background-color: #000 !important; color: #fff !important; -webkit-print-color-adjust: exact; }
            .bg-blue-900 { background-color: #1e3a8a !important; color: #fff !important; -webkit-print-color-adjust: exact; }
        }
    </style>
</x-filament-panels::page>