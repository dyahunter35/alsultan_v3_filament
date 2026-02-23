<x-filament-panels::page>
    <style>
        .ledger-table th { background-color: #1e293b; color: white; border: 1px solid #475569; padding: 6px; font-size: 11px; }
        .ledger-table td { border: 1px solid #cbd5e1; padding: 4px; font-size: 11px; vertical-align: middle; text-align: center; }
        .bg-side-blue { background-color: #f8fafc; font-weight: bold; color: #1e293b; }
        .row-group-total { background-color: #1e293b; color: white; font-weight: bold; }
        .text-right-custom { text-align: right !important; padding-right: 10px !important; }
        .payment-row { background-color: transparent; } /* لون أخضر خفيف للسداد */
    </style>

    <x-filament::section class="no-print mb-4">
        {{ $this->form }}
    </x-filament::section>

    @if($companyId)
    <div id="report-content">
        <x-report-header label="كشف حساب شركة: " :value="$_company?->name" />

        <div class="mt-12">
            <h3 class="font-bold text-sm mb-2 border-r-4 border-emerald-600 pr-2">اولا: حركة كشف الحساب التراكمي</h3>
            <table class="w-full ledger-table border-collapse" dir="rtl">
                <thead>
                    <tr class="bg-slate-800 text-white">
                        <th class="p-2 border border-slate-600">#</th>
                        <th class="p-2 border border-slate-600">التاريخ</th>
                        <th class="p-2 border border-slate-600 text-right px-4">البيان / المرجع</th>
                        <th class="p-2 border border-slate-600 w-32 bg-slate-700">مدين (+) شحن</th>
                        <th class="p-2 border border-slate-600 w-32 bg-slate-700">دائن (-) سداد</th>
                        <th class="p-2 border border-slate-600 w-40 bg-slate-900 text-yellow-400">الرصيد المتبقي</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="bg-blue-50 font-bold italic text-slate-900">
                        <td class="p-2 border border-slate-300">{{ $start ?? '-' }}</td>
                        <td class="p-2 text-right px-4">رصيد مدور من فترة سابقة</td>
                        <td>-</td>
                        <td>-</td>
                        <td class="font-black tabular-nums">{{ number_format($opening_balance, 2) }}</td>
                    </tr>
                    @foreach ($report_lines as $index => $line)
                        <tr class="hover:bg-slate-50">
                            <td>{{ ++$index }}</td>
                            <td>{{ $line['date'] }}</td>
                            <td class="text-right px-4 font-medium">{{ $line['ref'] }}</td>
                            <td class="font-bold tabular-nums">{{ $line['debit'] > 0 ? number_format($line['debit'], 2) : '-' }}</td>
                            <td class="font-bold text-red-600 tabular-nums">{{ $line['credit'] > 0 ? number_format($line['credit'], 2) : '-' }}</td>
                            <td class="font-black bg-slate-50 tabular-nums">{{ number_format($line['balance'], 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot class="row-group-total">
                    <tr>
                        <td colspan="3" class="text-left px-4 py-3">الإجماليات الختامية:</td>
                        <td class="tabular-nums">{{ number_format($summary['total_debit'], 2) }}</td>
                        <td class="text-red-400 tabular-nums">{{ number_format($summary['total_credit'], 2) }}</td>
                        <td class="bg-yellow-500 text-slate-900 text-lg tabular-nums">{{ number_format($summary['final_balance'], 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
        {{-- أولاً: جدول الفواتير والسداد المدمج --}}
        <div class="mb-10">
            <h3 class="font-bold text-sm mb-2 border-r-4 border-slate-800 pr-2">ثانيا: بيان حركة الشحن والسداد (الطلبيات)</h3>
            <table class="w-full ledger-table border-collapse" dir="rtl">
                <thead>
                    <tr>
                        <th rowspan="3" class="bg-slate-200 border-slate-900 w-32 text-slate-900">التاريخ / البيان</th>
                        <th colspan="10" class="border-slate-900 italic py-2">بيان الفاتورة (الطلبية)</th>
                        <th colspan="2" rowspan="2" class="border-slate-900 italic py-2 bg-green-900">السداد</th>
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
                        <th class="bg-green-800 w-24">البيان</th>
                        <th class="bg-green-800 w-24">المبلغ</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($combined_records as $record)
                        @if($record['type'] === 'truck')
                            {{-- صفوف الشحنة --}}
                            @foreach($record['cargos'] as $index => $cargo)
                                <tr>
                                    @if($index === 0)
                                    <td id="row-{{ $record['id'] }}" rowspan="{{ count($record['cargos']) + 1 }}" class="bg-white font-bold border-r-2 border-slate-900">
                                        <div class="mb-1">{{ $record['date'] }}</div>
                                        <a href="{{ \App\Filament\Resources\Trucks\TruckResource::getUrl('edit', ['record' => $record['id']]) }}" 
   target="_blank" 
   class="text-blue-600 hover:underline flex items-center justify-center gap-1">
    <x-heroicon-o-eye class="w-4 h-4"/>
                                            <span class="bg-blue-600 text-white px-2 py-0.5 rounded text-[9px]" wire:click.prevent="viewTruck({{ $record['id'] }})" style="cursor: pointer;">شحنة #{{ $record['id'] }}</span>

</a>
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
                                    <td class="bg-slate-50 text-slate-300">-</td>
                                    <td class="bg-slate-50 text-slate-300">-</td>
                                </tr>
                            @endforeach
                            {{-- سطر إجمالي الشحنة --}}
                            <tr class="row-group-total">
                                <td colspan="10" class="text-right px-4 italic text-xs">إجمالي الشحنة #{{ $record['id'] }}</td>
                                <td colspan="2" class="bg-slate-900 text-white text-left px-4">
                                    {{ number_format($record['total'], 2) }}
                                </td>
                            </tr>
                        @else
                            {{-- صف السداد (Currency Transaction) --}}
                            <!-- <tr class="payment-row">
                                <td class="bg-white font-bold border-r-2 border-slate-900">
                                    <div class="mb-1">{{ $record['date'] }}</div>
                                    <span class="bg-emerald-600 text-white px-2 py-0.5 rounded text-[9px]">سداد نقدي</span>
                                </td>
                                <td colspan="10" class="text-right px-4 italic text-slate-400">
                                    {{ $record['description'] }}
                                </td>
                                <td class=" font-bold text-emerald-700 text-[9px]">وصل #{{ $record['id'] }}</td>
                                <td class="bg-green-200 font-bold text-emerald-900">{{ number_format($record['amount'], 2) }}</td>
                            </tr> -->
                        @endif
                        <tr class="h-1 bg-slate-100"><td colspan="13"></td></tr>
                    @endforeach
                </tbody>
            </table>
            <x-print-button/>
        </div>

        {{-- ثانياً: كشف الحساب التراكمي --}}
        
    </div>
    @endif
</x-filament-panels::page>