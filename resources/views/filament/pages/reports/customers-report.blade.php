<x-filament-panels::page>
    {{-- 1. قسم الفلترة (يختفي عند الطباعة) --}}
    <x-filament::section class="mb-4 shadow-sm no-print border-slate-200">
        <div class="flex flex-col gap-4 md:flex-row md:items-end">
            <div class="flex-1">
                {{-- فورم الفلترة الخاص بـ Filament --}}
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
            <x-report-header :label="$this->getTitle()" :value="$customer?->name ?? '—'" />

            {{-- 3. بيانات العميل والملخص --}}

            {{-- بيانات العميل --}}
            <div
                class="grid grid-cols-1 overflow-hidden bg-white border shadow-sm md:grid-cols-3 print:grid-cols-3 rounded-xl print:shadow-none print:border-slate-300">

                {{-- قسم العنوان --}}
                <div
                    class="flex items-center justify-center gap-2 px-4 py-3 border-b md:border-b-0 md:border-l border-slate-100 print:border-l print:border-b-0">
                    <x-filament::icon icon="heroicon-m-map-pin" class="w-4 h-4 text-slate-400" />
                    <span class="text-sm font-bold text-slate-400">العنوان:</span>
                    <span class="text-sm font-medium text-slate-700">{{ $customer?->address ?? '-' }}</span>
                </div>

                {{-- قسم الهاتف --}}
                <div
                    class="flex items-center justify-center gap-2 px-4 py-3 border-b md:border-b-0 md:border-l border-slate-100 print:border-l print:border-b-0">
                    <x-filament::icon icon="heroicon-m-phone" class="w-4 h-4 text-slate-400" />
                    <span class="text-sm font-bold text-slate-400">الهاتف:</span>
                    <span class="text-sm font-bold tabular-nums text-slate-700">{{ $customer?->phone ?? '-' }}</span>
                </div>

                {{-- قسم الرصيد --}}
                <div class="flex items-center justify-center gap-3 px-4 py-3 bg-slate-50/50 print:bg-transparent">
                    <x-filament::icon icon="heroicon-m-credit-card" class="w-4 h-4 text-slate-400" />
                    <span class="text-sm font-bold text-slate-400">إجمالي الرصيد:</span>
                    <span
                        class="text-lg font-black tabular-nums tracking-tight {{ ($customer?->balance ?? 0) < 0 ? 'text-red-600' : 'text-green-700' }}">
                        {{ number_format($customer?->balance ?? 0, 2) }}
                    </span>
                </div>

            </div>
            {{-- 4. جدول الأستاذ (Ledger) --}}
            <div
                class="overflow-x-auto bg-white border shadow-sm border-slate-200 rounded-xl print:border-slate-800 print:rounded-none">
                <table class="w-full text-center border-collapse text-sm print:text-[11px]">
                    <thead>
                        <tr class="font-bold text-white bg-slate-800">
                            <th class="p-3 border border-slate-700">التاريخ</th>
                            <th class="p-3 text-right border border-slate-700">المعاملة</th>
                            <th class="p-3 text-right border border-slate-700">البيان / الوصف</th>
                            <th class="p-3 border border-slate-700 bg-green-900/50">دائن (+)</th>
                            <th class="p-3 border border-slate-700 bg-red-900/50">مدين (-)</th>
                            <th class="p-3 italic border border-slate-700 bg-slate-700">الرصيد</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 tabular-nums">
                        @foreach ($ledger as $row)
                            <tr class="transition-colors hover:bg-slate-50">
                                <td class="p-2 border border-slate-200 text-slate-500">{{ $row['date'] }}</td>
                                <td class="p-2 px-4 font-medium text-right border border-slate-200">
                                    {{ $row['description'] }}</td>
                                    <td class="p-2 text-right border border-slate-200 min-w-[150px] leading-snug">
                                    @if ($row['data'] instanceof \Illuminate\Support\Collection )
                        
                                <table class="w-full border-collapse text-[12px] divide-y divide-slate-200">
                                        <tbody>
                                            @foreach ($row['data'] as $item)
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
                                    {{  $row['data']}}
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
                    {{-- الجزء الخاص بالتذييل (Footer) في جدول كشف الحساب --}}
                    <tfoot class="font-black border-t-2 bg-slate-100 border-slate-800">
                        <tr>
                            <td colspan="3" class="p-3 px-6 text-center">الإجمالي</td>
                            <td class="p-3 text-green-700 border border-slate-300">
                                {{ number_format($ledger->sum('amount_in'), 2) }}
                            </td>
                            <td class="p-3 text-red-700 border border-slate-300">
                                {{ number_format($ledger->sum('amount_out'), 2) }}
                            </td>
                            <td
                                class="p-3 text-lg italic font-black border border-slate-800 bg-slate-200 print:text-sm">
                                @php
                                    $lastEntry = $ledger->last();
                                @endphp
                                {{ number_format($lastEntry['balance'] ?? 0, 2) }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            {{-- تذييل الطباعة --}}
            <div class="justify-between hidden px-8 mt-12 text-xs italic print:flex text-slate-400">
                <p></p>
                <p>توقيع المحاسب المسئول: ............................</p>
            </div>
        </div>

        <x-print-button />
    @else
        <div class="p-20 text-center bg-white border-2 border-gray-300 border-dashed shadow-sm rounded-xl">
            <x-filament::icon icon="heroicon-o-document-magnifying-glass"
                class="w-12 h-12 mx-auto mb-4 text-gray-300" />
            <h3 class="text-xl font-bold tracking-tight text-gray-400">لا توجد حركات مالية مسجلة لهذا العميل في الفترة
                المحددة</h3>
        </div>
    @endif

    <style>
        @font-face {
            font-family: 'FlatJooza';
            src: url('{{ asset('fonts/flat-jooza-regular.woff2') }}') format('woff2');
        }

        #report-content {
            font-family: 'FlatJooza', sans-serif;
        }

        @media print {
            @page {
                size: A4 portrait;
                margin: 15mm;
            }

            .no-print {
                display: none !important;
            }

            .fi-main-ctn {
                padding: 0 !important;
                width: 100% !important;
            }

            body {
                background: white !important;
                -webkit-print-color-adjust: exact !important;
                color: black;
            }

            .tabular-nums {
                font-variant-numeric: tabular-nums;
            }
        }
    </style>
</x-filament-panels::page>
