<x-filament-panels::page>
    <div class="no-print">
        <x-filament::section class="mb-6 border-slate-200 shadow-sm">
            {{ $this->form }}
        </x-filament::section>
    </div>

    <div id="report-content" class="p-0 bg-white print:p-0">

        <x-report-header label="كشف تفاصيل منصرفات التخليص" />

        {{-- ملخص الحالة المالية --}}
        <div
            class="grid grid-cols-1 border-2 border-slate-950 mb-6 rounded-sm overflow-hidden shadow-sm print:shadow-none">
            <div class="flex flex-col items-center justify-center p-4 bg-slate-50">
                <span class="text-[10px] font-bold text-slate-500 uppercase mb-1">إجمالي المنصرفات | Total
                    Expenses</span>
                <span class="text-2xl font-black tabular-nums text-rose-700">{{ number_format($totalAmount, 2) }}</span>
            </div>
        </div>

        {{-- جدول البيانات --}}
        <div class="overflow-x-auto border-t-2 border-slate-950 shadow-sm">
            <table class="w-full text-center border-collapse text-[11px] print:text-[10px]">
                <thead>
                    <tr class="bg-slate-900 text-white uppercase tracking-widest">
                        <th class="p-3 border border-slate-700 text-xs w-12">#</th>
                        <th class="p-3 border border-slate-700 text-xs">رقم الشاحنة / اللوحة</th>
                        <th class="p-3 border border-slate-700 text-xs">نوع المنصرف</th>
                        <th class="p-3 border border-slate-700 text-xs">التاريخ</th>
                        <th class="p-3 border border-slate-700 text-xs">المنصرف بواسطة</th>
                        <th class="p-3 border border-slate-700 text-xs w-64">التفاصيل (بيان)</th>
                        <th class="p-3 border border-slate-700 bg-rose-900 text-xs text-white">المبلغ (إجمالي)</th>
                    </tr>
                </thead>
                <tbody class="tabular-nums">
                    @forelse ($expenses as $i => $expense)
                        <tr class="hover:bg-slate-50 transition-colors border-b border-slate-200">
                            <td class="p-2 border-x border-slate-200 bg-slate-50/50 text-slate-500 font-bold">{{ $i + 1 }}
                            </td>
                            <td class="p-2 border-x border-slate-200 font-bold text-slate-800">
                                {{ $expense->truck?->code ?? $expense->truck_id }}<br>
                                <span
                                    class="text-[10px] text-slate-500 font-normal">{{ $expense->truck?->car_number }}</span>
                            </td>
                            <td class="p-2 border-x border-slate-200 font-medium text-slate-700">
                                {{ $expense->type?->label ?? 'غير محدد' }}
                            </td>
                            <td class="p-2 border-x border-slate-200 text-blue-700 font-bold">
                                {{ $expense->created_at?->format('Y-m-d') }}
                            </td>
                            <td class="p-2 border-x border-slate-200 text-slate-600">
                                {{ $expense->representative?->name ?? '-' }}
                            </td>
                            <td class="p-2 border-x border-slate-200 text-right pr-4 italic text-slate-600">
                                {{ $expense->note ?? '-' }}
                            </td>
                            <td class="p-2 border-x border-slate-300 bg-amber-50/30 font-black text-rose-700">
                                {{ number_format($expense->total_amount, 2) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="p-10 text-center text-slate-400 bg-slate-50 italic">
                                لاتوجد منصرفات لهذا النطاق من البحث.
                            </td>
                        </tr>
                    @endforelse

                    <tr class="bg-slate-900 text-white font-black border-t-2 border-slate-950">
                        <td colspan="6" class="p-4 text-left uppercase tracking-widest">الإجمالي النهائي | Grand Total
                        </td>
                        <td class="p-4 bg-rose-900 tabular-nums border-l border-slate-700 shadow-inner">
                            {{ number_format($totalAmount, 2) }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div
            class="mt-8 flex justify-between items-center text-[8px] text-slate-400 hidden print:flex uppercase tracking-tighter italic">
            <span>تاريخ التقرير: {{ now()->format('Y-m-d H:i') }}</span>
            <span>بواسطة: {{ auth()->user()->name ?? '-' }}</span>
        </div>

        <x-print-button />
    </div>

    <style>
        #report-content {
            font-family: 'FlatJooza', 'Inter', sans-serif;
        }

        @media print {
            @page {
                size: A4 landscape;
                margin: 8mm;
            }

            .no-print {
                display: none !important;
            }

            body {
                background: white !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            table {
                border: 1px solid #000 !important;
            }

            th,
            td {
                border: 1px solid #ccc !important;
            }
        }

        .tabular-nums {
            font-variant-numeric: tabular-nums;
        }
    </style>
</x-filament-panels::page>