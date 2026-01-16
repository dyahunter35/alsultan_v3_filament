<x-filament-panels::page>
    {{-- قسم الفلاتر --}}
    <x-filament::section class="mb-4 no-print shadow-sm border-slate-200">
        <div class="flex flex-col gap-4 md:flex-row md:items-end">
            <div class="flex-1">
                {{ $this->form }}
            </div>
            <x-filament::button wire:click="loadData" color="gray" icon="heroicon-m-arrow-path">
                تحديث العرض
            </x-filament::button>
        </div>
    </x-filament::section>

    @if ($reportData->isNotEmpty())
        <div id="report-content" class="space-y-6 print:m-0">
            <x-report-header :label="$this->getNavigationLabel()" />

            <div class="overflow-hidden bg-white border shadow-sm rounded-xl print:border-slate-800">
                <div class="p-4 bg-slate-50/50 border-b flex justify-between items-center">
                    <h2 class="text-lg font-bold text-slate-800">جرد المنتجات التراكمي لكل الفروع</h2>
                    <span class="text-xs font-mono">تاريخ التقرير: {{ now()->format('Y-m-d H:i') }}</span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-right text-gray-600 border-collapse">
                        <thead class="text-xs text-white uppercase bg-slate-800">
                            <tr>
                                <th class="px-6 py-4 font-bold border-l border-slate-700">المنتج</th>
                                @foreach ($branches as $branch)
                                    <th class="px-6 py-4 font-bold text-center border-l border-slate-700">
                                        {{ $branch->name }}
                                    </th>
                                @endforeach
                                <th class="px-6 py-4 font-black text-center bg-slate-900 text-green-400">
                                    المجموع الكلي
                                </th>
                                {{-- الأعمدة الجديدة --}}
        <th class="px-6 py-4 font-bold text-center border-l border-slate-700 bg-orange-900/40">في الحظيرة (Barn)</th>
        <th class="px-6 py-4 font-bold text-center border-l border-slate-700 bg-blue-900/40">في الميناء (Port)</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-slate-200 tabular-nums">
                            @foreach ($reportData as $row)
                                <tr class="hover:bg-slate-50 transition-colors bg-white">
                                    <td class="px-6 py-4 font-bold text-slate-900 border-l border-slate-100">
                                        {{ $row->name }}
                                    </td>
                                    @foreach ($branches as $branch)
                                        <td class="px-6 py-4 text-center border-l border-slate-50">
                                            {{ number_format($row->balances[$branch->id]) }}
                                        </td>
                                    @endforeach
                                    <td class="px-6 py-4 font-black text-center text-slate-900 bg-slate-50/50 border-r border-slate-200">
                                        {{ number_format($row->row_total) }}
                                    </td>

                                    <td class="px-6 py-4 text-center border-l border-slate-50 bg-orange-50 font-semibold text-orange-700">
            {{ number_format($row->barn_qty) }}
        </td>
        <td class="px-6 py-4 text-center border-l border-slate-50 bg-blue-50 font-semibold text-blue-700">
            {{ number_format($row->port_qty) }}
        </td>
                                </tr>
                            @endforeach
                        </tbody>

                        {{-- <tfoot class="bg-slate-800 text-white font-bold">
                            <tr>
                                <td class="px-6 py-3 border-l border-slate-700">إجمالي المخزون</td>
                                @foreach ($branches as $branch)
                                    <td class="px-6 py-3 text-center border-l border-slate-700">
                                        {{ number_format($footerTotals[$branch->id]) }}
                                    </td>
                                @endforeach
                                <td class="px-6 py-3 text-center bg-slate-900 text-green-400 text-lg">
                                    {{ number_format($footerTotals['grand_total']) }}
                                </td>
                            </tr>
                        </tfoot> --}}
                    </table>
                </div>
            </div>

            <div class="hidden print:flex justify-between px-10 mt-12 text-xs italic text-slate-500">
                <p>توقيع أمين المخزن: ............................</p>
                <p>اعتماد الإدارة: ............................</p>
            </div>
        </div>
        <x-print-button />
    @else
        <div class="p-20 text-center bg-white border-2 border-dashed rounded-xl">
            <h3 class="text-gray-400 font-bold">لا توجد بيانات متاحة حالياً</h3>
        </div>
    @endif

    <style>
        #report-content { font-family: 'FlatJooza', sans-serif; }
        @media print {
            @page { size: A4 landscape; margin: 10mm; }
            .no-print { display: none !important; }
        }
    </style>
</x-filament-panels::page>