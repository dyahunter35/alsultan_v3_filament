<x-filament-panels::page>
    <div dir="rtl" class="space-y-6 text-gray-900 dark:text-white" id="report-content">

        {{-- ═══════════════════════════════════════════════════ --}}
        {{-- HEADER --}}
        {{-- ═══════════════════════════════════════════════════ --}}
        <div
            class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl p-6 shadow-sm print:shadow-none print:border-gray-300">
            <x-report-header :label="__('report.inventory_summary_report.heading')" />
            {{-- Currency selector + rate inputs --}}
            <div
                class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4 border-t pt-4 dark:border-gray-700">
                <div class="flex items-center gap-4 flex-wrap">
                    <div class="w-60">
                        {{ $this->form }}
                    </div>

                </div>
                
                <div class="flex items-center gap-2">
                    @foreach(App\Models\Currency::all() as $currency)
                        <div class="flex items-center gap-2">
                            <span class="text-sm font-bold text-gray-500">{{ $currency->name }}:</span>
                            <input type="number" step="0.001"
                                wire:change="updateRate({{ $currency->id }}, $event.target.value)"
                                value="{{ $exchangeRates[$currency->id] ?? 1 }}"
                                class="w-28 text-left border border-gray-300 dark:border-gray-600 rounded-lg px-2 py-1 text-sm font-mono dark:bg-gray-800 focus:ring-primary-500 focus:border-primary-500">
                        </div>
                    @endforeach
                </div>
                <div class="flex items-center gap-2 print:hidden">
                    <x-filament::button color="gray" size="sm" icon="heroicon-o-arrow-path" wire:click="updatePalance">
                        تحديث الحسابات
                    </x-filament::button>
                    
                </div>
            </div>
        </div>

        {{-- ═══════════════════════════════════════════════════ --}}
        {{-- SECTION 1 — تكاليف البضاعة --}}
        {{-- ═══════════════════════════════════════════════════ --}}
        <div
            class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl overflow-hidden shadow-sm">
            @php $colHeader = 'القيمة (جنيه سوداني)'; @endphp
            <table class="w-full text-sm text-right border-collapse">
                <thead>
                    <tr class="bg-gray-100 dark:bg-gray-800 border-b dark:border-gray-700">
                        <th class="p-3 w-12 text-center font-bold text-gray-500">الرقم</th>
                        <th class="p-3 font-bold text-gray-700 dark:text-gray-200">البند</th>
                        <th class="p-3 text-center font-bold text-gray-700 dark:text-gray-200">القيمة</th>
                        <th class="p-3 text-center font-bold text-gray-700 dark:text-gray-200 w-24">المعادل</th>
                        <th
                            class="p-3 text-center font-bold text-primary-600 dark:text-primary-400 bg-primary-50/50 dark:bg-primary-900/10 w-40">
                            المبلغ بـ({{ $targetCurrencyName }})
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50 dark:divide-gray-800">
                    @foreach($goodsCosts as $i => $row)
                        <tr class="hover:bg-gray-50/60 dark:hover:bg-gray-800/30 transition-colors">
                            <td class="p-3 text-center text-gray-400 font-mono">{{ $i + 1 }}</td>
                            <td class="p-3 font-semibold">{{ $row['label'] }}</td>
                            <td class="p-3 text-center font-mono tabular-nums font-bold">
                                {{ number_format($row['value'], 2) }}
                            </td>
                            <td class="p-3 text-center font-mono tabular-nums text-gray-500">
                                <input type="number" step="0.00001"
                                    wire:change="updateCustomRate('goods', {{ $i }}, $event.target.value)"
                                    value="{{ $row['rate'] }}"
                                    class="w-24 text-center border border-gray-300 dark:border-gray-600 rounded-lg px-2 py-1 text-sm font-mono dark:bg-gray-800 focus:ring-primary-500 focus:border-primary-500">
                            </td>
                            <td
                                class="p-3 text-center font-mono tabular-nums font-bold text-primary-700 dark:text-primary-400 bg-primary-50/30 dark:bg-primary-900/5">
                                {{ number_format($row['equivalent'], 2) }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-gray-100 dark:bg-gray-800 border-t-2 border-gray-300 dark:border-gray-600">
                    <tr>
                        <td colspan="3" class="p-3 text-center font-black text-gray-500 uppercase tracking-widest">
                            التكلفة الكلية للبضاعة
                        </td>
                        <td class="p-3"></td>
                        <td
                            class="p-3 text-center font-black text-lg tabular-nums text-primary-700 dark:text-primary-300 bg-primary-50/40 dark:bg-primary-900/10">
                            {{ number_format($goodsCostsTotal, 2) }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>

        {{-- ═══════════════════════════════════════════════════ --}}
        {{-- SECTION 2 — رصيد العملاء --}}
        {{-- ═══════════════════════════════════════════════════ --}}
        <div
            class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl overflow-hidden shadow-sm">
            <table class="w-full text-sm text-right border-collapse">
                <thead>
                    <tr class="bg-gray-100 dark:bg-gray-800 border-b dark:border-gray-700">
                        <th class="p-3 w-12 text-center font-bold text-gray-500">الرقم</th>
                        <th class="p-3 font-bold text-gray-700 dark:text-gray-200">البند</th>
                        <th class="p-3 text-center font-bold text-gray-700 dark:text-gray-200">المبيعات</th>
                        <th class="p-3 text-center font-bold text-gray-700 dark:text-gray-200">الايداعات</th>
                        <th class="p-3 text-center font-bold text-gray-700 dark:text-gray-200">الرصيد</th>
                        <th class="p-3 text-center font-bold text-gray-700 dark:text-gray-200 w-24">المعادل</th>
                        <th
                            class="p-3 text-center font-bold text-primary-600 dark:text-primary-400 bg-primary-50/50 dark:bg-primary-900/10 w-40">
                            المبلغ بـ({{ $targetCurrencyName }})
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50 dark:divide-gray-800">
                    @foreach($customers as $i => $row)
                        <tr class="hover:bg-gray-50/60 dark:hover:bg-gray-800/30">
                            <td class="p-3 text-center text-gray-400 font-mono">{{ $i + 1 }}</td>
                            <td class="p-3 font-semibold">{{ $row['label'] }}</td>
                            <td class="p-3 text-center font-mono tabular-nums">{{ number_format($row['sales'], 2) }}</td>
                            <td class="p-3 text-center font-mono tabular-nums">{{ number_format($row['deposits'], 2) }}</td>
                            <td
                                class="p-3 text-center font-mono tabular-nums font-bold {{ $row['balance'] < 0 ? 'text-danger-600' : 'text-success-600' }}">
                                {{ number_format($row['balance'], 2) }}
                            </td>
                            <td class="p-3 text-center font-mono tabular-nums text-gray-500">
                                <input type="number" step="0.00001"
                                    wire:change="updateCustomRate('customers', {{ $i }}, $event.target.value)"
                                    value="{{ $row['rate'] }}"
                                    class="w-24 text-center border border-gray-300 dark:border-gray-600 rounded-lg px-2 py-1 text-sm font-mono dark:bg-gray-800 focus:ring-primary-500 focus:border-primary-500">
                            </td>
                            <td
                                class="p-3 text-center font-mono tabular-nums font-bold text-primary-700 dark:text-primary-400 bg-primary-50/30 dark:bg-primary-900/5">
                                {{ number_format($row['equivalent'], 2) }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-gray-100 dark:bg-gray-800 border-t-2 border-gray-300 dark:border-gray-600">
                    <tr>
                        <td colspan="5" class="p-3 text-center font-black text-gray-500 uppercase tracking-widest">
                            المجموع</td>
                        <td class="p-3"></td>
                        <td
                            class="p-3 text-center font-black text-lg tabular-nums text-primary-700 dark:text-primary-300 bg-primary-50/40 dark:bg-primary-900/10">
                            {{ number_format($customersTotal, 2) }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>

        {{-- ═══════════════════════════════════════════════════ --}}
        {{-- SECTION 3 — رصيد المناديب (الخزنة) --}}
        {{-- ═══════════════════════════════════════════════════ --}}
        <div
            class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl overflow-hidden shadow-sm">
            <table class="w-full text-sm text-right border-collapse">
                <thead>
                    <tr class="bg-gray-100 dark:bg-gray-800 border-b dark:border-gray-700">
                        <th class="p-3 w-12 text-center font-bold text-gray-500">الرقم</th>
                        <th class="p-3 font-bold text-gray-700 dark:text-gray-200">البند</th>
                        <th class="p-3 text-center font-bold text-gray-700 dark:text-gray-200">الايداعات</th>
                        <th class="p-3 text-center font-bold text-gray-700 dark:text-gray-200">المنصرفات</th>
                        <th class="p-3 text-center font-bold text-gray-700 dark:text-gray-200">الرصيد</th>
                        <th class="p-3 text-center font-bold text-gray-700 dark:text-gray-200 w-24">المعادل</th>
                        <th
                            class="p-3 text-center font-bold text-primary-600 dark:text-primary-400 bg-primary-50/50 dark:bg-primary-900/10 w-40">
                            المبلغ بـ({{ $targetCurrencyName }})
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50 dark:divide-gray-800">
                    @forelse($delegates as $i => $row)
                        <tr class="hover:bg-gray-50/60 dark:hover:bg-gray-800/30">
                            <td class="p-3 text-center text-gray-400 font-mono">{{ $i + 1 }}</td>
                            <td class="p-3 font-semibold">{{ $row['label'] }}</td>
                            <td class="p-3 text-center font-mono tabular-nums">{{ number_format($row['deposits'], 2) }}</td>
                            <td class="p-3 text-center font-mono tabular-nums">{{ number_format($row['expenses'], 2) }}</td>
                            <td
                                class="p-3 text-center font-mono tabular-nums font-bold {{ $row['balance'] < 0 ? 'text-danger-600' : 'text-success-600' }}">
                                {{ number_format($row['balance'], 2) }}
                            </td>
                            <td class="p-3 text-center font-mono tabular-nums text-gray-500">
                                <input type="number" step="0.00001"
                                    wire:change="updateCustomRate('delegates', {{ $i }}, $event.target.value)"
                                    value="{{ $row['rate'] }}"
                                    class="w-24 text-center border border-gray-300 dark:border-gray-600 rounded-lg px-2 py-1 text-sm font-mono dark:bg-gray-800 focus:ring-primary-500 focus:border-primary-500">
                            </td>
                            <td
                                class="p-3 text-center font-mono tabular-nums font-bold text-primary-700 dark:text-primary-400 bg-primary-50/30 dark:bg-primary-900/5">
                                {{ number_format($row['equivalent'], 2) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="p-6 text-center text-gray-400">لا يوجد مناديب</td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot class="bg-gray-100 dark:bg-gray-800 border-t-2 border-gray-300 dark:border-gray-600">
                    <tr>
                        <td colspan="5" class="p-3 text-center font-black text-gray-500 uppercase tracking-widest">
                            المجموع</td>
                        <td class="p-3"></td>
                        <td
                            class="p-3 text-center font-black text-lg tabular-nums text-primary-700 dark:text-primary-300 bg-primary-50/40 dark:bg-primary-900/10">
                            {{ number_format($delegatesTotal, 2) }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>

        {{-- ═══════════════════════════════════════════════════ --}}
        {{-- SECTION 4 — رصيد الشركات والمصانع --}}
        {{-- ═══════════════════════════════════════════════════ --}}
        <div
            class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl overflow-hidden shadow-sm">
            <table class="w-full text-sm text-right border-collapse">
                <thead>
                    <tr class="bg-gray-100 dark:bg-gray-800 border-b dark:border-gray-700">
                        <th class="p-3 w-12 text-center font-bold text-gray-500">الرقم</th>
                        <th class="p-3 font-bold text-gray-700 dark:text-gray-200">البند</th>
                        <th class="p-3 text-center font-bold text-gray-700 dark:text-gray-200">المطالبات</th>
                        <th class="p-3 text-center font-bold text-gray-700 dark:text-gray-200">المدفوعات</th>
                        <th class="p-3 text-center font-bold text-gray-700 dark:text-gray-200">الرصيد</th>
                        <th class="p-3 text-center font-bold text-gray-700 dark:text-gray-200 w-24">المعادل</th>
                        <th
                            class="p-3 text-center font-bold text-primary-600 dark:text-primary-400 bg-primary-50/50 dark:bg-primary-900/10 w-40">
                            المبلغ بـ({{ $targetCurrencyName }})
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50 dark:divide-gray-800">
                    @foreach($companies as $i => $row)
                        <tr class="hover:bg-gray-50/60 dark:hover:bg-gray-800/30">
                            <td class="p-3 text-center text-gray-400 font-mono">{{ $i + 1 }}</td>
                            <td class="p-3 font-semibold">{{ $row['label'] }}</td>
                            <td class="p-3 text-center font-mono tabular-nums">{{ number_format($row['claims'], 2) }}</td>
                            <td class="p-3 text-center font-mono tabular-nums">{{ number_format($row['payments'], 2) }}</td>
                            <td
                                class="p-3 text-center font-mono tabular-nums font-bold {{ $row['balance'] < 0 ? 'text-danger-600' : 'text-success-600' }}">
                                {{ number_format($row['balance'], 2) }}
                            </td>
                            <td class="p-3 text-center font-mono tabular-nums text-gray-500">
                                <input type="number" step="0.00001"
                                    wire:change="updateCustomRate('companies', {{ $i }}, $event.target.value)"
                                    value="{{ $row['rate'] }}"
                                    class="w-24 text-center border border-gray-300 dark:border-gray-600 rounded-lg px-2 py-1 text-sm font-mono dark:bg-gray-800 focus:ring-primary-500 focus:border-primary-500">
                            </td>
                            <td
                                class="p-3 text-center font-mono tabular-nums font-bold text-primary-700 dark:text-primary-400 bg-primary-50/30 dark:bg-primary-900/5">
                                {{ number_format($row['equivalent'], 2) }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-gray-100 dark:bg-gray-800 border-t-2 border-gray-300 dark:border-gray-600">
                    <tr>
                        <td colspan="5" class="p-3 text-center font-black text-gray-500 uppercase tracking-widest">
                            المجموع</td>
                        <td class="p-3"></td>
                        <td
                            class="p-3 text-center font-black text-lg tabular-nums text-primary-700 dark:text-primary-300 bg-primary-50/40 dark:bg-primary-900/10">
                            {{ number_format($companiesTotal, 2) }}
                        </td>
                    </tr>
                </tfoot>
            </table>
            <x-print-button/>
        </div>

        {{-- ═══════════════════════════════════════════════════ --}}
        {{-- GRAND TOTAL FOOTER --}}
        {{-- ═══════════════════════════════════════════════════ --}}
        <div class="rounded-xl overflow-hidden shadow-lg bg-gray-900 dark:bg-black border border-gray-700">
            <div class="flex items-center justify-between px-8 py-5">
                <span class="text-lg font-black text-white tracking-wider uppercase">
                    القيمة الكلية للجرد (تحديد العملة حسب الاختيار في الأعلى)
                </span>
                <div class="text-right">
                    <span class="text-4xl font-black text-primary-400 tabular-nums">
                        {{ number_format($grandTotal, 2) }}
                    </span>
                    <span class="text-sm font-bold text-gray-500 mr-2">{{ $targetCurrencyName }}</span>
                </div>
            </div>
        </div>

        <style>
            @import url('https://fonts.googleapis.com/css2?family=Almarai:wght@400;700;800&display=swap');

            .fi-main {
                font-family: 'Almarai', sans-serif !important;
            }

            @media print {

                .print\:hidden,
                .fi-topbar,
                .fi-sidebar,
                .fi-header,
                .fi-page-header {
                    display: none !important;
                }

                body {
                    background: white !important;
                }

                .bg-white,
                .dark\:bg-gray-900 {
                    background: white !important;
                }

                table {
                    border-collapse: collapse !important;
                }

                th,
                td {
                    border: 1px solid #ddd !important;
                }

                .rounded-xl {
                    border-radius: 4px !important;
                }

                .shadow-sm,
                .shadow-lg {
                    box-shadow: none !important;
                }

                .text-primary-700,
                .dark\:text-primary-400 {
                    color: #1e40af !important;
                }
            }
        </style>
    </div>
</x-filament-panels::page>