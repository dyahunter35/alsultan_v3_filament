<x-filament-panels::page>
    <x-filament::section class="no-print mb-4 shadow-sm">
        {{ $this->form }}
    </x-filament::section>

    <div id="report-content" class="space-y-6">
        {{-- الهيدر الموحد --}}
        <x-report-header :label="$this->getTitle()"/>
        <div class="flex flex-col items-center py-4 border-b">
            <!-- <h1 class="text-2xl font-bold">{{ __('app.name') }}</h1> -->
            <p class="text-primary-600 font-bold">تقرير مخزن: {{ $branch->name }}</p>
        </div>

        <div class="overflow-hidden bg-white border rounded-xl shadow-lg">
            <table class="w-full text-sm text-right">
                <thead class="bg-slate-800 text-white">
                    <tr>
                        <th class="px-6 py-4">المنتج</th>
                        <th class="px-6 py-4 text-center">أول كمية</th>
                        <th class="px-6 py-4 text-center">التوريدات (+)</th>
                        <th class="px-6 py-4 text-center">المبيعات (-)</th>
                        <th class="px-6 py-4 text-center bg-slate-900 text-yellow-400">الرصيد الصافي</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse ($reportData as $row)  
                        @php if (!$this->withZero && $row->current_balance <= 0)continue;@endphp
                    
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 font-bold">{{ $row->name }}</td>
                            <td class="px-6 py-4 text-center">{{ number_format($row->initial, 2) }}</td>
                            <td class="px-6 py-4 text-center text-green-600 font-bold">+{{ number_format($row->increase, 2) }}</td>
                            <td class="px-6 py-4 text-center text-red-600 font-bold">-{{ number_format($row->decrease, 2) }}</td>
                            <td class="px-6 py-4 text-center font-black bg-slate-50">
                                {{ number_format($row->current_balance, 2) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="p-10 text-center text-gray-400">لا توجد بيانات لهذا الفرع</td>
                        </tr>
                    @endforelse
                </tbody>
                {{-- 
                <tfoot class="bg-slate-900 text-white font-bold">
                    <tr>
                        <td class="px-6 py-4">الإجمالي الكلي لجميع المنتجات</td>
                        <td colspan="3"></td>
                        <td class="px-6 py-4 text-center text-yellow-400 text-lg">
                            {{ number_format($this->total_inventory, 2) }}
                        </td>
                    </tr>
                </tfoot>
                --}}
            </table>
        </div>
    </div>
</x-filament-panels::page>