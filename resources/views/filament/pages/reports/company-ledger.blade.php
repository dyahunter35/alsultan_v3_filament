<x-filament-panels::page>
    <x-filament::section class="mb-4 no-print">
        {{ $this->form }}
    </x-filament::section>

    @if ($companyId)
        <div id='report-content'>
            <x-report-header label="كشف حساب شركة: " :value="$_company?->name" />

            {{-- 1. جدول بيان شحنات الفترة (تفصيلي) --}}
            <div class="mb-8">
                <h3 class="text-lg font-bold mb-3 border-b-2 border-slate-800 pb-1">أولاً: بيان فواتير الشحن (الطلبيات)</h3>
                <div class="overflow-x-auto">
                    <table class="w-full text-[11px] text-center border-collapse border border-slate-800">
                        <thead class="bg-slate-100 font-bold">
                            <tr>
                                <th class="p-2 border border-slate-800 w-24">التاريخ</th>
                                <th class="p-2 border border-slate-800 w-8">#</th>
                                <th class="p-2 border border-slate-800 text-right">الصنف</th>
                                <th class="p-2 border border-slate-800">المقاس</th>
                                <th class="p-2 border border-slate-800">و.الوحدة</th>
                                <th class="p-2 border border-slate-800">الطرد</th>
                                <th class="p-2 border border-slate-800">العدد</th>
                                <th class="p-2 border border-slate-800 font-bold text-blue-800">الطن</th>
                                <th class="p-2 border border-slate-800">س.الوحدة</th>
                                <th class="p-2 border border-slate-800">س.الطن</th>
                                <th class="p-2 border border-slate-800 bg-slate-800 text-white">إجمالي الفاتورة</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($groups as $group)
                                @foreach ($group['cargos'] as $index => $cargo)
                                    <tr class="border-b border-slate-300">
                                        @if ($index === 0)
                                            <td class="border border-slate-800 font-bold bg-white" rowspan="{{ count($group['cargos']) }}">
                                                {{ $group['date'] }} <br>
                                                <span class="text-[9px] text-blue-600 font-bold">شحنة #{{ $group['truck_id'] }}</span>
                                            </td>
                                        @endif
                                        <td class="p-1 border border-slate-300 text-slate-400">{{ $index + 1 }}</td>
                                        <td class="p-1 border border-slate-300 text-right font-medium">{{ $cargo->product->name ?? '-' }}</td>
                                        <td class="p-1 border border-slate-300">{{ $cargo->size }}</td>
                                        <td class="p-1 border border-slate-300">{{ number_format($cargo->weight, 2) }}</td>
                                        <td class="p-1 border border-slate-300">{{ number_format($cargo->quantity, 2) }}</td>
                                        <td class="p-1 border border-slate-300">{{ number_format($cargo->unit_quantity, 2) }}</td>
                                        <td class="p-1 border border-slate-300 font-bold text-blue-700">{{ number_format($cargo->ton_weight, 3) }}</td>
                                        <td class="p-1 border border-slate-300">{{ number_format($cargo->unit_price, 2) }}</td>
                                        <td class="p-1 border border-slate-300">{{ number_format($cargo->ton_price, 2) }}</td>
                                        @if ($index === 0)
                                            <td class="border border-slate-800 font-black bg-slate-50 text-sm tabular-nums" rowspan="{{ count($group['cargos']) }}">
                                                {{ number_format($group['total_invoice'], 2) }}
                                            </td>
                                        @endif
                                    </tr>
                                @endforeach
                            @endforeach
                        </tbody>
                        <tfoot class="bg-slate-800 text-white font-bold">
                            <tr>
                                <td colspan="10" class="p-2 text-left px-4 italic">إجمالي قيمة كافة فواتير الفترة:</td>
                                <td class="p-2 bg-slate-900 text-sm">{{ number_format($summary['total_debit'], 2) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            {{-- 2. جدول كشف الحساب التراكمي (الزيادة والنقصان) --}}
            <div class="mt-10 no-break-inside">
                <h3 class="text-lg font-bold mb-3 border-r-4 border-emerald-600 pr-3">ثانياً: ملخص حركة كشف الحساب (مدين / دائن)</h3>
                <table class="w-full text-[13px] text-center border-collapse border border-slate-800">
                    <thead class="bg-slate-700 text-white font-bold">
                        <tr>
                            <th class="p-2 border border-slate-600 w-32">التاريخ</th>
                            <th class="p-2 border border-slate-600 text-right">البيان / المرجع</th>
                            <th class="p-2 border border-slate-600 w-36 bg-slate-600">مدين (فواتير +)</th>
                            <th class="p-2 border border-slate-600 w-36 bg-slate-600">دائن (سداد -)</th>
                            <th class="p-2 border border-slate-600 w-40 bg-slate-900 text-yellow-400 font-black">الرصيد التراكمي</th>
                        </tr>
                    </thead>
                    <tbody>
                        {{-- سطر الرصيد السابق --}}
                        <tr class="bg-blue-50 font-bold italic border-b border-slate-400">
                            <td class="p-2 border border-slate-300" colspan="2">رصيد افتتاحي مرحل من فترة سابقة</td>
                            <td class="p-2 border border-slate-300">-</td>
                            <td class="p-2 border border-slate-300">-</td>
                            <td class="p-2 border border-slate-300 font-black">{{ number_format($opening_balance, 2) }}</td>
                        </tr>

                        @foreach ($report_lines as $line)
                            <tr class="hover:bg-slate-50 border-b border-slate-200">
                                <td class="p-2 border border-slate-300 tabular-nums">{{ $line['date'] }}</td>
                                <td class="p-2 border border-slate-300 text-right font-medium">{{ $line['reference'] }}</td>
                                <td class="p-2 border border-slate-300 font-bold text-slate-700">
                                    {{ $line['debit'] > 0 ? number_format($line['debit'], 2) : '-' }}
                                </td>
                                <td class="p-2 border border-slate-300 font-bold text-red-600">
                                    {{ $line['credit'] > 0 ? number_format($line['credit'], 2) : '-' }}
                                </td>
                                <td class="p-2 border border-slate-300 font-black bg-slate-50/50 tabular-nums">
                                    {{ number_format($line['balance'], 2) }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-slate-100 font-black border-t-2 border-slate-800">
                        <tr>
                            <td colspan="2" class="p-3 text-left">الخلاصة الختامية للحساب:</td>
                            <td class="p-2 border border-slate-300 text-slate-800">{{ number_format($summary['total_debit'], 2) }}</td>
                            <td class="p-2 border border-slate-300 text-red-600">{{ number_format($summary['total_credit'], 2) }}</td>
                            <td class="p-2 bg-slate-800 text-white text-base">{{ number_format($summary['final_balance'], 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        {{-- زر الطباعة --}}
        <div class="fixed bottom-6 left-6 no-print">
            <x-print-button />
        </div>
    @endif
</x-filament-panels::page>