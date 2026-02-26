<x-filament-panels::page>
    <head>
            @vite('resources/js/app.js')
    </head>
    {{-- جذر Livewire الرئيسي --}}
    <div id="report-content">
    <div id="main-ledger-container">
        
        <style id="custom-ledger-styles">
            /* إجبار الجدول على أخذ العرض بالكامل ومنع التفاف النصوص في العناوين */
            .ledger-table { width: 100% !important; table-layout: auto; }
            .ledger-table th { background-color: #1e293b !important; color: white !important; border: 1px solid #475569; padding: 6px; font-size: 11px; white-space: nowrap; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .ledger-table td { border: 1px solid #cbd5e1; padding: 4px; font-size: 11px; vertical-align: middle; text-align: center; }
            .bg-side-blue { background-color: #f8fafc; font-weight: bold; color: #1e293b; }
            .row-group-total { background-color: #1e293b !important; color: white !important; font-weight: bold; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .text-right-custom { text-align: right !important; padding-right: 10px !important; }
            
            @media print {
                .no-print { display: none !important; }
                body { background: white !important; -webkit-print-color-adjust: exact; }
                .fi-main-ctn { padding: 0 !important; margin: 0 !important; }
                /* إجبار الطابعة على الطباعة بالعرض (Landscape) */
                @page { size: landscape; margin: 10mm; }
            }
        </style>

        {{-- قسم الفلاتر --}}
        <x-filament::section class="no-print mb-4">
            {{ $this->form }}
        </x-filament::section>

        @if($companyId)
            {{-- الترويسة الرئيسية - سنعطيها ID لنسخها في الطباعة --}}
            <div id="printable-header">
                <x-report-header label="كشف حساب شركة: " :value="$_company?->name" />
            </div>

            <div id="full-report-container">
                
                {{-- قسم الإحصائيات (Stats) --}}
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6 mt-4 no-print">
                    <!-- المطلوبات (مدين) -->
                    <div class="bg-gradient-to-br from-white to-slate-50 border border-slate-200 rounded-2xl p-5 shadow-sm hover:shadow-md transition-all border-r-4 border-r-blue-600">
                        <div class="flex justify-between items-start">
                            <div>
                                <p class="text-xs font-bold text-slate-500 mb-1 uppercase tracking-wider">إجمالي المطلوبات (شحن)</p>
                                <h3 class="text-2xl font-black text-slate-900 tabular-nums">
                                    {{ number_format($summary['total_debit'], 2) }}
                                    @if($withRates)
                                        <span class="block text-sm font-bold text-blue-600 mt-1">
                                            معادل: {{ number_format($summary['total_debit_eq'], 2) }}
                                        </span>
                                    @endif
                                </h3>
                            </div>
                            <div class="p-2 bg-blue-100 rounded-lg text-blue-600">
                                <x-heroicon-m-shopping-cart class="w-6 h-6" />
                            </div>
                        </div>
                    </div>

                    <!-- المدفوعات (دائن) -->
                    <div class="bg-gradient-to-br from-white to-slate-50 border border-slate-200 rounded-2xl p-5 shadow-sm hover:shadow-md transition-all border-r-4 border-r-rose-600">
                        <div class="flex justify-between items-start">
                            <div>
                                <p class="text-xs font-bold text-slate-500 mb-1 uppercase tracking-wider">إجمالي المدفوعات (سداد)</p>
                                <h3 class="text-2xl font-black text-slate-900 tabular-nums">
                                    {{ number_format($summary['total_credit'], 2) }}
                                    @if($withRates)
                                        <span class="block text-sm font-bold text-rose-600 mt-1">
                                            معادل: {{ number_format($summary['total_credit_eq'], 2) }}
                                        </span>
                                    @endif
                                </h3>
                            </div>
                            <div class="p-2 bg-rose-100 rounded-lg text-rose-600">
                                <x-heroicon-m-banknotes class="w-6 h-6" />
                            </div>
                        </div>
                    </div>

                    <!-- صافي المطلوب (الرصيد النهائي) -->
                    <div class="bg-gradient-to-br from-white to-slate-50 border border-slate-200 rounded-2xl p-5 shadow-sm hover:shadow-md transition-all border-r-4 border-r-amber-500">
                        <div class="flex justify-between items-start">
                            <div>
                                <p class="text-xs font-bold text-slate-500 mb-1 uppercase tracking-wider">صافي المطلوب (الرصيد)</p>
                                <h3 class="text-2xl font-black text-slate-900 tabular-nums {{ $summary['final_balance'] >= 0 ? 'text-amber-600' : 'text-rose-600' }}">
                                    {{ number_format($summary['final_balance'], 2) }}
                                    @if($withRates)
                                        <span class="block text-sm font-bold mt-1 {{ $summary['final_balance_eq'] >= 0 ? 'text-emerald-600' : 'text-rose-600' }}">
                                            معادل: {{ number_format($summary['final_balance_eq'], 2) }}
                                        </span>
                                    @endif
                                </h3>
                            </div>
                            <div class="p-2 bg-amber-100 rounded-lg text-amber-600">
                                <x-heroicon-m-calculator class="w-6 h-6" />
                            </div>
                        </div>
                    </div>
                </div>
                {{-- الجدول الأول --}}
                <div id="print-section-1" class="mb-10 bg-white p-4 rounded-xl border border-gray-200 shadow-sm mt-4">
                    <div class="flex justify-between items-center mb-4 no-print border-b pb-2">
                        <h3 class="font-bold text-sm border-r-4 border-emerald-600 pr-2 text-gray-800">اولا: حركة كشف الحساب التراكمي</h3>
                        <x-filament::button 
                            color="gray" 
                            icon="heroicon-m-printer" 
                            size="sm" 
                            outlined
                            tag="button"
                            onclick="printWithHeader('print-section-1')">
                            طباعة هذا الجدول فقط
                        </x-filament::button>
                    </div>

                    <table class="w-full ledger-table border-collapse" dir="rtl">
                        <thead>
                            <tr class="bg-slate-800 text-white">
                                <th class="p-2 border border-slate-600">#</th>
                                <th class="p-2 border border-slate-600">التاريخ</th>
                                <th class="p-2 border border-slate-600 text-right px-4">البيان / المرجع</th>
                                <th class="p-2 border border-slate-600 w-32 bg-slate-700">مدين (+) شحن</th>
                                <th class="p-2 border border-slate-600 w-32 bg-slate-700">دائن (-) سداد</th>
                                <th class="p-2 border border-slate-600 w-32 bg-emerald-800">المعامل</th>
                                <th class="p-2 border border-slate-600 w-40 bg-emerald-950 text-yellow-100">الرصيد المعادل</th>
                                
                                @if ($withRates)
                                <th class="p-2 border border-slate-600 w-40 bg-slate-900 text-yellow-400">الرصيد بدون معادل</th>
                                <th class="p-2 border border-slate-600 w-32 bg-emerald-900">المعادل (+) مدين</th>
                                <th class="p-2 border border-slate-600 w-32 bg-emerald-900">المعادل (-) دائن</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="bg-blue-50 font-bold italic text-slate-900">
                                <td class="p-2 border border-slate-300">{{ $start ?? '-' }}</td>
                                <td class="p-2 text-right px-4">رصيد مدور من فترة سابقة</td>
                                <td>-</td>
                                <td>-</td>
                                <td>-</td>
                                <td class="bg-emerald-50">
                                    <input type="number" step="0.0001" wire:model.live="opening_factor" 
                                        class="w-24 border-none bg-transparent text-center no-print font-bold text-emerald-700 focus:ring-0" 
                                        placeholder="1.0">
                                </td>
                                <td class="font-black tabular-nums bg-emerald-100 text-emerald-900">{{ number_format($opening_balance * $opening_factor, 2) }}</td>
                                @if ($withRates)
                                <td class="font-black tabular-nums">{{ number_format($opening_balance, 2) }}</td>
                                <td class="bg-emerald-50">-</td>
                                <td class="bg-emerald-50">-</td>
                                @endif
                            </tr>
                            @foreach ($report_lines as $index => $line)
                                <tr class="hover:bg-slate-50">
                                    <td>{{ ++$index }}</td>
                                    <td>{{ $line['date'] }}</td>
                                    <td class="text-right px-4 font-medium  text-center"> 
                                        @if ($line['type']=='truck')
                                            <a href="#row-{{ $line['id'] }}" class="text-blue-600 hover:underline">{{ $line['ref'] }}</a>
                                        @else
                                            {{ $line['ref'] }}
                                        @endif
                                    </td>
                                    <td class="font-bold tabular-nums">{{ $line['debit'] > 0 ? number_format($line['debit'], 2) : '-' }}</td>
                                    <td class="font-bold text-red-600 tabular-nums">{{ $line['credit'] > 0 ? number_format($line['credit'], 2) : '-' }}</td>
                                    
                                    <td class="bg-emerald-50 border-r-2 border-emerald-200">
                                        <input type="number" step="0.0001" wire:model.live="factors.{{ $index - 1 }}" 
                                            class="w-24 border-none bg-transparent text-center no-print font-bold text-emerald-700 focus:ring-0" 
                                            placeholder="1.0">
                                    </td>
                                    <td class="font-black bg-emerald-100 text-emerald-900 tabular-nums">{{ number_format($line['balance_eq'], 2) }}</td>
                                    
                                    @if ($withRates)
                                    <td class="font-black bg-slate-50 tabular-nums">{{ number_format($line['balance'], 2) }}</td>
                                    <td class="font-bold tabular-nums bg-emerald-50">{{ $line['debit_eq'] > 0 ? number_format($line['debit_eq'], 2) : '-' }}</td>
                                    <td class="font-bold text-red-600 tabular-nums bg-emerald-50">{{ $line['credit_eq'] > 0 ? number_format($line['credit_eq'], 2) : '-' }}</td>
                                    @endif
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="row-group-total">
                            <tr>
                                <td colspan="3" class="text-left px-4 py-3 bg-slate-900">الإجماليات الختامية:</td>
                                <td class="tabular-nums bg-slate-800">{{ number_format($summary['total_debit'], 2) }}</td>
                                <td class="text-red-400 tabular-nums bg-slate-800">{{ number_format($summary['total_credit'], 2) }}</td>
                                <td class="bg-emerald-900 border-r-2 border-emerald-700"></td>
                                
                                <td class="bg-emerald-500 text-white text-lg tabular-nums">{{ number_format($summary['final_balance_eq'], 2) }}</td>
                                @if ($withRates)
                                <td class="bg-yellow-500 text-slate-900 text-lg tabular-nums">{{ number_format($summary['final_balance'], 2) }}</td>
                                <td class="tabular-nums bg-emerald-800 text-emerald-200">{{ number_format($summary['total_debit_eq'], 2) }}</td>
                                <td class="text-red-300 tabular-nums bg-emerald-800">{{ number_format($summary['total_credit_eq'], 2) }}</td>
                                @endif
                            </tr>
                        </tfoot>
                    </table>
                </div>

                {{-- الجدول الثاني --}}
                <div id="print-section-2" class="mb-10 bg-white p-4 rounded-xl border border-gray-200 shadow-sm">
                    <div class="flex justify-between items-center mb-4 no-print border-b pb-2">
                        <h3 class="font-bold text-sm border-r-4 border-slate-800 pr-2 text-gray-800">ثانيا: تفاصيل الشحنات</h3>
                        <x-filament::button 
                            color="gray" 
                            icon="heroicon-m-printer" 
                            size="sm" 
                            outlined
                            tag="button"
                            onclick="printWithHeader('print-section-2')">
                            طباعة هذا الجدول فقط
                        </x-filament::button>
                    </div>

                    <table class="w-full ledger-table border-collapse" dir="rtl">
                        <thead>
                            <tr>
                                <th rowspan="3" class="bg-slate-200 border-slate-900 w-32 text-slate-900">التاريخ / البيان</th>
                                <th colspan="10" class="border-slate-900 italic py-2">بيان الفاتورة (الطلبية)</th>
                            </tr>
                            <tr>
                                <th colspan="4" class="border-slate-900 italic py-2">بيان الصنف</th>
                                <th colspan="3" class="border-slate-900 italic py-2">الكميات</th>
                                <th colspan="3" class="border-slate-900 italic py-2">التسعير</th>
                            </tr>
                            <tr class="bg-slate-700 text-white">
                                <th class="bg-slate-800">#</th>
                                <th class="bg-slate-800 text-right px-4">الصنف</th>
                                <th class="bg-slate-800">المقاس</th>
                                <th class="bg-slate-800">و.الوحدة</th>
                                <th class="bg-slate-800">العدد</th>
                                <th class="bg-slate-800">الطرد</th>
                                <th class="bg-slate-800 text-blue-400">الطن</th>
                                <th class="bg-slate-800">س.الوحدة</th>
                                <th class="bg-slate-800">س.الطن</th>
                                <th class="bg-slate-800">المجموع</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($combined_records as $record)
                                @if($record['type'] === 'truck')
                                    @foreach($record['cargos'] as $index => $cargo)
                                        <tr>
                                            @if($index === 0)
                                            <td id="row-{{ $record['id'] }}" rowspan="{{ count($record['cargos']) + 1 }}" class="bg-white font-bold border-r-2 border-slate-900">
                                                <div class="mb-1">{{ $record['date'] }}</div>
                                                <div class="no-print">
                                                    <a href="{{ \App\Filament\Resources\Trucks\TruckResource::getUrl('edit', ['record' => $record['id']]) }}" target="_blank" class="text-blue-600 text-[10px] flex items-center justify-center gap-1">
                                                        <x-heroicon-o-eye class="w-3 h-3"/>{{ $record['ref'] }}
                                                    </a>
                                                </div>
                                            </td>
                                            @endif
                                            <td>{{ $index + 1 }}</td>
                                            <td class="text-right-custom font-medium">{{ $cargo->product->name ?? '-' }}</td>
                                            <td>{{ $cargo->size ?? '-' }}</td>
                                            <td>{{ number_format($cargo->weight, 2) }}</td>
                                            <td>{{ number_format($cargo->unit_quantity, 2) }}</td>
                                            <td>{{ number_format($cargo->quantity, 2) }}</td>
                                            <td class="font-bold text-blue-800">{{ number_format($cargo->ton_weight, 3) }}</td>
                                            <td class="text-slate-400">{{ number_format($cargo->unit_price, 2) }}</td>
                                            <td class="text-slate-400">{{ number_format($cargo->ton_price, 2) }}</td>
                                            <td class="font-bold">{{ number_format($cargo->ton_price * $cargo->ton_weight, 2) }}</td>
                                        </tr>
                                    @endforeach
                                    <tr class="row-group-total">
                                        <td colspan="5" class="text-right px-4 italic text-xs">إجمالي الشحنة {{ $record['ref'] }}</td>
                                        <td>{{ number_format($record['cargos']->sum('quantity'), 2) }}</td>
                                        <td>{{ number_format($record['cargos']->sum('ton_weight'), 3) }}</td>
                                        <td colspan="2"></td>
                                        <td colspan="1" class="bg-slate-900 text-white text-left px-4 italic font-bold">
                                            {{ number_format($record['total'], 2) }}
                                        </td>
                                    </tr>
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                <div class="flex justify-center mt-8 no-print">
                    <x-filament::button 
                        color="warning" 
                        icon="heroicon-m-printer" 
                        size="lg" 
                        onclick="window.print()">
                        طباعة الكشف بالكامل
                    </x-filament::button>
                </div>
            </div>
        @endif
    </div>
    </div>
    @push('scripts')
    <script>
        
    </script>
    @endpush
</x-filament-panels::page>