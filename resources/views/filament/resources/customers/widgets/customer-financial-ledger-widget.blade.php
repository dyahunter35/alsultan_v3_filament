<x-filament-widgets::widget>
    <x-filament::section>
        <div class="flex gap-2 mb-4">
            <input type="date" wire:model="startDate" class="px-2 py-1 border rounded" />
            <input type="date" wire:model="endDate" class="px-2 py-1 border rounded" />
            <button wire:click="$refresh" class="px-3 py-1 text-white bg-blue-500 rounded">تحديث</button>
        </div>

        <table class="min-w-full border table-auto">
            <thead>
                <tr class="text-center bg-gray-100">
                    <th class="px-2 py-1 border">التاريخ</th>
                    <th class="px-2 py-1 border">الوصف</th>
                    <th class="px-2 py-1 border">الداخل</th>
                    <th class="px-2 py-1 border">الخارج</th>
                    <th class="px-2 py-1 border">الرصيد</th>
                </tr>
            </thead>

            <tbody>
                @foreach ($this->getLedger() as $item)
                    <tr class="text-center">
                        <td class="px-2 py-1 border">
                            {{ \Carbon\Carbon::parse($item['date'])->format('Y-m-d') }}
                        </td>

                        <td class="px-2 py-1 border">{{ $item['description'] }}</td>

                        <td class="px-2 py-1 border">
                            {{ number_format($item['amount_in'], 2) }}
                        </td>

                        <td class="px-2 py-1 border">
                            {{ number_format($item['amount_out'], 2) }}
                        </td>

                        <td class="px-2 py-1 font-bold border">
                            {{ number_format($item['balance'], 2) }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

    </x-filament::section>
</x-filament-widgets::widget>
