<x-filament-panels::page>
    <x-filament::section class="mb-4 no-print shadow-sm border-slate-200">
        <div class="flex flex-col gap-4 md:flex-row md:items-end">
            <div class="flex-1">
                {{ $this->form }}
            </div>
            <x-filament::button wire:click="updateQty" color="primary" icon="heroicon-m-arrow-path" class="shadow-sm">
                تحديث مزامنة الكميات
            </x-filament::button>
        </div>
    </x-filament::section>

    <div id="report-content" class="space-y-6">
        <x-report-header :label="$this->getTitle()" />
        
        <div class="flex justify-between items-center py-4 border-b">
            <p class="text-primary-600 font-bold text-lg">فرع: {{ $branch->name }}</p>
            <span class="text-gray-400 text-xs italic">عدد المنتجات: {{ $reportData->count() }}</span>
        </div>

        <div class="overflow-hidden bg-white border rounded-xl shadow-lg border-gray-200">
            <table class="w-full text-sm text-right">
                <thead class="bg-slate-800 text-white">
                    <tr>
                        <th class="px-6 py-4">اسم المنتج</th>
                        <th class="px-6 py-4 text-center bg-slate-700/50">الرصيد الافتتاحي</th>
                        <th class="px-6 py-4 text-center">التوريدات (+)</th>
                        <th class="px-6 py-4 text-center">المبيعات (-)</th>
                        <th class="px-6 py-4 text-center bg-slate-900 text-yellow-400">الرصيد الحالي</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($reportData as $row)
                        <tr class="hover:bg-blue-50/50 transition-colors duration-150">
                            <td class="px-6 py-4 font-bold text-gray-900">{{ $row->name }}</td>
                            <td class="px-6 py-4 text-center text-gray-600 italic">{{ number_format($row->initial, 2) }}</td>
                            <td class="px-6 py-4 text-center text-green-600 font-bold">
                                +{{ number_format($row->increase, 2) }}
                            </td>
                            <td class="px-6 py-4 text-center text-red-600 font-bold">
                                -{{ number_format($row->decrease, 2) }}
                            </td>
                            <td class="px-6 py-4 text-center font-black bg-slate-50/80 text-slate-900">
                                {{ number_format($row->current_balance, 2) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="p-12 text-center">
                                <div class="flex flex-col items-center">
                                    <x-filament::icon icon="heroicon-o-archive-box-x-mark" class="w-12 h-12 text-gray-300 mb-2"/>
                                    <p class="text-gray-400 font-medium">لا توجد بيانات تطابق الفلاتر المختارة</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <x-print-button/>
    </div>
</x-filament-panels::page>