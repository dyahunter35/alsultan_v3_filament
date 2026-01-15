<x-filament-panels::page>
    <x-filament::section class="mb-4 shadow-sm no-print border-slate-200">
        <div class="flex flex-col gap-4 md:flex-row md:items-end">
            <div class="flex-1">{{ $this->form }}</div>
            <x-filament::button wire:click="loadData" color="gray" icon="heroicon-m-arrow-path">تحديث</x-filament::button>
        </div>
    </x-filament::section>

    @if ($supplyings->count())
    <div id="report-content" class="space-y-6 print:m-0">
        <x-report-header :label="$this->getTitle()" />

        {{-- ملخص بيانات العميل المختار --}}
        @if($customer)
        <div class="grid grid-cols-1 overflow-hidden bg-white border shadow-sm md:grid-cols-3 rounded-xl print:shadow-none print:border-slate-300">
            <div class="flex items-center justify-center gap-2 px-4 py-3 border-l border-slate-100">
                <x-filament::icon icon="heroicon-m-user" class="w-4 h-4 text-slate-400" />
                <span class="text-sm font-bold text-slate-400">العميل:</span>
                <span class="text-sm font-bold text-slate-700">{{ $customer->name }}</span>
            </div>
            <div class="flex items-center justify-center gap-2 px-4 py-3 border-l border-slate-100">
                <x-filament::icon icon="heroicon-m-phone" class="w-4 h-4 text-slate-400" />
                <span class="text-sm font-bold text-slate-400">الهاتف:</span>
                <span class="text-sm font-bold tabular-nums text-slate-700">{{ $customer->phone ?? '-' }}</span>
            </div>
            <div class="flex items-center justify-center gap-3 px-4 py-3 bg-slate-50/50">
                <span class="text-sm font-bold text-slate-400">إجمالي المورد:</span>
                <span class="text-lg font-black text-green-700 tabular-nums">
                    {{ number_format($supplyings->sum('paid_amount'), 2) }}
                </span>
            </div>
        </div>
        @endif

        <div class="overflow-x-auto bg-white border shadow-sm border-slate-200 rounded-xl print:border-slate-800">
            <table class="w-full text-center border-collapse text-sm print:text-[11px]">
                <thead>
                    <tr class="font-bold text-white bg-slate-800">
                        <th class="p-3 border border-slate-700 w-28">التاريخ</th>
                        @if(!$customerId)
                        <th class="p-3 text-right border border-slate-700">العميل</th>
                        @endif
                        <th class="p-3 text-right border border-slate-700">المندوب</th>
                        <th class="p-3 text-right border border-slate-700">البيان</th>
                        <th class="p-3 border border-slate-700">وسيلة الدفع</th>
                        <th class="p-3 border border-slate-700">المرجع/الإيصال</th>
                        <th class="p-3 border border-slate-700 bg-green-900/50 w-32">المبلغ المورد</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 tabular-nums">
                    @foreach ($supplyings as $row)
                    <tr class="hover:bg-slate-50">
                        <td class="p-2 border border-slate-200 text-slate-500">{{ $row->created_at?->format('Y-m-d') }}</td>
                        @if(!$customerId)

                        <td class="p-2 border border-slate-200 text-right">{{ $row->customer?->name ?? '—' }}</td>
                        @endif
                        <td class="p-2 border border-slate-200 text-right">{{ $row->representative?->name ?? '—' }}</td>
                        <td class="p-2 px-4 text-right border border-slate-200 font-medium">{{ $row->statement }}</td>
                        <td class="p-2 border border-slate-200">
                            <span class="px-2 py-1 text-xs bg-slate-100 rounded-full">{{ $row->payment_method->getLabel() }}</span>
                        </td>
                        <td class="p-2 border border-slate-200 text-slate-400 italic">{{ $row->payment_reference ?? '—' }}</td>
                        <td class="p-2 font-black text-green-700 border border-slate-200 bg-green-50/30">
                            {{ number_format($row->paid_amount, 2) }}
                        </td>
                    </tr>
                    @endforeach
                    <tr class="font-black bg-slate-100 border-t-2 border-slate-800">
                    
                        <td colspan="{{ ($customerId?'5':'6') }}" class="p-3 text-left px-6">إجمالي التوريدات في هذه الفترة</td>
                        <td class="p-3 text-lg text-green-800 border-slate-300 bg-green-100">
                            {{ number_format($supplyings->sum('paid_amount'), 2) }}
                        </td>
                    </tr>
                
                </tbody>
                
            </table>
        </div>
    </div>
    <x-print-button />
    @else
    <div class="p-20 text-center bg-white border-2 border-gray-300 border-dashed rounded-xl">
        <x-filament::icon icon="heroicon-o-document-magnifying-glass" class="w-12 h-12 mx-auto mb-4 text-gray-300" />
        <h3 class="text-xl font-bold text-gray-400">لا توجد عمليات توريد مسجلة</h3>
    </div>
    @endif
</x-filament-panels::page>