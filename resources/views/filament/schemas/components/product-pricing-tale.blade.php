@props(['data'])

<div class="p-4 overflow-x-auto bg-white rounded-lg shadow" dir="rtl">
    {{-- Header Section --}}
    <div class="flex items-center justify-between pb-4 mb-6 border-b">
        <div>
            <h2 class="text-xl font-bold underline">شركة جلفريك للتجارة</h2>
            <h3 class="mt-2 text-lg font-bold text-center">بيان الشحنة رقم {{ $data['truck']->id }}</h3>
        </div>
        <div class="text-sm">
            <p><strong>التاريخ:</strong> {{ now()->format('Y-m-d') }}</p>
            <p><strong>السائق:</strong> {{ $data['truck']->driver_name ?? 'غير محدد' }}</p>
        </div>
    </div>

    {{-- Control Inputs --}}
    {{-- لاحظ: المتغيرات هنا مربوطة بالـ Livewire Parent مباشرة --}}
    <div class="grid grid-cols-4 gap-4 p-4 mb-6 border rounded bg-gray-50">
        <div>
            <label class="block text-sm font-bold text-gray-700">التكلفة بالسوداني (الجمارك)</label>
            <input type="number" wire:model.live="customs_sdg"
                class="w-full p-2 border border-gray-300 rounded focus:ring-2 focus:ring-blue-500">
        </div>
        <div>
            <label class="block text-sm font-bold text-gray-700">المعادل (سعر الصرف)</label>
            <input type="number" step="0.1" wire:model.live="exchange_rate"
                class="w-full p-2 border border-gray-300 rounded focus:ring-2 focus:ring-blue-500">
        </div>
        <div>
            <label class="block text-sm font-bold text-gray-700">تكلفة الترحيل</label>
            <input type="number" wire:model.live="transport_cost"
                class="w-full p-2 border border-gray-300 rounded focus:ring-2 focus:ring-blue-500">
        </div>
        <div>
            <label class="block text-sm font-bold text-gray-700">التكلفة بالجنية المصري</label>
            <div class="p-2 font-mono font-bold bg-gray-200 rounded">
                {{ number_format($data['customs_egp_total'], 2) }}</div>
        </div>
    </div>

    {{-- Main Table --}}
    <table class="w-full text-xs text-center border border-collapse border-gray-400">
        <thead>
            <tr class="bg-gray-200">
                <th colspan="3" class="p-1 border border-gray-400">بيانات الصنف</th>
                <th colspan="2" class="p-1 border border-gray-400">الكميات</th>
                <th colspan="2" class="p-1 border border-gray-400">السعر الأساسي</th>
                <th colspan="3" class="p-1 border border-gray-400">التكاليف المضافة</th>
                <th colspan="3" class="p-1 border border-gray-400">البيانات المالية بالمصري</th>
                <th colspan="2" class="p-1 border border-gray-400">سعر الطرد</th>
                <th colspan="2" class="p-1 border border-gray-400">السعر السوداني</th>
            </tr>
            <tr class="font-bold bg-gray-100">
                <th class="w-10 p-1 border border-gray-400">الرقم</th>
                <th class="p-1 border border-gray-400">البند</th>
                <th class="p-1 border border-gray-400">المقاس</th>
                <th class="p-1 border border-gray-400">وزن الطرد</th>
                <th class="p-1 border border-gray-400">العدد بالطرد</th>
                <th class="p-1 border border-gray-400">الطن (وزن)</th>
                <th class="p-1 border border-gray-400">سعر الطن</th>
                <th class="p-1 border border-gray-400">المجموع (مصري)</th>
                <th class="p-1 border border-gray-400">الترحيل</th>
                <th class="w-20 p-1 border border-gray-400">الجمارك/مصاريف</th>
                <th class="p-1 font-bold border border-gray-400 bg-yellow-50">التكلفة الكلية</th>
                <th class="w-16 p-1 border border-gray-400 bg-blue-50">نسبة الارباح %</th>
                <th class="p-1 border border-gray-400">قيمة الارباح</th>
                <th class="p-1 border border-gray-400">سعر البيع (م)</th>
                <th class="p-1 border border-gray-400">سعر الطرد (م)</th>
                <th class="p-1 border border-gray-400">سعر الطرد (س)</th>
                <th class="p-1 border border-gray-400">سعر الطن (س)</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($data['rows'] as $row)
                <tr class="hover:bg-gray-50 group">
                    <td class="p-1 border border-gray-400">{{ $row->index }}</td>
                    <td class="p-1 text-right border border-gray-400 whitespace-nowrap">{{ $row->product_name }}</td>
                    <td class="p-1 border border-gray-400">{{ $row->size }}</td>
                    <td class="p-1 border border-gray-400">{{ $row->unit_weight }}</td>
                    <td class="p-1 border border-gray-400">{{ $row->quantity }}</td>
                    <td class="p-1 border border-gray-400">{{ $row->weight_ton }}</td>
                    <td class="p-1 border border-gray-400">{{ number_format($row->unit_price, 2) }}</td>
                    <td class="p-1 border border-gray-400">{{ number_format($row->base_total_egp, 2) }}</td>
                    <td class="p-1 border border-gray-400">{{ number_format($row->transport_cost, 2) }}</td>
                    <td class="p-1 border border-gray-400">{{ number_format($row->customs_cost, 2) }}</td>
                    <td class="p-1 font-bold border border-gray-400 bg-yellow-50">
                        {{ number_format($row->total_cost, 2) }}</td>

                    {{-- Input الربح --}}
                    <td class="p-0 border border-gray-400 bg-blue-50">
                        <input type="number" step="0.5"
                            wire:model.live.debounce.500ms="profit_percents.{{ $row->cargo_id }}"
                            class="w-full h-full p-1 text-xs font-bold text-center text-blue-700 bg-transparent border-none focus:ring-0">
                    </td>

                    <td class="p-1 border border-gray-400">{{ number_format($row->profit_value, 2) }}</td>
                    <td class="p-1 font-bold text-green-700 border border-gray-400">
                        {{ number_format($row->selling_price_egp, 2) }}</td>
                    <td class="p-1 border border-gray-400">{{ number_format($row->package_price_egp, 2) }}</td>
                    <td class="p-1 font-bold border border-gray-400">
                        {{ number_format($row->package_price_sdg, 2) }}</td>
                    <td class="p-1 border border-gray-400">{{ number_format($row->ton_price_sdg, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot class="bg-gray-200 font-bold text-[10px]">
            <tr>
                <td colspan="3" class="p-1 text-center border border-gray-400">المجمــوع</td>
                <td class="p-1 border border-gray-400"></td>
                <td class="p-1 border border-gray-400">{{ $data['totals']['quantity'] }}</td>
                <td class="p-1 border border-gray-400">{{ $data['totals']['weight'] }}</td>
                <td class="p-1 border border-gray-400"></td>
                <td class="p-1 border border-gray-400">{{ number_format($data['totals']['base_egp'], 2) }}</td>
                <td class="p-1 border border-gray-400">{{ number_format($data['totals']['transport'], 2) }}</td>
                <td class="p-1 border border-gray-400">{{ number_format($data['totals']['customs'], 2) }}</td>
                <td class="p-1 border border-gray-400">{{ number_format($data['totals']['total_cost'], 2) }}</td>
                <td class="p-1 border border-gray-400"></td>
                <td class="p-1 border border-gray-400">{{ number_format($data['totals']['profit'], 2) }}</td>
                <td class="p-1 border border-gray-400">{{ number_format($data['totals']['selling_egp'], 2) }}</td>
                <td class="p-1 border border-gray-400"></td>
                <td class="p-1 border border-gray-400"></td>
                <td class="p-1 border border-gray-400"></td>
            </tr>
        </tfoot>
    </table>
</div>
