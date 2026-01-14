<x-filament-panels::page>
    {{-- 1. قسم الفلترة --}}
    <x-filament::section class="mb-4 shadow-sm no-print border-slate-200">
        <div class="flex flex-col gap-4 md:flex-row md:items-end">
            <div class="flex-1">
                {{ $this->form }}
            </div>
            <div class="flex items-center gap-2">
                <x-filament::button wire:click="loadData" color="gray" icon="heroicon-m-arrow-path">
                    تحديث البيانات
                </x-filament::button>
            </div>
        </div>
    </x-filament::section>

    @if ($ledger && count($ledger))
        <div id="report-content" class="space-y-6 print:m-0">
            {{-- ترويسة التقرير الرسمية --}}
            <x-report-header :label="$this->getTitle()" :value="$delegate?->name ?? '—'" />

            {{-- 2. صناديق الملخص العلوي --}}
            <div class="grid grid-cols-1 overflow-hidden bg-white border shadow-sm md:grid-cols-3 print:grid-cols-3 rounded-xl print:shadow-none print:border-slate-300">
                <div class="flex items-center justify-center gap-2 px-4 py-3 border-b md:border-b-0 md:border-l border-slate-100 print:border-l">
                    <x-filament::icon icon="heroicon-m-map-pin" class="w-4 h-4 text-slate-400" />
                    <span class="text-sm font-bold text-slate-400">العنوان:</span>
                    <span class="text-sm font-bold tabular-nums text-slate-700">{{ $delegate?->address ?? '-' }}</span>
                </div>
                <div class="flex items-center justify-center gap-2 px-4 py-3 border-b md:border-b-0 md:border-l border-slate-100 print:border-l">
                    <x-filament::icon icon="heroicon-m-user" class="w-4 h-4 text-slate-400" />
                    <span class="text-sm font-bold text-slate-400">المسمى:</span>
                    <span class="text-sm font-medium text-slate-700">مندوب مبيعات</span>
                </div>
                <div class="flex items-center justify-center gap-3 px-4 py-3 bg-slate-50/50 print:bg-slate-50">
                    <x-filament::icon icon="heroicon-m-credit-card" class="w-4 h-4 text-slate-400" />
                    <span class="text-sm font-bold text-slate-400">الرصيد الختامي:</span>
                    
                     <button wire:click="updatePalance">
                         <span class="text-lg font-black tabular-nums {{ ($delegate?->balance ?? 0) < 0 ? 'text-red-600' : 'text-green-700' }}">
                            {{ number_format($delegate?->balance ?? 0, 2) }}
                        </span>
                     </button> 
                </div>
            </div>

            {{-- 3. الجدول التفصيلي --}}
            <div class="overflow-x-auto bg-white border shadow-sm border-slate-200 rounded-xl print:border-slate-800 print:rounded-none">
                <table class="w-full text-center border-collapse text-xs print:text-[10px]">
                    <thead>
                        <tr class="text-white bg-slate-800">
                            <th colspan="4" class="p-2 border border-slate-600 bg-slate-900/50 uppercase tracking-wider">بيانات الحركة</th>
                            <th colspan="3" class="p-2 border border-slate-600 bg-blue-900/50 uppercase tracking-wider">حساب العملاء</th>
                            <th colspan="3" class="p-2 border border-slate-600 bg-green-900/50 uppercase tracking-wider">حساب الخزينة</th>
                        </tr>
                        <tr class="font-bold text-slate-700 bg-slate-100 italic">
                            <th class="p-2 border border-slate-300">التاريخ</th>
                            <th class="p-2 border border-slate-300">المعاملة</th>
                            <th class="p-2 border border-slate-300">العميل</th>
                            <th class="p-2 border border-slate-300">البيان</th> 
                            
                            <th class="p-2 border border-slate-300 bg-blue-50/50">مدين (عليه)</th>
                            <th class="p-2 border border-slate-300 bg-blue-50/50">دائن (له)</th>
                            <th class="p-2 border border-slate-300 bg-blue-100/50">الرصيد</th>

                            <th class="p-2 border border-slate-300 bg-green-50/50">دخل (قبض)</th>
                            <th class="p-2 border border-slate-300 bg-green-50/50">خرج (صرف)</th> 
                            <th class="p-2 border border-slate-300 bg-green-100/50">الرصيد</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 tabular-nums font-medium">
                        @foreach ($ledger as $row)
                            <tr class="transition-colors hover:bg-slate-50/80">
                                <td class="p-2 border border-slate-200 text-slate-500 whitespace-nowrap">{{ \Carbon\Carbon::parse($row['date'])->format('Y-m-d') }}</td>
                                <td class="p-2 font-bold border border-slate-200 text-slate-700">{{ $row['transaction_name'] }}</td>
                                <td class="p-2 border border-slate-200 font-semibold">{{ $row['customer_name'] ?? '—' }}</td>
                                <td class="p-2 text-right border border-slate-200 min-w-[150px] leading-snug">
                                    @if ($row['details'] instanceof \Illuminate\Support\Collection )
                        
                                <table class="w-full border-collapse text-[12px] divide-y divide-slate-200">
                                        <tbody>
                                            @foreach ($row['details'] as $item)
                                                <tr class="border-b border-slate-100 last:border-0 hover:bg-white/50">
                                                    {{-- اسم الصنف - مساحة مرنة --}}
                                                    <td class="text-right py-1.5 pr-1 font-bold text-slate-700  w-24">
                                                        {{ $item->product?->name }}
                                                    </td>

                                                    {{-- الكمية - عرض ثابت --}}
                                                    <td
                                                        class="text-center py-1.5 px-2 tabular-nums w-12 border-r border-slate-50">
                                                        <span
                                                            class="font-black text-slate-900">{{ number_format($item->qty) }}</span>
                                                    </td>

                                                    {{-- السعر - عرض ثابت --}}
                                                    <td
                                                        class="text-center py-1.5 px-2 tabular-nums w-20 border-r border-slate-50">
                                                        <span
                                                            class="font-medium text-slate-600">{{ number_format($item->price, 1) }}</span>
                                                    </td>

                                                    {{-- الإجمالي - عرض ثابت --}}
                                                    <td
                                                        class="text-center py-1.5 pl-1 tabular-nums w-24 border-r border-slate-50">

                                                        <span
                                                            class="font-black text-green-700">{{ number_format($item->qty * $item->price, 1) }}</span>
                                                    </td>
                                                </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                                @else
                                    {{  $row['details']}}
                                    @endif
                            </td>

                                {{-- عملاء --}}
                                <td class="p-2 text-blue-700 border border-slate-200 bg-blue-50/10">
                                    {{ $row['customer_sales'] > 0 ? number_format($row['customer_sales'], 2) : '-' }}
                                </td>
                                <td class="p-2 text-blue-700 border border-slate-200 bg-blue-50/10">
                                    {{ $row['customer_payment'] > 0 ? number_format($row['customer_payment'], 2) : '-' }}
                                </td>
                                <td class="p-2 font-bold border border-slate-200 bg-blue-50/30 text-slate-600">
                                    {{ (is_numeric($row['customer_balance'])) ? number_format($row['customer_balance'], 2) : '-' }}
                                </td>

                                {{-- خزينة --}}
                                <td class="p-2 font-bold text-green-700 border border-slate-200 bg-green-50/10">
                                    {{ $row['treasury_debit'] > 0 ? number_format($row['treasury_debit'], 2) : '-' }}
                                </td>
                                <td class="p-2 font-bold text-red-700 border border-slate-200 bg-red-50/10">
                                    {{ $row['treasury_credit'] > 0 ? number_format($row['treasury_credit'], 2) : '-' }}
                                </td>
                                <td class="p-2 font-black border border-slate-200 bg-green-100/50 text-slate-800">
                                    {{ number_format($row['treasury_balance'], 2) }}
                                </td>
                            </tr>
                        @endforeach
                        <tr class="font-black border-t-2 bg-slate-800 text-white border-slate-900">
                            <td colspan="4" class="p-3 text-left uppercase">إجمالي الحركة الحالية</td>
                            <td class="p-2 border bg-blue-700/50">{{ number_format($ledger->sum('customer_sales'), 2) }}</td>
                            <td class="p-2 border bg-blue-700/50">{{ number_format($ledger->sum('customer_payment'), 2) }}</td>
                            <td class="p-2 text-lg border bg-blue-900">{{ number_format($ledger->last()['customer_balance'] ?? 0, 2) }}</td>

                            <td class="p-2 border bg-green-700/50">{{ number_format($ledger->sum('treasury_debit'), 2) }}</td>
                            <td class="p-2 border bg-red-700/50">{{ number_format($ledger->sum('treasury_credit'), 2) }}</td>
                            <td class="p-2 text-lg border bg-green-900">{{ number_format($ledger->last()['treasury_balance'] ?? 0, 2) }}</td>
                        </tr>
                    </tbody>
                    
                </table>
            </div>
            
            {{-- 4. التوقيعات --}}
            <div class="justify-between hidden px-8 mt-12 text-xs italic print:flex text-slate-400">
                <p>توقيع المندوب: ............................</p>
                <p>توقيع المراجع المالي: ............................</p>
            </div>
        </div>
        <x-print-button />
    @else
        <div class="p-20 text-center bg-white border-2 border-gray-300 border-dashed shadow-sm rounded-xl">
            <x-filament::icon icon="heroicon-o-document-magnifying-glass" class="w-12 h-12 mx-auto mb-4 text-gray-300" />
            <h3 class="text-xl font-bold tracking-tight text-gray-400">لا توجد حركات مسجلة لهذا المندوب</h3>
        </div>
    @endif

    <style>
        @font-face { font-family: 'FlatJooza'; src: url('{{ asset('fonts/flat-jooza-regular.woff2') }}') format('woff2'); }
        #report-content { font-family: 'FlatJooza', sans-serif; }
        
        @media print {
            @page { size: A4 landscape; margin: 8mm; }
            .no-print { display: none !important; }
            body { background: white !important; -webkit-print-color-adjust: exact !important; }
            
            /* تكرار رأس الجدول في كل صفحة مطبوعة */
            thead { display: table-header-group; }
            tfoot { display: table-footer-group; }
            
            tr { page-break-inside: avoid; }
        }
    </style>
</x-filament-panels::page>