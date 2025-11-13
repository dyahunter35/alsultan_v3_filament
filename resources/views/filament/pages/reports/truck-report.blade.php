<div>
    <div class="p-4 mb-4 bg-white shadow-sm rounded-xl dark:bg-gray-800">
        {{ $this->form }}
    </div>

    @if ($truck)
        {{-- تفاصيل الشاحنة --}}
        <x-filament::section>
            <x-slot name="heading">تفاصيل الشاحنة</x-slot>
            <dl class="grid grid-cols-2 gap-4 text-sm md:grid-cols-3">
                <div>
                    <dt class="font-semibold text-gray-600">اسم السائق</dt>
                    <dd>{{ $truck->driver_name }}</dd>
                </div>
                <div>
                    <dt class="font-semibold text-gray-600">رقم اللوحة</dt>
                    <dd>{{ $truck->plate_number }}</dd>
                </div>
                <div>
                    <dt class="font-semibold text-gray-600">الموديل</dt>
                    <dd>{{ $truck->model }}</dd>
                </div>
                <div>
                    <dt class="font-semibold text-gray-600">النولون</dt>
                    <dd>{{ number_format($truck->nolon, 2) }}</dd>
                </div>
                <div>
                    <dt class="font-semibold text-gray-600">تكلفة العطلات</dt>
                    <dd>{{ number_format($truck->extra_days_cost, 2) }}</dd>
                </div>
                <div>
                    <dt class="font-semibold text-gray-600">عدد الأيام الزائدة</dt>
                    <dd>{{ $truck->extra_days }}</dd>
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
                            <th class="p-2 border">تكلفة الجرام</th>
                            <th class="p-2 border">التكلفة الإجمالية</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($rows as $i => $row)
                            <tr>
                                <td class="p-2 border">{{ $i + 1 }}</td>
                                <td class="p-2 border">{{ $row['product_name'] }}</td>
                                <td class="p-2 border">{{ number_format($row['weight_grams']) }}</td>
                                <td class="p-2 border">{{ $row['quantity'] }}</td>
                                <td class="p-2 border">{{ $row['note'] }}</td>
                                <td class="p-2 border">{{ number_format($row['cost_per_gram'], 6) }}</td>
                                <td class="p-2 border">{{ number_format($row['total_cost'], 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="p-4 text-center text-gray-500">لا توجد بيانات</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-filament::section>
    @endif
</div>
