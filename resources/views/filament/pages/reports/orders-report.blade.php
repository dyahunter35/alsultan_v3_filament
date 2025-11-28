<x-filament-panels::page>

    <div class="space-y-6">

        <h2 class="text-2xl font-bold text-gray-800">تقرير المبيعات اليومي</h2>

        <div class="flex items-center gap-4">
            <div class="w-48">
                <x-filament::input.wrapper>
                    <x-filament::input type="date" wire:model.live="date" />
                </x-filament::input.wrapper>
            </div>

            <x-filament::button wire:click="loadData" color="primary" icon="heroicon-o-arrow-path">
                تحديث
            </x-filament::button>
        </div>

        {{-- الملخص --}}
        {{-- <div class="grid grid-cols-1 gap-4 md:grid-cols-4">

            <x-filament::stats.card label="إجمالي المبيعات" value="{{ number_format($summary['total_sales'], 2) }}" />

            <x-filament::stats.card label="عدد الفواتير" value="{{ $summary['orders_count'] }}" />

            <x-filament::stats.card label="متوسط البيع" value="{{ number_format($summary['avg_sale'], 2) }}" />

            <x-filament::stats.card label="الخصومات" value="{{ number_format($summary['discounts'], 2) }}" />
        </div> --}}

        {{-- جدول الفواتير --}}
        <div class="overflow-x-auto bg-white shadow-sm rounded-xl">
            <table class="min-w-full text-sm text-right border-collapse">
                <thead class="bg-gray-50">
                    <tr class="font-semibold text-gray-700">
                        <th class="px-4 py-2 border-b">التاريخ</th>
                        <th class="px-4 py-2 border-b">رقم الفاتورة</th>
                        <th class="px-4 py-2 border-b">العميل</th>
                        <th class="px-4 py-2 border-b">التفاصيل</th>
                        <th class="px-4 py-2 border-b">الإجمالي</th>
                        <th class="px-4 py-2 border-b">الخصم</th>
                        <th class="px-4 py-2 border-b">المخزن</th>
                        <th class="px-4 py-2 border-b">المندوب</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($orders as $order)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-2 border-b">
                                {{ $order->created_at->format('d-m-Y') }}
                            </td>
                            <td class="px-4 py-2 border-b">{{ $order->number }}</td>
                            <td class="px-4 py-2 border-b">{{ $order->customer?->name }}</td>
                            <td class="px-4 py-2 border-b">
                                @foreach ($order->items as $item)
                                    <div>
                                        {{ $item->product?->name }} [ {{ $item->qty }} *
                                        {{ number_format($item->price, 2) }} =
                                        {{ number_format($item->qty * $item->price, 2) }} ]
                                    </div>
                                @endforeach
                            </td>
                            <td class="px-4 py-2 font-semibold text-green-700 border-b">
                                {{ number_format($order->total, 2) }}
                            </td>
                            <td class="px-4 py-2 text-red-700 border-b">
                                {{ number_format($order->discount, 2) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-3 text-center text-gray-500">
                                لا توجد فواتير لهذا اليوم.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    </div>

</x-filament-panels::page>
