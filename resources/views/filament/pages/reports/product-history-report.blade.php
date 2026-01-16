<x-filament-panels::page>
    <x-filament::section class="mb-4 no-print shadow-sm border-slate-200">
        {{ $this->form }}
    </x-filament::section>

    @if ($reportData->isNotEmpty())
        <div id="report-content" class="space-y-6 print:m-0">
            <x-report-header :label="$this->getNavigationLabel()" />

            <div class="overflow-hidden bg-white border shadow-sm rounded-xl print:border-slate-800">
                <div class="p-4 bg-slate-50/50 border-b flex justify-between items-center">
                    <div>
                        <h2 class="text-lg font-bold text-slate-800 italic">سجل حركات المخزون</h2>
                        <p class="text-xs text-gray-500">حركة الوارد والمنصرف والتعديلات</p>
                    </div>
                    <span class="text-xs font-mono bg-slate-200 px-2 py-1 rounded">{{ $reportData->count() }} حركة</span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-right text-gray-600 border-collapse">
                        <thead class="text-xs text-white uppercase bg-slate-800">
                            <tr>
                                <th class="px-6 py-4 font-bold border-l border-slate-700">التاريخ</th>
                                <th class="px-6 py-4 font-bold border-l border-slate-700">الفرع</th>
                                <th class="px-6 py-4 font-bold border-l border-slate-700">المنتج</th>
                                <th class="px-6 py-4 font-bold border-l border-slate-700">نوع الحركة</th>
                                <th class="px-6 py-4 font-bold border-l border-slate-700 text-center">الكمية</th>
                                <th class="px-6 py-4 font-bold border-l border-slate-700 text-center">الرصيد بعد</th>
                                <th class="px-6 py-4 font-bold border-l border-slate-700">ملاحظات</th>
                                <th class="px-6 py-4 font-bold bg-slate-900">المستخدم</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-slate-200 tabular-nums">
                            @foreach ($reportData as $row)
                                <tr class="hover:bg-slate-50 transition-colors bg-white">
                                    <td class="px-6 py-4 font-medium whitespace-nowrap border-l border-slate-100">
                                        {{ $row->created_at->format('Y-m-d H:i') }}
                                        <div class="text-xs text-slate-400">{{ $row->created_at->diffForHumans() }}</div>
                                    </td>
                                    <td class="px-6 py-4 border-l border-slate-50">
                                        {{ $row->branch?->name }}
                                    </td>
                                    <td class="px-6 py-4 border-l border-slate-50 font-bold text-slate-800">
                                        {{ $row->product?->name }}
                                    </td>
                                    <td class="px-6 py-4 border-l border-slate-50">
                                        <x-filament::badge :color="$row->type->getColor()" :icon="$row->type->getIcon()">
                                            {{ $row->type->getLabel() }}
                                        </x-filament::badge>
                                    </td>
                                    <td
                                        class="px-6 py-4 text-center font-bold border-l border-slate-50 {{ $row->quantity_change > 0 ? 'text-success-600' : 'text-danger-600' }}">
                                        {{ $row->quantity_change > 0 ? '+' : '' }}{{ number_format($row->quantity_change, 2) }}
                                    </td>
                                    <td class="px-6 py-4 text-center font-black border-l border-slate-50 bg-slate-50/50">
                                        {{ number_format($row->new_quantity, 2) }}
                                    </td>
                                    <td class="px-6 py-4 text-xs border-l border-slate-50 max-w-xs truncate"
                                        title="{{ $row->notes }}">
                                        {{ $row->notes ?? '-' }}
                                    </td>
                                    <td class="px-6 py-4 text-xs bg-slate-50/30">
                                        {{ $row->user?->name }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <x-print-button />
    @else
        <div class="p-20 text-center bg-white border-2 border-dashed rounded-xl">
            <x-filament::icon icon="heroicon-o-clipboard-document-list" class="mx-auto w-12 h-12 text-gray-300 mb-4" />
            <h3 class="text-gray-400 font-bold text-xl">لا توجد حركات مخزنية مطابقة</h3>
        </div>
    @endif
</x-filament-panels::page>