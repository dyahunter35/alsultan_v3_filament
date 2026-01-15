<x-filament-panels::page>
    {{-- قسم الفلترة --}}
    <x-filament::section class="mb-4 no-print shadow-sm border-slate-200">
        {{ $this->form }}
    </x-filament::section>

    @if ($expenses->count())
    <div id="report-content" class="space-y-6">
        <x-report-header :label="$this->getTitle()" />

        {{-- ملخص المبالغ --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-white p-4 border rounded-xl shadow-sm flex flex-col items-center justify-center">
                <span class="text-slate-400 text-sm font-bold">إجمالي المصروفات</span>
                <span class="text-2xl font-black text-slate-800 tabular-nums">
                    {{ number_format($expenses->sum('total_amount'), 2) }}
                </span>
            </div>
            <div class="bg-white p-4 border rounded-xl shadow-sm flex flex-col items-center justify-center">
                <span class="text-slate-400 text-sm font-bold">المبلغ المدفوع</span>
                <span class="text-2xl font-black text-green-700 tabular-nums">
                    {{ number_format($expenses->sum('total_amount') - $expenses->sum('remaining_amount'), 2) }}
                </span>
            </div>
            <div class="bg-white p-4 border rounded-xl shadow-sm flex flex-col items-center justify-center border-red-100 bg-red-50/10">
                <span class="text-red-400 text-sm font-bold">المبالغ المستحقة (الباقي)</span>
                <span class="text-2xl font-black text-red-700 tabular-nums">
                    {{ number_format($expenses->sum('remaining_amount'), 2) }}
                </span>
            </div>
        </div>

        {{-- جدول البيانات --}}
        <div class="bg-white border shadow-sm rounded-xl overflow-hidden print:border-slate-800">
            <table class="w-full text-right border-collapse text-sm print:text-[10px]">
                <thead>
                    <tr class="bg-slate-800 text-white font-bold">
                        <th class="p-3 border border-slate-700 w-24">التاريخ</th>
                        <th class="p-3 border border-slate-700">النوع / الفرع</th>
                        <th class="p-3 border border-slate-700">المستفيد</th>
                        <th class="p-3 border border-slate-700">البيان / ملاحظات</th>
                        <th class="p-3 border border-slate-700 w-20 text-center">الكمية</th>
                        <th class="p-3 border border-slate-700 w-24 text-center text-green-300">الإجمالي</th>
                        <th class="p-3 border border-slate-700 w-24 text-center text-red-300">المتبقي</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 tabular-nums">
                    @foreach ($expenses as $row)
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="p-2 border border-slate-200 text-slate-500">{{ $row->created_at?->format('Y-m-d') }}</td>
                        <td class="p-2 border border-slate-200">
                            <div class="font-bold text-slate-700">{{ $row->type?->label ?? 'مصروف عام' }}</div>
                            <div class="text-[10px] text-slate-400">{{ $row->branch?->name }}</div>
                        </td>
                        <td class="p-2 border border-slate-200">
                            {{ $row->beneficiary?->name ?? '—' }}
                            <div class="text-[9px] text-slate-400 italic">بواسطة: {{ $row->representative?->name }}</div>
                        </td>
                        <td class="p-2 border border-slate-200 text-xs leading-snug">
                            {{ $row->notes ?? '—' }}
                            @if($row->payment_reference)
                                <div class="text-[9px] text-blue-500 font-bold">إيصال: {{ $row->payment_reference }}</div>
                            @endif
                        </td>
                        <td class="p-2 border border-slate-200 text-center font-medium">{{ number_format($row->amount, 1) }}</td>
                        <td class="p-2 border border-slate-200 text-center font-black text-slate-800 bg-slate-50/30">
                            {{ number_format($row->total_amount, 2) }}
                        </td>
                        <td class="p-2 border border-slate-200 text-center font-bold text-red-600 bg-red-50/10">
                            {{ $row->remaining_amount > 0 ? number_format($row->remaining_amount, 2) : '—' }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-slate-100 font-black border-t-2 border-slate-800">
                    <tr>
                        <td colspan="5" class="p-3 text-left px-6">الإجمالي الكلي</td>
                        <td class="p-3 text-center text-slate-900 bg-slate-200">{{ number_format($expenses->sum('total_amount'), 2) }}</td>
                        <td class="p-3 text-center text-red-700 bg-red-100">{{ number_format($expenses->sum('remaining_amount'), 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
    <x-print-button />
    @else
        <div class="p-20 text-center bg-white border-2 border-dashed rounded-xl">
            <x-filament::icon icon="heroicon-o-document-magnifying-glass" class="w-12 h-12 mx-auto mb-4 text-gray-300" />
            <p class="text-gray-400 font-bold">لا توجد مصروفات مسجلة لهذه الفترة</p>
        </div>
    @endif
</x-filament-panels::page>