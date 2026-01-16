<x-filament-panels::page>
    <x-filament::section class="mb-4 no-print shadow-sm border-slate-200">
        <div class="flex flex-col gap-4 md:flex-row md:items-end">
            <div class="flex-1">
                {{ $this->form }}
            </div>
            <x-filament::button keyBindings="command+h" wire:click="updateQty" color="gray" icon="heroicon-m-arrow-path">
                تحديث الكميات
            </x-filament::button>
        </div>
    </x-filament::section>

    @if ($reportData->isNotEmpty())
        <div id="report-content" class="space-y-6 print:m-0">
            <x-report-header :label="$this->getNavigationLabel()" />

            <div class="overflow-hidden bg-white border shadow-sm rounded-xl print:border-slate-800">
                <div class="p-4 bg-slate-50/50 border-b flex justify-between items-center">
                    <div>
                        <h2 class="text-lg font-bold text-slate-800 italic">تقرير الجرد التراكمي الشامل</h2>
                        <p class="text-xs text-gray-500">مخازن الفروع + البضاعة العالقة (ميناء وحظيرة)</p>
                    </div>
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
                                <th class="px-6 py-4 font-bold text-center border-l border-slate-700 bg-slate-700">إجمالي الفروع</th>
                                <th class="px-6 py-4 font-bold text-center border-l border-slate-700 bg-orange-700/50">في الحظيرة</th>
                                <th class="px-6 py-4 font-bold text-center border-l border-slate-700 bg-blue-700/50">في الميناء</th>
                                <th class="px-6 py-4 font-black text-center bg-slate-900 text-green-400">الإجمالي العام</th>
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
                                            {{ number_format($row->balances[$branch->id], 2) }}
                                        </td>
                                    @endforeach
                                    
                                    {{-- إجمالي الفروع --}}
                                    <td class="px-6 py-4 text-center font-bold bg-slate-50 border-l">
                                        {{ number_format($row->branches_total, 2) }}
                                    </td>

                                    {{-- كميات الشاحنات --}}
                                    <td class="px-6 py-4 text-center border-l bg-orange-50/50 text-orange-800 font-semibold">
                                        {{ number_format($row->barn_qty, 2) }}
                                    </td>
                                    <td class="px-6 py-4 text-center border-l bg-blue-50/50 text-blue-800 font-semibold">
                                        {{ number_format($row->port_qty, 2) }}
                                    </td>

                                    {{-- المجموع الكلي الشامل --}}
                                    <td class="px-6 py-4 font-black text-center text-slate-900 bg-green-50/30">
                                        {{ number_format($row->grand_total, 2) }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <x-print-button/>
    @else
        <div class="p-20 text-center bg-white border-2 border-dashed rounded-xl">
            <x-filament::icon icon="heroicon-o-funnel" class="mx-auto w-12 h-12 text-gray-300 mb-4" />
            <h3 class="text-gray-400 font-bold text-xl">لا توجد بيانات مطابقة لهذه الفلاتر</h3>
        </div>
    @endif
</x-filament-panels::page>