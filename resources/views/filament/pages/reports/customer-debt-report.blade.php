<x-filament-panels::page>
    {{-- قسم الفلترة --}}
    <x-filament::section class="mb-6 no-print border-slate-200 shadow-none bg-slate-50/50">
        <div class="flex flex-col gap-4 md:flex-row md:items-end">
            <div class="flex-1">
                {{ $this->form }}
            </div>
            <div class="flex items-center gap-2">
                <x-filament::button wire:click="updateBalances" color="gray" variant="outline" icon="heroicon-m-arrow-path">
                    تحديث البيانات
                </x-filament::button>
            </div>
        </div>
    </x-filament::section>

    <div id="report-content" class="space-y-4 print:m-0 bg-white p-2">
        {{-- الهيدر الموحد --}}
        <x-report-header :label="$this->getTitle()" />

        {{-- 1. جدول البيانات فقط --}}
        <div class="overflow-x-auto border border-slate-300 rounded-sm">
            <table class="w-full text-sm text-right border-collapse">
                <thead>
                    <tr class="bg-slate-100 border-b border-slate-300">
                        <th class="px-4 py-3 font-bold text-slate-700 border-l border-slate-200 w-10">#</th>
                        <th class="px-4 py-3 font-bold text-slate-700 border-l border-slate-200">اسم العميل</th>
                        <th class="px-4 py-3 font-bold text-slate-700 border-l border-slate-200">المنطقة</th>
                        <th class="px-4 py-3 font-bold text-emerald-800 border-l border-slate-200 bg-emerald-50/30">له (Credit)</th>
                        <th class="px-4 py-3 font-bold text-rose-800 bg-rose-50/30">عليه (Debt)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 bg-white">
                    @php $totalCredit = 0; $totalDebt = 0; @endphp
                    @foreach($customers as $index => $customer)
                        @php
                            $balance = $customer['balance'];
                            $credit = $balance < 0 ? abs($balance) : 0;
                            $debt = $balance > 0 ? $balance : 0;
                            $totalCredit += $credit; $totalDebt += $debt;
                        @endphp
                        <tr class="hover:bg-slate-50/50 transition-colors">
                            <td class="px-4 py-2 text-slate-500 border-l border-slate-100 text-center text-xs">{{ $loop->iteration }}</td>
                            <td class="px-4 py-2 font-semibold text-slate-900 border-l border-slate-100">{{ $customer['name'] }}</td>
                            <td class="px-4 py-2 text-slate-600 border-l border-slate-100">{{ $customer['region'] }}</td>
                            <td class="px-4 py-2 text-center font-mono font-bold text-emerald-700 border-l border-slate-100 tabular-nums">
                                {{ $credit > 0 ? number_format($credit, 2) : '-' }}
                            </td>
                            <td class="px-4 py-2 text-center font-mono font-bold text-rose-700 tabular-nums">
                                {{ $debt > 0 ? number_format($debt, 2) : '-' }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- 2. قسم ملخص الأرصدة (الـ div الخارجي) --}}
        <div class="mt-6 border-2 border-slate-800 rounded-sm overflow-hidden">
            {{-- عنوان الملخص --}}
            <div class="bg-slate-800 text-white px-4 py-1 text-xs font-bold uppercase tracking-widest">
                Financial Summary | ملخص مالي
            </div>
            
            <div class="grid grid-cols-3 md:grid-cols-3 divide-y md:divide-y-0 md:divide-x md:divide-x-reverse divide-slate-200 bg-slate-50">
                {{-- إجمالي له --}}
                <div class="p-4 flex flex-col items-center justify-center bg-white">
                    <span class="text-xs text-slate-500 font-bold mb-1">إجمالي المبالغ الدائنة (له)</span>
                    <span class="text-xl font-black text-emerald-700 tabular-nums">{{ number_format($totalCredit, 2) }}</span>
                </div>

                {{-- إجمالي عليه --}}
                <div class="p-4 flex flex-col items-center justify-center bg-white">
                    <span class="text-xs text-slate-500 font-bold mb-1">إجمالي المبالغ المدينة (عليه)</span>
                    <span class="text-xl font-black text-rose-700 tabular-nums">{{ number_format($totalDebt, 2) }}</span>
                </div>

                {{-- صافي المديونية --}}
                @php $net = $totalDebt - $totalCredit; @endphp
                <div @class([
                    'p-4 flex flex-col items-center justify-center border-t-4 md:border-t-0 md:border-r-4 shadow-inner',
                    'border-rose-600 bg-rose-50/30' => $net >= 0,
                    'border-emerald-600 bg-emerald-50/30' => $net < 0,
                ])>
                    <span class="text-xs font-bold mb-1 text-slate-700">صافي الحالة النهائية</span>
                    <div class="flex items-baseline gap-2">
                        <span class="text-2xl font-black tabular-nums text-slate-900">
                            {{ number_format(abs($net), 2) }}
                        </span>
                    </div>
                    <span @class([
                        'text-[10px] font-bold px-2 py-0.5 rounded-full mt-1 uppercase',
                        'bg-rose-600 text-white' => $net >= 0,
                        'bg-emerald-600 text-white' => $net < 0,
                    ])>
                        {{ $net >= 0 ? 'مطلوب تحصيله' : 'فائض للعملاء' }}
                    </span>
                </div>
            </div>
        </div>

        {{-- تذييل الصفحة للطباعة --}}
        <div class="mt-12 hidden print:grid grid-cols-2 text-[10px] text-slate-500 border-t border-slate-200 pt-4">
            <div>تاريخ التقرير: {{ now()->format('Y-m-d H:i') }}</div>
            <div class="text-left italic underline">توقيع المراجعة والاعتماد: ..........................</div>
        </div>
    </div>

    <div class="mt-6 no-print">
        <x-print-button/>
    </div>
</x-filament-panels::page>