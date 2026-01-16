<x-filament-panels::page>

<x-filament::section class="mb-4 shadow-sm no-print border-slate-200">
        <div class="flex flex-col gap-4 md:flex-row md:items-end">
            <div class="flex-1">
                {{-- فورم الفلترة الخاص بـ Filament --}}
                {{ $this->form }}
            </div>

            <div class="flex items-center gap-2">
                <x-filament::button wire:click="updateBalances" color="gray" icon="heroicon-m-arrow-path">
                    تحديث حسابات العملاء
                </x-filament::button>
            </div>
        </div>
    </x-filament::section>
    <div id="report-content" class="space-y-6 print:m-0">

            {{-- 2. الهيدر الموحد --}}
        <x-report-header :label="$this->getTitle()" />

        {{-- محتوى التقرير --}}
            <div class="overflow-x-auto rounded-md">
                <table class="w-full text-sm text-right border-separate border-spacing-0">
                    <thead>
                        <tr class="bg-gray-200 dark:bg-gray-800/50">
                            <th class="px-6 py-4 font-bold text-gray-700 dark:text-gray-300 border-b first:rounded-tr-xl">#</th>
                            <th class="px-6 py-4 font-bold text-gray-700 dark:text-gray-300 border-b">اسم العميل</th>
                            <th class="px-6 py-4 font-bold text-gray-700 dark:text-gray-300 border-b">المنطقة</th>
                            <th class="px-6 py-4 font-bold text-emerald-700 dark:text-emerald-400 border-b bg-emerald-50/30">له (Credit)</th>
                            <th class="px-6 py-4 font-bold text-rose-700 dark:text-rose-400 border-b bg-rose-50/30 last:rounded-tl-xl">عليه (Debt)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800 bg-white dark:bg-gray-800/50">
                        @php $totalCredit = 0; $totalDebt = 0; @endphp
                        @foreach($customers as $index => $customer)
                            @php
                                $balance = $customer['balance'];
                                $credit = $balance < 0 ? abs($balance) : 0;
                                $debt = $balance > 0 ? $balance : 0;
                                $totalCredit += $credit; $totalDebt += $debt;
                            @endphp
                            <tr @click="focusedRowIndex = {{ $index }}"
                                :class="focusedRowIndex === {{ $index }} ? 'bg-primary-50/50 dark:bg-primary-900/10' : ''"
                                class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors cursor-pointer">
                                
                                <td class="px-6 py-4 text-gray-400 text-xs">{{ $loop->iteration }}</td>
                                <td class="px-6 py-4 font-bold text-gray-900 dark:text-white">{{ $customer['name'] }}</td>
                                <td class="px-6 py-4 text-gray-500 text-xs">{{ $customer['region'] }}</td>
                                
                                <td class="px-6 py-4 text-center font-mono font-bold text-emerald-600">
                                    {{ $credit > 0 ? number_format($credit, 2) : '---' }}
                                </td>
                                <td class="px-6 py-4 text-center font-mono font-bold text-rose-600">
                                    {{ $debt > 0 ? number_format($debt, 2) : '---' }}
                                </td>
                            </tr>
                        @endforeach
                        <tr class="bg-gray-50 dark:bg-gray-800 font-black">
                            <td class="px-6 py-4 border-t" colspan="3">إجمالي الأرصدة المتراكمة</td>
                            <td class="px-6 py-4 text-center border-t text-emerald-700 text-lg">{{ number_format($totalCredit, 2) }}</td>
                            <td class="px-6 py-4 text-center border-t text-rose-700 text-lg">{{ number_format($totalDebt, 2) }}</td>
                        </tr>
                        <tr class="bg-white dark:bg-gray-900">
                            <td class="px-6 py-6 border-t" colspan="5">
                                <div class="flex items-center justify-between bg-slate-900 text-white p-4 rounded-xl shadow-lg">
                                    <span class="text-sm font-medium opacity-80">صافي المديونية النهائية:</span>
                                    @php $net = $totalDebt - $totalCredit; @endphp
                                    <div class="flex items-center gap-3">
                                        <span class="text-2xl font-black tabular-nums">
                                            {{ number_format(abs($net), 2) }}
                                        </span>
                                        <span @class([
                                            'px-3 py-1 rounded-md text-[10px] uppercase font-bold',
                                            'bg-rose-500' => $net >= 0,
                                            'bg-emerald-500' => $net < 0,
                                        ])>
                                            {{ $net >= 0 ? 'مطلوب تحصيله من العملاء' : 'فائض لصالح العملاء' }}
                                        </span>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    </tbody>                    
                </table>
            </div>
    </div>
    <x-print-button/>
</x-filament-panels::page>