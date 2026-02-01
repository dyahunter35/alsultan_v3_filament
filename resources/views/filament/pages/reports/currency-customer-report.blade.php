<x-filament-panels::page>
    {{-- 1. قسم الفلترة --}}
    <x-filament::section class="mb-4 shadow-sm no-print border-slate-200">
        <div class="flex flex-col gap-4 md:flex-row md:items-end">
            <div class="flex-1">{{ $this->form }}</div>
            <div class="flex items-center gap-2">
                <x-filament::button wire:click="updateBalances" color="gray" icon="heroicon-m-arrow-path">
                    تحديث حسابات العملاء
                </x-filament::button>
            </div>
        </div>
    </x-filament::section>

    @if ($ledger && count($ledger))
        <div id="report-content" class="space-y-6 print:m-0">
            <x-report-header :label="$this->getTitle()"/>

            {{-- 2. بيانات العميل --}}
            <div class="grid grid-cols-1 overflow-hidden bg-white border shadow-sm md:grid-cols-3 print:grid-cols-3 rounded-xl print:border-slate-300">
                <div class="flex items-center justify-center gap-2 px-4 py-3 border-b md:border-b-0 md:border-l border-slate-100">
                    <x-filament::icon icon="heroicon-m-map-pin" class="w-4 h-4 text-slate-400" />
                    <span class="text-sm font-bold text-slate-400">العنوان:</span>
                    <span class="text-sm font-medium text-slate-700">{{ $customer?->address ?? '-' }}</span>
                </div>
                <div class="flex items-center justify-center gap-2 px-4 py-3 border-b md:border-b-0 md:border-l border-slate-100">
                    <x-filament::icon icon="heroicon-m-phone" class="w-4 h-4 text-slate-400" />
                    <span class="text-sm font-bold text-slate-400">الهاتف:</span>
                    <span class="text-sm font-bold tabular-nums text-slate-700">{{ $customer?->phone ?? '-' }}</span>
                </div>
                <div class="flex items-center justify-center gap-3 px-4 py-3 bg-slate-50/50">
                    <x-filament::icon icon="heroicon-m-credit-card" class="w-4 h-4 text-slate-400" />
                    <span class="text-sm font-bold text-slate-400">إجمالي الرصيد (SDG):</span>
                    <span class="text-lg font-black tabular-nums {{ ($customer?->balance ?? 0) < 0 ? 'text-red-600' : 'text-green-700' }}">
                        {{ number_format($customer?->balance ?? 0, 2) }}
                    </span>
                </div>
            </div>

            {{-- 3. جديد: جدول مقارنة العملات (المنفقة والمكتسبة) --}}
            <div class="overflow-hidden bg-white border shadow-sm border-slate-200 rounded-xl">
                <div class="px-4 py-2 border-b bg-slate-50 border-slate-200">
                    <h3 class="text-sm font-bold text-slate-600">| ملخص حركة العملات (المكتسب مقابل المنفق)</h3>
                </div>
                <table class="w-full text-center border-collapse text-xs tabular-nums">
                    <thead>
                        <tr class="font-bold text-slate-700 bg-slate-100/50">
                            <th class="p-2 border-l border-slate-200">العملة</th>
                            <th class="p-2 border-l border-slate-200 text-green-700">المكتسب (شراء)</th>
                            <th class="p-2 border-l border-slate-200 text-red-700">المنفق (صرف)</th>
                            <th class="p-2 border-l border-slate-200">الصافي (خلال الفترة)</th>
                            <th class="p-2">المعادل الكلي (سوداني)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            // استخراج العملات الفريدة من الليدجر (باستثناء الرصيد الافتتاحي)
                            $currencySummary = collect($ledger)
                                ->whereNotIn('type', ['opening_balance'])
                                ->whereNotNull('currency')
                                ->groupBy('currency');
                        @endphp
                        @foreach ($currencySummary as $currencyCode => $moves)
                            @php
                                $gained = $moves->where('type', 'purchase')->sum('amount');
                                $spent = $moves->where('type', 'payment')->sum('amount');
                                $net = $gained - $spent;
                                $totalSdg = $moves->sum('amount_in') - $moves->sum('amount_out');
                            @endphp
                            <tr class="border-t border-slate-100 hover:bg-slate-50">
                                <td class="p-2 font-black border-l border-slate-100 bg-slate-50/30">{{ $currencyCode }}</td>
                                <td class="p-2 text-green-600 border-l border-slate-100 font-bold">{{ number_format($gained, 2) }}</td>
                                <td class="p-2 text-red-600 border-l border-slate-100 font-bold">{{ number_format($spent, 2) }}</td>
                                <td class="p-2 border-l border-slate-100 font-black {{ $net < 0 ? 'text-red-700' : 'text-blue-700' }}">
                                    {{ number_format($net, 2) }}
                                </td>
                                <td class="p-2 font-bold bg-slate-50/50">{{ number_format($totalSdg, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- 4. جدول الأستاذ (Ledger) --}}
            <div class="overflow-x-auto bg-white border shadow-sm border-slate-200 rounded-xl print:border-slate-800 print:rounded-none">
                <table class="w-full text-center border-collapse text-sm print:text-[11px]">
                    <thead>
                        <tr class="font-bold text-white bg-slate-800">
                            <th class="p-3 border border-slate-700">التاريخ</th>
                            <th class="p-3 text-right border border-slate-700">المعاملة / البيان</th>
                            <th class="p-3 border border-slate-700 bg-green-900/50">وارد (+)</th>
                            <th class="p-3 border border-slate-700 bg-red-900/50">منصرف (-)</th>
                            <th class="p-3 italic border border-slate-700 bg-slate-700">الرصيد التراكمي</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 tabular-nums">
                        @foreach ($ledger as $row)
                            <tr class="transition-colors hover:bg-slate-50">
                                <td class="p-2 border border-slate-200 text-slate-500">{{ $row['date'] }}</td>
                                <td class="p-2 px-4 font-medium text-right border border-slate-200">
                                    <div class="font-bold">{{ $row['description'] }}</div>
                                    @if(isset($row['currency']) && $row['currency'] != '-')
                                        <div class="text-[10px] text-slate-400">
                                            الكمية: {{ number_format($row['amount'], 2) }} {{ $row['currency'] }} | السعر: {{ number_format($row['rate'], 2) }}
                                        </div>
                                    @endif
                                </td>
                                <td class="p-2 font-bold text-green-600 border border-slate-200 bg-green-50/30">
                                    {{ $row['amount_in'] > 0 ? number_format($row['amount_in'], 2) : '-' }}
                                </td>
                                <td class="p-2 font-bold text-red-600 border border-slate-200 bg-red-50/30">
                                    {{ $row['amount_out'] > 0 ? number_format($row['amount_out'], 2) : '-' }}
                                </td>
                                <td class="p-2 font-black border border-slate-200 text-slate-800 bg-slate-50">
                                    {{ number_format($row['balance'], 2) }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="font-black border-t-2 bg-slate-100 border-slate-800">
                        <tr>
                            <td colspan="2" class="p-3 text-center">إجمالي الحركة خلال الفترة</td>
                            <td class="p-3 text-green-700 border border-slate-300">{{ number_format($ledger->sum('amount_in'), 2) }}</td>
                            <td class="p-3 text-red-700 border border-slate-300">{{ number_format($ledger->sum('amount_out'), 2) }}</td>
                            <td class="p-3 bg-slate-200">{{ number_format($ledger->last()['balance'] ?? 0, 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div class="justify-between hidden px-8 mt-12 text-xs italic print:flex text-slate-400">
                <p>طُبع في: {{ now()->format('Y-m-d H:i') }}</p>
                <p>توقيع المحاسب المسئول: ............................</p>
            </div>
        </div>

        <x-print-button />
    @else
        <div class="p-20 text-center bg-white border-2 border-gray-300 border-dashed shadow-sm rounded-xl">
            <x-filament::icon icon="heroicon-o-document-magnifying-glass" class="w-12 h-12 mx-auto mb-4 text-gray-300" />
            <h3 class="text-xl font-bold tracking-tight text-gray-400">لا توجد حركات مسجلة</h3>
        </div>
    @endif

    {{-- CSS Styles --}}
    <style>
        @font-face { font-family: 'FlatJooza'; src: url('{{ asset('fonts/flat-jooza-regular.woff2') }}') format('woff2'); }
        #report-content { font-family: 'FlatJooza', sans-serif; }
        @media print {
            @page { size: A4 portrait; margin: 10mm; }
            .no-print { display: none !important; }
            body { -webkit-print-color-adjust: exact !important; }
        }
    </style>
</x-filament-panels::page>