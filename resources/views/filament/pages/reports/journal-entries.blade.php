<x-filament-panels::page>

    <div class="space-y-6" id="report-content">

        {{-- Filters --}}
        <x-filament::section>
            <div class="flex items-center justify-between gap-6">

                {{-- Date Input --}}
                <div>
                    {{-- <x-filament::input.label>اختر اليوم</x-filament::input.label> --}}
                    <x-filament::input type="date" wire:model.live="date" class="input" />
                </div>

                {{-- Toggle Include Previous --}}
                <div class ='input'>
                    <label>
                        <x-filament::input.checkbox wire:model.live="include_previous" />

                        <span>
                            تضمين الرصيد المرحل؟
                        </span>
                    </label>
                    {{-- <x-filament::input.label>/x-filament::input.label> --}}
                    {{-- <x-filament::toggle wire:model.live="include_previous" /> --}}
                </div>

            </div>
        </x-filament::section>

        {{-- Balances Overview --}}
        <x-filament::section>
            <div class="grid grid-cols-3 gap-4 text-center">

                {{-- Opening Balance --}}
                <div class="p-4 shadow rounded-xl bg-gray-50">
                    <div class="text-sm">الرصيد المرحل</div>
                    <div
                        class="text-xl font-bold {{ $this->getOpeningBalance() >= 0 ? 'text-green-600' : 'text-red-600' }}">
                        {{ number_format($this->getOpeningBalance(), 2) }}
                    </div>
                </div>

                {{-- Today Profit --}}
                <div class="p-4 shadow rounded-xl bg-gray-50">
                    <div class="text-sm">ربح / خسارة اليوم</div>
                    <div
                        class="text-xl font-bold {{ $this->getTodayProfit() >= 0 ? 'text-green-600' : 'text-red-600' }}">
                        {{ number_format($this->getTodayProfit(), 2) }}
                    </div>
                </div>

                {{-- Final --}}
                <div class="p-4 shadow rounded-xl bg-gray-50">
                    <div class="text-sm">الصافي النهائي</div>
                    <div
                        class="text-xl font-bold {{ $this->getFinalBalance() >= 0 ? 'text-green-600' : 'text-red-600' }}">
                        {{ number_format($this->getFinalBalance(), 2) }}
                    </div>
                </div>

            </div>
        </x-filament::section>

        {{-- Journal Entries --}}
        <x-filament::section>
            <x-slot name="heading">قيود اليومية</x-slot>

            <table class="w-full text-right border">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="p-2">النوع</th>
                        <th class="p-2">الوصف</th>
                        <th class="p-2">مدين</th>
                        <th class="p-2">دائن</th>
                        <th class="p-2">الوقت</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach ($this->getJournalEntries() as $item)
                        <tr class="border-b">
                            <td class="p-2">{{ $item->type }}</td>
                            <td class="p-2">{{ $item->description }}</td>
                            <td class="p-2">{{ number_format($item->debit, 2) }}</td>
                            <td class="p-2">{{ number_format($item->credit, 2) }}</td>
                            <td class="p-2">{{ $item->created_at }}</td>
                        </tr>
                    @endforeach
                </tbody>

            </table>

        </x-filament::section>

    </div>

    <x-print-button />

</x-filament-panels::page>
