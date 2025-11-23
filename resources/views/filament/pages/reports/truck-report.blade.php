<div>
    <div class="p-4 my-4 bg-white shadow-sm rounded-xl dark:bg-gray-800">
        {{ $this->form }}
    </div>

    @if ($truck)
        <div id="report-content" class="m-2">

            {{-- تفاصيل الشاحنة --}}
            <x-filament::section class="mb-4">
                <header class="clearfix">
                    <div id="logo" style="text-align:center; margin-top:10px;">
                        <img width="80" src="{{ asset('asset/logo.png') }}" alt="logo" class="mx-auto" />
                        <h2 class="text-bold">{{ __('app.name') }}</h2>
                        <h3>{{ __('app.address') }}</h3>
                    </div>

                    <div class="border row" style="border:1px dashed #999; padding:6px;">
                        <div style="display:flex; justify-content:space-between;">
                            <div>
                                <div>📍 <b>اسم السائق:</b> {{ $truck?->driver_name ?? '-' }}</div>
                            </div>
                            <div style="text-align:left;">
                                <div><b>تاريخ التقرير:</b> {{ now()->format('Y/m/d') }}</div>
                            </div>
                        </div>
                    </div>
                </header>

                <dl class="grid grid-cols-3 gap-4 my-4 text-sm text-center md:grid-cols-3">
                    <div>
                        <dt class="font-bold text-gray-600">رقم اللوحة</dt>
                        <dd>{{ $truck->car_number }}</dd>
                    </div>
                    <div>
                        <dt class="font-bold text-gray-600">الموديل</dt>
                        <dd>{{ $truck->truck_model }}</dd>
                    </div>

                    <div>
                        <dt class="font-bold text-gray-600">تكلفة العطلات</dt>
                        <dd>{{ number_format($truck->delay_value, 2) }}</dd>
                    </div>

                </dl>
            </x-filament::section>

            {{-- المنصرفات --}}
            <x-filament::section>
                <x-slot name="heading">المنصرفات</x-slot>
                <table class="w-full text-sm border">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="p-2 border">#</th>
                            <th class="p-2 border">النوع</th>
                            <th class="p-2 border">المبلغ</th>
                            <th class="p-2 border">ملاحظة</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($truck->expenses as $i => $expense)
                            <tr>
                                <td class="p-2 border">{{ $i + 1 }}</td>
                                <td class="p-2 border">{{ $expense->type->label }}</td>
                                <td class="p-2 border">{{ number_format($expense->total_amount, 2) }}</td>
                                <td class="p-2 border">{{ $expense->note }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="p-3 text-center text-gray-500">لا توجد مصروفات مسجلة</td>
                            </tr>
                        @endforelse
                        <tr class="font-semibold bg-gray-50">
                            <td colspan="2" class="p-2 text-right border">الإجمالي</td>
                            <td class="p-2 border" colspan="2">
                                {{ number_format($truck->expenses->sum('total_amount'), 2) }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </x-filament::section>

            {{-- تكلفة الجرام --}}
            <div class="p-4 mt-4 mb-4 border border-blue-200 rounded-lg bg-blue-50">
                <span class="font-semibold text-blue-800">تكلفة الجرام الواحد:</span>
                <strong>{{ number_format($costPerGram, 6) }}</strong>
            </div>

            {{-- تفاصيل المنتجات --}}
            <x-filament::section>
                <x-slot name="heading">تفاصيل البضائع</x-slot>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm border border-gray-200">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="p-2 border">#</th>
                                <th class="p-2 border">اسم المنتج</th>
                                <th class="p-2 border">الوزن (جم)</th>
                                <th class="p-2 border">الكمية</th>
                                <th class="p-2 border">ملاحظة</th>
                                <th class="p-2 border">نسبة الوزن</th>
                                <th class="p-2 border">التكلفة الإجمالية</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($rows as $i => $row)
                                <tr>
                                    <td class="p-2 border">{{ $i + 1 }}</td>
                                    <td class="p-2 border">{{ $row['product_name'] }}</td>
                                    <td class="p-2 border">{{ number_format($row['weight_grams'], 2) }}</td>
                                    <td class="p-2 border">{{ number_format($row['quantity'], 2) }}</td>
                                    <td class="p-2 border">{{ $row['note'] }}</td>
                                    <td class="p-2 border">{{ $row['cost_per_gram'] }}</td>
                                    <td class="p-2 border">{{ number_format($row['total_cost'], 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="p-4 text-center text-gray-500">لا توجد بيانات</td>
                                </tr>
                            @endforelse
                            <tr class="font-semibold bg-gray-50">
                                <td colspan="2" class="p-2 text-right border">الإجمالي</td>
                                <td class="p-2 border" colspan="1">
                                    {{ number_format(array_sum(array_column($rows, 'weight_grams')), 2) }}
                                </td>
                                <td class="p-2 border" colspan="1">
                                    {{ number_format(array_sum(array_column($rows, 'quantity')), 2) }}
                                </td>
                                <td class="p-2 border" colspan="2"></td>
                                <td class="p-2 border" colspan="1">
                                    {{ number_format(array_sum(array_column($rows, 'total_cost')), 2) }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </x-filament::section>

            {{-- الحسابات النهائية --}}
            <x-filament::section class="mt-6">
                <x-slot name="heading">📊 الحسابات النهائية</x-slot>
                @php
                    $fare = $truck->truck_fare ?? 0;
                    $delay = $truck->delay_value ?? 0;
                    $expenses = $truck->expenses->sum('total_amount') ?? 0;
                    // $netFare = $fare - ($delay + $expenses);
                    $totalWeight = $truck->total_weight ?? 1;
                    $costPerGram = $costPerGram ?? 0;
                    $totalProductsCost = array_sum(array_column($rows, 'total_cost')) ?? 0;
                @endphp
                <table class="w-full text-sm border border-gray-200">
                    <tbody>
                        <tr>
                            <td class="p-2 font-semibold text-gray-700 border">النولون</td>
                            <td class="p-2 border">{{ number_format($fare, 2) }}</td>
                        </tr>
                        <tr>
                            <td class="p-2 font-semibold text-gray-700 border">تكلفة العطلات</td>
                            <td class="p-2 border">{{ number_format($delay, 2) }}</td>
                        </tr>
                        <tr>
                            <td class="p-2 font-semibold text-gray-700 border">إجمالي المنصرفات</td>
                            <td class="p-2 border">{{ number_format($expenses, 2) }}</td>
                        </tr>
                        {{-- <tr>
                            <td class="p-2 font-semibold text-gray-700 border">صافي النولون بعد الخصم</td>
                            <td class="p-2 text-green-700 border">{{ number_format($netFare, 2) }}</td>
                        </tr> --}}
                        <tr>
                            <td class="p-2 font-semibold text-gray-700 border">الوزن الكلي</td>
                            <td class="p-2 border">{{ number_format($totalWeight, 2) }}</td>
                        </tr>
                        <tr>
                            <td class="p-2 font-semibold text-gray-700 border">تكلفة الجرام الواحد</td>
                            <td class="p-2 border">{{ number_format($costPerGram, 6) }}</td>
                        </tr>
                        <tr class="font-bold bg-gray-50">
                            <td class="p-2 text-blue-800 border">إجمالي تكلفة البضائع</td>
                            <td class="p-2 text-blue-800 border">{{ number_format($totalProductsCost, 2) }}</td>
                        </tr>
                    </tbody>
                </table>

            </x-filament::section>

        </div>
        <x-print-button />
    @endif
</div>
