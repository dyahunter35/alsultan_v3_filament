<x-filament-panels::page>
    {{-- 1. قسم الفلترة --}}
    <x-filament::section class="mb-4 shadow-sm no-print border-slate-200">
        <div class="flex flex-col gap-4 md:flex-row md:items-end">
            <div class="flex-1">
                {{ $this->form }}
            </div>
            <div class="flex items-center gap-2">
                <x-filament::button wire:click="loadData" color="gray" icon="heroicon-m-arrow-path">
                    تحديث
                </x-filament::button>
            </div>
        </div>
    </x-filament::section>

    @if ($ledger && count($ledger))
        <div id="report-content" class="space-y-6 print:m-0">

            {{-- 2. الهيدر الموحد --}}
            <x-report-header :label="$this->getTitle()" :value="$delegate?->name ?? '—'" />

            {{-- 3. بطاقة بيانات المندوب --}}
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
            
            {{-- 4. جدول الأستاذ المطور --}}
            <div class="overflow-x-auto bg-white border shadow-sm border-slate-200 rounded-xl print:border-slate-800 print:rounded-none">
                <table class="w-full text-center border-collapse text-[11px] print:text-[10px]">
                    <thead>
                        <tr class="font-bold text-white bg-slate-800">
                            <th class="w-24 p-2 border border-slate-700">التاريخ</th>
                            <th class="w-24 p-2 border border-slate-700">المعاملة</th>
                            <th class="w-32 p-2 border border-slate-700">العميل</th>
                            <th class="p-2 text-right border border-slate-700">التفاصيل (الأصناف)</th>
                            <th class="w-24 p-2 border border-slate-700 bg-green-900/50">مدين (+)</th>
                            <th class="w-24 p-2 border border-slate-700 bg-red-900/50">دائن (-)</th>
                            <th class="w-28 p-2 italic border border-slate-700 bg-slate-700">الرصيد</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 tabular-nums">
                        @foreach ($ledger as $row)
                            <tr class="transition-colors hover:bg-slate-50/50">
                                <td class="p-2 border border-slate-200 text-slate-500 font-medium">{{ $row['date'] }}</td>
                                <td class="p-2 border border-slate-200 font-bold text-slate-700">{{ $row['transaction_name'] }}</td>
                                <td class="p-2 border border-slate-200 font-medium">{{ $row['customer_name'] ?? '—' }}</td>
                                
                                {{-- عمود التفاصيل الموزون --}}
                                <td class="p-1 border border-slate-200 bg-slate-50/20 align-top">
                                    @if ($row['details'] instanceof \Illuminate\Support\Collection)
                                        <table class="w-full border-collapse text-[10px] leading-tight">
                                            <tbody>
                                                @foreach ($row['details'] as $item)
                                                    <tr class="border-b border-slate-100 last:border-0">
                                                        <td class="text-right py-1 font-bold text-slate-700">{{ $item->product?->name }}</td>
                                                        <td class="text-center w-8 font-black text-slate-900">x{{ (int)$item->qty }}</td>
                                                        <td class="text-center w-12 text-slate-500">{{ number_format($item->price, 1) }}</td>
                                                        <td class="text-left w-16 font-black text-green-700">{{ number_format($item->qty * $item->price, 1) }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    @else
                                        <span class="px-2 text-slate-600 italic">{{ $row['details'] }}</span>
                                    @endif
                                </td>

                                <td class="p-2 font-bold text-green-700 border border-slate-200 bg-green-50/10">
                                    {{ $row['amount_in'] > 0 ? number_format($row['amount_in'], 2) : '—' }}
                                </td>
                                <td class="p-2 font-bold text-red-700 border border-slate-200 bg-red-50/10">
                                    {{ $row['amount_out'] > 0 ? number_format($row['amount_out'], 2) : '—' }}
                                </td>
                                <td class="p-2 font-black border border-slate-200 text-slate-800 bg-slate-100/50">
                                    {{ number_format($row['balance'], 2) }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="font-black border-t-2 bg-slate-800 text-white border-slate-900">
                        <tr>
                            <td colspan="4" class="p-2 px-6 text-left text-sm uppercase">إجمالي الحركة</td>
                            <td class="p-2 border border-slate-700 bg-green-700/50">{{ number_format($ledger->sum('amount_in'), 2) }}</td>
                            <td class="p-2 border border-slate-700 bg-red-700/50">{{ number_format($ledger->sum('amount_out'), 2) }}</td>
                            <td class="p-2 text-sm border border-slate-700 bg-slate-900">{{ number_format($ledger->last()['balance'] ?? 0, 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div class="justify-between hidden px-8 mt-12 text-xs italic print:flex text-slate-400">
                <p>تاريخ الطباعة: {{ now()->format('Y-m-d H:i') }}</p>
                <p>توقيع المحاسب: ............................</p>
            </div>
        </div>
        <x-print-button />
    @else
        {{-- تنبيه عدم وجود بيانات --}}
        <div class="p-20 text-center bg-white border-2 border-gray-300 border-dashed rounded-xl">
            <x-filament::icon icon="heroicon-o-document-magnifying-glass" class="w-12 h-12 mx-auto mb-4 text-gray-300" />
            <h3 class="text-xl font-bold text-gray-400">لا توجد حركات مسجلة</h3>
        </div>
    @endif

    <style>
        @font-face { font-family: 'FlatJooza'; src: url('{{ asset('fonts/flat-jooza-regular.woff2') }}') format('woff2'); }
        #report-content { font-family: 'FlatJooza', sans-serif; }
        @media print {
            @page { size: A4 landscape; margin: 8mm; } {{-- Landscape أفضل لهذا الجدول العريض --}}
            .no-print { display: none !important; }
            body { background: white !important; -webkit-print-color-adjust: exact !important; }
            thead { display: table-header-group; }
            .tabular-nums { font-variant-numeric: tabular-nums; }
        }
    </style>
</x-filament-panels::page>