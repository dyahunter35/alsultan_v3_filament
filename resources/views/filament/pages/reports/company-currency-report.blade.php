<x-filament-panels::page>
    <x-filament::section id="report-content" class="print:shadow-none print:border-slate-300 print:p-0">

        <div class="space-y-6" dir="rtl">

            {{-- Header: Simple & Clean --}}
            <x-report-header :label="'كشف حسابات العملات الموحد'" :subtitle="'ملخص مديونيات الشركات والمصانع'" />
            <div
                class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-gray-100 dark:border-gray-800 pb-4">
                <div>
                    <p class="text-sm text-gray-500 font-medium">ملخص مديونيات الشركات والمصانع</p>
                </div>
                <div class="flex items-center gap-2 print:hidden">
                    <button wire:click="loadData" class="p-2 text-gray-400 hover:text-primary-600 transition-colors">
                        <x-heroicon-o-arrow-path class="w-5 h-5" wire:loading.class="animate-spin" />
                    </button>

                </div>
            </div>

            <div class="grid grid-cols-1 xl:grid-cols-4 gap-6">
                {{-- Compact Settings Sidebar --}}
                <div class="xl:col-span-1 space-y-4 print:hidden">
                    <div
                        class="p-5 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl shadow-sm">
                        <h2 class="text-sm font-bold text-gray-700 dark:text-gray-300 mb-4 flex items-center gap-2">
                            <x-heroicon-s-adjustments-horizontal class="w-4 h-4 text-primary-500" />
                            إعدادات العرض
                        </h2>

                        <div class="space-y-4">
                            <div
                                class="p-3 bg-gray-50 dark:bg-gray-800/50 rounded-lg border border-gray-100 dark:border-gray-800">
                                {{ $this->form }}
                            </div>

                            <div class="space-y-3">
                                <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest px-1">أسعار
                                    الصرف الحالية</label>
                                @foreach($reportData['summary'] as $item)
                                    <div
                                        class="flex flex-1 items-center justify-center gap-2 p-1 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg focus-within:border-primary-500 transition-all">
                                        <span
                                            class="text-xs font-bold text-gray-500 px-2 w-full truncate">{{ $item['currency_name'] }}</span>
                                        <input type="number" step="0.001"
                                            wire:change="updateRate({{ $item['currency_id'] }}, $event.target.value)"
                                            value="{{ $exchangeRates[$item['currency_id']] }}"
                                            class="block w-full bg-transparent border-none text-left font-mono font-bold text-sm focus:ring-0 p-1 text-gray-900 dark:text-white">
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Main Summary Table --}}
                <div class="xl:col-span-3">
                    <div
                        class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl shadow-sm overflow-hidden relative">
                        <div wire:loading wire:target="loadData, updateRate"
                            class="absolute inset-0 bg-white/40 dark:bg-gray-900/40 backdrop-blur-[1px] z-10 flex items-center justify-center">
                            <x-filament::loading-indicator class="w-8 h-8 text-primary-600" />
                        </div>

                        <div
                            class="p-4 border-b border-gray-100 dark:border-gray-800 bg-gray-50/50 dark:bg-gray-800/30">
                            <h2 class="text-sm font-bold text-gray-700 dark:text-gray-300 flex items-center gap-2">
                                <x-heroicon-s-chart-pie class="w-4 h-4 text-success-500" />
                                ملخص الأرصدة المجمعة
                            </h2>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="w-full text-sm text-right border-collapse">
                                <thead>
                                    <tr class="text-gray-500 border-b dark:border-gray-800">
                                        <th class="p-4 font-bold text-xs uppercase">العملة</th>
                                        <th class="p-4 font-bold text-xs uppercase text-center">المطالبات</th>
                                        <th class="p-4 font-bold text-xs uppercase text-center">المدفوعات</th>
                                        <th class="p-4 font-bold text-xs uppercase text-center">الرصيد الصافي</th>
                                        <th
                                            class="p-4 font-bold text-xs uppercase text-center bg-primary-50/30 dark:bg-primary-900/10 text-primary-600">
                                            المعادل بـ {{ $reportData['target_currency'] }}</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-50 dark:divide-gray-800">
                                    @foreach($reportData['summary'] as $item)
                                        <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/20 transition-colors">
                                            <td class="p-4 font-bold text-gray-700 dark:text-gray-300">
                                                {{ $item['currency_name'] }}</td>
                                            <td class="p-4 text-center font-mono text-gray-500">
                                                {{ number_format($item['total_claims'], 2) }}</td>
                                            <td class="p-4 text-center font-mono text-gray-500">
                                                {{ number_format($item['total_payments'], 2) }}</td>
                                            <td
                                                class="p-4 text-center font-mono font-bold {{ $item['total_balance'] < 0 ? 'text-danger-600' : 'text-success-600' }}">
                                                {{ number_format($item['total_balance'], 2) }}
                                            </td>
                                            <td
                                                class="p-4 text-center font-mono font-bold text-primary-700 dark:text-primary-400 bg-primary-50/20 dark:bg-primary-900/5">
                                                {{ number_format($item['equivalent'], 2) }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="bg-gray-50 dark:bg-gray-800/50">
                                    <tr>
                                        <td colspan="4" class="p-4 text-left font-bold text-gray-400 text-xs">إجمالي
                                            المحفظة المالية</td>
                                        <td class="p-4 text-center font-mono font-black text-lg text-primary-600">
                                            {{ number_format($grandTotalEquivalent, 2) }}
                                            <span
                                                class="text-[10px] font-bold text-gray-400 mr-1">{{ $reportData['target_currency'] }}</span>
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Detailed Sections: More Compact Cards --}}
            <div class="space-y-6 pt-4">
                <h3
                    class="text-xs font-black text-gray-400 uppercase tracking-[0.2em] border-r-4 border-primary-500 pr-3">
                    تفاصيل الأرصدة لكل عملة</h3>

                @foreach($reportData['groups'] as $currencyId => $group)
                    <div
                        class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl shadow-sm overflow-hidden">
                        <div
                            class="p-4 flex items-center justify-between bg-gray-50/50 dark:bg-gray-800/30 border-b dark:border-gray-800">
                            <div class="flex items-center gap-3">
                                <span
                                    class="px-2 py-1 rounded bg-gray-800 text-white text-[10px] font-black">{{ $group['currency_code'] }}</span>
                                <h2 class="text-sm font-bold text-gray-800 dark:text-white">حسابات
                                    {{ $group['currency_name'] }}</h2>
                            </div>
                            <span class="text-[10px] font-bold text-gray-400">صرف: {{ $exchangeRates[$currencyId] }}</span>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="w-full text-xs text-right border-collapse">
                                <thead>
                                    <tr class="text-gray-400 border-b dark:border-gray-800">
                                        <th class="p-3 text-center w-10">#</th>
                                        <th class="p-3 text-right">الشركة / المصنع</th>
                                        <th class="p-3 text-center">المطالبات</th>
                                        <th class="p-3 text-center">المدفوعات</th>
                                        <th class="p-3 text-center border-x dark:border-gray-800">الرصيد
                                            ({{ $group['currency_code'] }})</th>
                                        <th class="p-3 text-center bg-gray-50 dark:bg-gray-800/50 font-bold">المعادل بـ
                                            ({{ $reportData['target_currency'] }})</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-50 dark:divide-gray-800/50">
                                    @foreach($group['companies'] as $idx => $company)
                                        <tr class="hover:bg-gray-50/30 dark:hover:bg-gray-800/10 transition-colors">
                                            <td class="p-3 text-center font-mono text-gray-300">{{ $idx + 1 }}</td>
                                            <td class="p-3 font-bold text-gray-700 dark:text-gray-300">{{ $company['name'] }}
                                            </td>
                                            <td class="p-3 text-center font-mono text-gray-500">
                                                {{ number_format($company['claims'], 2) }}</td>
                                            <td class="p-3 text-center font-mono text-gray-500">
                                                {{ number_format($company['payments'], 2) }}</td>
                                            <td
                                                class="p-3 text-center font-mono font-bold border-x dark:border-gray-800 {{ $company['balance'] < 0 ? 'text-danger-600' : 'text-success-600' }}">
                                                {{ $company['balance'] == 0 ? '-' : number_format($company['balance'], 2) }}
                                            </td>
                                            <td class="p-3 text-center font-mono font-bold text-primary-600">
                                                {{ number_format($company['balance'] * ($exchangeRates[$currencyId] / (App\Models\Currency::find($targetCurrencyId)->exchange_rate ?? 1)), 2) }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot
                                    class="border-t border-gray-200 dark:border-gray-700 bg-gray-50/30 dark:bg-gray-800/20">
                                    <tr class="font-bold">
                                        <td colspan="2" class="p-3 text-left text-gray-400">المجاميع الفرعية</td>
                                        <td class="p-3 text-center font-mono">
                                            {{ number_format($group['totals']['claims'], 2) }}</td>
                                        <td class="p-3 text-center font-mono">
                                            {{ number_format($group['totals']['payments'], 2) }}</td>
                                        <td
                                            class="p-3 text-center font-mono text-sm border-x dark:border-gray-800 {{ $group['totals']['balance'] < 0 ? 'text-danger-600' : 'text-success-600' }}">
                                            {{ number_format($group['totals']['balance'], 2) }}
                                        </td>
                                        <td
                                            class="p-3 text-center font-mono text-sm text-primary-700 dark:text-primary-400 bg-primary-50/30 dark:bg-primary-900/10">
                                            {{ number_format($group['totals']['equivalent'], 2) }}
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                @endforeach
                <x-print-button class="no-print" />
            </div>

            <style>
                @import url('https://fonts.googleapis.com/css2?family=Almarai:wght@400;700&display=swap');

                .fi-main {
                    font-family: 'Almarai', sans-serif !important;
                }

                @media print {
                    .no-print {
                        display: none !important;
                    }

                    .print\:hidden {
                        display: none !important;
                    }

                    .fi-sidebar,
                    .fi-topbar,
                    .fi-header {
                        display: none !important;
                    }

                    body {
                        background: white !important;
                    }

                    .rounded-xl {
                        border-radius: 4px !important;
                    }

                    .shadow-sm {
                        box-shadow: none !important;
                    }

                    .border,
                    .border-b,
                    .border-x {
                        border-color: #f1f1f1 !important;
                    }

                    table {
                        page-break-inside: auto;
                    }

                    tr {
                        page-break-inside: avoid;
                    }
                }
            </style>
        </div>
    </x-filament::section>
</x-filament-panels::page>