<x-filament-panels::page>
    {{-- قسم الفلترة --}}
    <x-filament::section class="mb-4 shadow-sm no-print border-slate-200">
        <div class="flex flex-col gap-4 md:flex-row md:items-end">
            <div class="flex-1">
                {{ $this->form }}
            </div>
            <div class="flex items-center gap-2">
                <x-filament::button wire:click="loadData" color="gray" icon="heroicon-m-arrow-path">
                    تحديث البيانات
                </x-filament::button>
            </div>
        </div>
    </x-filament::section>

    @if ($date_range)
        <div class="space-y-2" id="report-content">
            <x-report-header label="تقرير المبيعات التفصيلي" />

            {{-- العنوان الرسمي --}}
            <div class="text-center border-b border-slate-100">
                <p class="text-sm text-slate-500 tabular-nums">الفترة: {{ $date_range }}</p>
            </div>

            {{-- شريط الملخص الموزون --}}
            <div
                class="grid grid-cols-2 overflow-hidden bg-white border shadow-sm md:grid-cols-4 print:grid-cols-4 rounded-xl print:shadow-none print:border-slate-300">
                <div
                    class="flex flex-col items-center justify-center p-4 border-b md:border-b-0 md:border-l border-slate-100 print:border-l">
                    <span class="mb-1 text-xs font-bold uppercase text-slate-400">إجمالي المبيعات</span>
                    <span
                        class="text-xl font-black text-green-600 tabular-nums">{{ number_format($summary['total_sales'], 2) }}</span>
                </div>

                <div
                    class="flex flex-col items-center justify-center p-4 border-b md:border-b-0 md:border-l border-slate-100 print:border-l">
                    <span class="mb-1 text-xs font-bold uppercase text-slate-400">عدد الفواتير</span>
                    <span class="text-xl font-black tabular-nums text-slate-700">{{ $summary['orders_count'] }}</span>
                </div>

                <div class="flex flex-col items-center justify-center p-4 border-l border-slate-100 print:border-l">
                    <span class="mb-1 text-xs font-bold uppercase text-slate-400">متوسط الفاتورة</span>
                    <span
                        class="text-xl font-black text-blue-600 tabular-nums">{{ number_format($summary['avg_sale'], 2) }}</span>
                </div>

                <div class="flex flex-col items-center justify-center p-4 bg-slate-50/30">
                    <span class="mb-1 text-xs font-bold uppercase text-slate-400">إجمالي الخصومات</span>
                    <span
                        class="text-xl font-black text-red-600 tabular-nums">{{ number_format($summary['discounts'], 2) }}</span>
                </div>
            </div>

            {{-- جدول المبيعات الرئيسي --}}
            <div class="overflow-x-auto bg-white border shadow-sm rounded-xl print:shadow-none print:border-slate-800">
                <table class="w-full text-sm text-center border-collapse">
                    <thead>
                        <tr class="font-bold text-white bg-slate-800 print:bg-slate-100 print:text-black">
                            <th class="px-4 py-3 border border-slate-700">التاريخ</th>
                            <th class="px-4 py-3 border border-slate-700">رقم الفاتورة</th>
                            <th class="px-4 py-3 border border-slate-700">العميل / الفرع</th>
                            <th class="px-4 py-3 border border-slate-700">المندوب</th>
                            <th class="px-2 py-3 border border-slate-700">المنتج</th>
                            <th class="px-2 py-3 border border-slate-700">الكمية</th>
                            <th class="px-2 py-3 border border-slate-700">السعر</th>
                            <th class="px-2 py-3 border border-slate-700">المجموع</th>
                            <th class="px-4 py-3 border border-slate-700">الخصم</th>
                            <th class="px-4 py-3 border border-slate-700">الإجمالي</th>
                        </tr>
                    </thead>
                    <tbody class="text-center divide-y divide-slate-200">
                        @forelse($orders as $order)
                            <tr class="transition-colors hover:bg-slate-50">
                                <td class="px-4 py-2 font-medium border tabular-nums text-[11px]">
                                    {{ $order->created_at?->format('Y/m/d') }}
                                </td>
                                <td class="px-4 py-2 font-bold text-center border tabular-nums">
                                    #{{ $order->number }}
                                </td>
                                <td class="px-4 py-2 border">
                                    <div class="font-bold text-slate-700">{{ $order->customer?->name ?? 'عميل نقدي' }}
                                    </div>
                                    <div class="text-[13px] text-slate-400 uppercase">{{ $order->branch?->name }}</div>
                                </td>
                                <td class="px-4 py-2 font-bold text-center border tabular-nums">
                                    {{ str_replace(' (مندوب)', '', $order->representative?->name) }}
                                </td>
                                {{-- الجدول الداخلي الموزون للأصناف بدون رؤوس --}}
                                <td colspan="4" class="px-2 py-2 align-top border bg-slate-50/10">
                                    <table class="w-full border-collapse text-[15px] divide-y divide-slate-200">
                                        <tbody>
                                            @foreach ($order->items as $item)
                                                <tr class="border-b border-slate-100 last:border-0 hover:bg-white/50">
                                                    {{-- اسم الصنف - مساحة مرنة --}}
                                                    <td class="text-right py-1.5 pr-1 font-bold text-slate-700  w-24">
                                                        {{ $item->product?->name }}
                                                    </td>

                                                    {{-- الكمية - عرض ثابت --}}
                                                    <td
                                                        class="text-center py-1.5 px-2 tabular-nums w-12 border-r border-slate-50">
                                                        <span
                                                            class="font-black text-slate-900">{{ number_format($item->qty) }}</span>
                                                    </td>

                                                    {{-- السعر - عرض ثابت --}}
                                                    <td
                                                        class="text-center py-1.5 px-2 tabular-nums w-20 border-r border-slate-50">
                                                        <span
                                                            class="font-medium text-slate-600">{{ number_format($item->price, 2) }}</span>
                                                    </td>

                                                    {{-- الإجمالي - عرض ثابت --}}
                                                    <td
                                                        class="text-center py-1.5 pl-1 tabular-nums w-24 border-r border-slate-50">

                                                        <span
                                                            class="font-black text-green-700">{{ number_format($item->qty * $item->price, 2) }}</span>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </td>

                                <td class="px-4 py-2 text-center text-red-600 border tabular-nums">
                                    {{ number_format($order->discount, 2) }}
                                </td>
                                <td
                                    class="px-4 py-2 font-black text-center text-green-700 border tabular-nums bg-green-50/30">
                                    {{ number_format($order->total, 2) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-10 italic text-center text-gray-400">
                                    لا توجد مبيعات مسجلة ضمن هذه الفترة.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <x-print-button />
    @else
        <div class="p-20 text-center bg-white border-2 border-gray-300 border-dashed shadow-sm rounded-xl">
            <x-filament::icon icon="heroicon-o-calendar" class="w-12 h-12 mx-auto mb-4 text-gray-300" />
            <h3 class="text-xl font-bold tracking-tight text-gray-400">الرجاء اختيار الفترة لتوليد
                التقرير</h3>
        </div>
    @endif


</x-filament-panels::page>
