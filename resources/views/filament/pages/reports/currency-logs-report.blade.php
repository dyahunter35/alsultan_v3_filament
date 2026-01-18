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
        @endphp

        <div id="report-content" class="p-6 bg-white text-black border-[3px] border-black" dir="rtl">
            {{-- ترويسة احترافية --}}
            <div class="flex justify-between items-center mb-6 border-b-4 border-double border-black pb-4">
                <div class="text-right">
                    <h1 class="text-3xl font-black">السلطان للبلاستيك</h1>
                    <p class="text-sm">كشف حساب سجلات العملات</p>
                </div>
                <div class="text-center font-bold">
                    <p class="text-xl underline">{{ $customer->name }}</p>
                    <p class="text-xs">تاريخ التقرير: {{ now()->format('Y-m-d') }}</p>
                </div>
                <div class="text-left">
                    {{-- لوجو الشركة هنا --}}
                </div>
            </div>

            {{-- ملخص الأرصدة --}}
            <table class="w-full text-center border-collapse border-2 border-black mb-6">
                <tr class="bg-gray-200 font-black text-[11px]">
                    <td class="border border-black p-2">درهم (AED)</td>
                    <td class="border border-black p-2">ريال (SAR)</td>
                    <td class="border border-black p-2">دولار (USD)</td>
                    <td class="border border-black p-2 text-red-600">مصري (EGP)</td>
                    <td class="border border-black p-2 bg-gray-300">سوداني (SDG)</td>
                    <td class="border border-black p-2 bg-black text-white w-24">العملة</td>
                </tr>
                <tr class="font-bold text-lg">
                    <td class="border border-black p-2">{{ number_format($balances['AED'] ?? 0, 2) }}</td>
                    <td class="border border-black p-2">{{ number_format($balances['SAR'] ?? 0, 2) }}</td>
                    <td class="border border-black p-2">{{ number_format($balances['USD'] ?? 0, 2) }}</td>
                    <td class="border border-black p-2 text-red-600">{{ number_format($balances['EGP'] ?? 0, 2) }}</td>
                    <td class="border border-black p-2">{{ number_format($balances['sd'] ?? 0, 2) }}</td>
                    <td class="border border-black p-2 bg-gray-100">الرصيد</td>
                </tr>
            </table>

            {{-- جدول الشراء --}}
            <div class="text-center font-bold my-2 py-1 bg-gray-50 border-y-2 border-black text-sm italic">سجل مشتريات العملات</div>
            <table class="w-full text-[10px] text-center border-collapse border-2 border-black mb-8">
                <thead>
                    <tr class="bg-gray-100 font-bold">
                        <th class="border border-black" colspan="2">درهم اماراتي</th>
                        <th class="border border-black" colspan="2">ريال سعودي</th>
                        <th class="border border-black" colspan="2">دولار امريكي</th>
                        <th class="border border-black" colspan="2">جنيه مصري</th>
                        <th class="border border-black px-2 bg-yellow-50" rowspan="2">مبلغ الشراء</th>
                        <th class="border border-black px-2 bg-gray-200" rowspan="2">الرصيد التراكمي</th>
                        <th class="border border-black px-1 no-print" rowspan="2">سعر الصرف</th>
                        <th class="border border-black px-4" rowspan="2">التاريخ</th>
                        <th class="border border-black" rowspan="2">#</th>
                    </tr>
                    <tr class="bg-gray-100 text-[9px]">
                        @foreach(['AED', 'SAR', 'USD', 'EGP'] as $c)
                            <th class="border border-black">مبلغ</th><th class="border border-black">معادل</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach ($reportData['purchase_transactions'] as $tr)
                        <tr class="hover:bg-gray-50">
                            @foreach(['AED', 'SAR', 'USD', 'EGP'] as $code)
                                <td class="border border-black">{{ $tr['currency_code'] == $code ? number_format($tr['amount'], 2) : '-' }}</td>
                                <td class="border border-black text-gray-400">{{ $tr['currency_code'] == $code ? number_format($tr['total'], 2) : '-' }}</td>
                            @endforeach
                            
                            <td class="border border-black font-black bg-yellow-50">{{ number_format($tr['total'], 2) }}</td>
                            <td class="border border-black font-black text-blue-800 bg-gray-100">{{ number_format($tr['running_balance'], 2) }}</td>
                            <td class="border border-black p-0 no-print bg-blue-50">
                                {{ $tr['rate'] }}
                            </td>
                            <td class="border border-black whitespace-nowrap">{{ $tr['date'] }}</td>
                            <td class="border border-black">{{ $tr['index'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            {{-- جدول الصرف --}}
            <div class="text-center font-bold my-4 py-1 bg-gray-50 border-y-2 border-black text-sm italic">سجل صرف العملات (الشركات)</div>
            <table class="w-full text-[10px] text-center border-collapse border-2 border-black">
                <thead>
                    <tr class="bg-gray-100 font-bold">
                        <th class="border border-black p-1">درهم</th>
                        <th class="border border-black p-1">سعودي</th>
                        <th class="border border-black p-1">دولار</th>
                        <th class="border border-black p-1">مصري</th>
                        <th class="border border-black p-1">اسم الشركة</th>
                        <th class="border border-black p-1 w-1/3 text-right pr-4">البيان</th>
                        <th class="border border-black p-1">التاريخ</th>
                        <th class="border border-black p-1">#</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($reportData['payment_transactions'] as $tr)
                        <tr>
                            <td class="border border-black">{{ $tr['currency_code'] == 'AED' ? number_format($tr['amount'], 2) : '-' }}</td>
                            <td class="border border-black">{{ $tr['currency_code'] == 'SAR' ? number_format($tr['amount'], 2) : '-' }}</td>
                            <td class="border border-black">{{ $tr['currency_code'] == 'USD' ? number_format($tr['amount'], 2) : '-' }}</td>
                            <td class="border border-black font-bold text-red-600">{{ $tr['currency_code'] == 'EGP' ? number_format($tr['amount'], 2) : '-' }}</td>
                            <td class="border border-black font-bold">{{ $tr['company'] }}</td>
                            <td class="border border-black text-right px-2 leading-tight">{{ $tr['note'] }}</td>
                            <td class="border border-black">{{ $tr['date'] }}</td>
                            <td class="border border-black">{{ $tr['index'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        <div class="mt-6 flex justify-end gap-4 no-print">
            <x-filament::button onclick="window.print()" icon="heroicon-m-printer" size="lg">طباعة التقرير النهائي</x-filament::button>
        </div>
    @endif

    <style>
        @media print {
            @page { size: A4 landscape; margin: 8mm; }
            .no-print { display: none !important; }
            #report-content { border: 3px solid black !important; padding: 5mm !important; }
            table { border: 1.5px solid black !important; width: 100% !important; }
            th, td { border: 1px solid black !important; }
            .bg-gray-100, .bg-gray-200, .bg-gray-300 { background-color: #f3f4f6 !important; -webkit-print-color-adjust: exact; }
        }
    </style>
</x-filament-panels::page>