<x-filament-panels::page id="report-content">
    <x-filament::section style="no-print">

        <div class="flex items-center justify-between">

            <x-filament::input.wrapper>
                <x-filament::input.select :searchale wire:model.live="companyId" wire:change="loadData">

                    @foreach ($companies as $c)
                        <option value="{{ $c->id }}">{{ $c->name }}</option>
                    @endforeach
                </x-filament::input.select>
            </x-filament::input.wrapper>



        </div>
    </x-filament::section>


    <x-filament::section>
        <div>
            @livewire(\App\Filament\Resources\Companies\Widgets\CurrencyWidget::class, ['record' => $company])
        </div>
    </x-filament::section>

    <x-filament::section>
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-xl font-bold">تقرير شركة - التفاصيل والمعاملات</h1>
                <p class="text-sm text-gray-500">{{ $company->name ?? '-' }} — {{ now()->format('Y-m-d H:i') }}</p>
            </div>

            <div class="space-x-2">
                <x-filament::button wire:click="loadData">تحديث</x-filament::button>
                <button onclick="window.print()"
                    class="inline-flex items-center px-3 py-1.5 bg-primary-600 text-white rounded">طباعة</button>
            </div>
        </div>

        {{-- Company summary --}}
        <div class="grid grid-cols-1 gap-4 mt-6 md:grid-cols-3">
            <div class="p-4 bg-white rounded-lg shadow-sm">
                <h3 class="text-sm font-medium text-gray-600">معلومات أساسية</h3>
                <dl class="mt-2 text-sm text-gray-700">
                    <div class="flex justify-between">
                        <dt>الاسم</dt>
                        <dd>{{ $company->name ?? '-' }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt>الهاتف</dt>
                        <dd>{{ $company->phone ?? '-' }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt>النوع</dt>
                        <dd>{{ $company->type ?? '-' }}</dd>
                    </div>
                </dl>
            </div>

            <div class="p-4 bg-white rounded-lg shadow-sm">
                <h3 class="text-sm font-medium text-gray-600">ملخص مالي</h3>
                <dl class="mt-2 text-sm text-gray-700">
                    <div class="flex justify-between">
                        <dt>المجموع الوارد</dt>
                        <dd>{{ number_format($totals['total_in'] ?? 0, 2) }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt>المجموع الصادر</dt>
                        <dd>{{ number_format($totals['total_out'] ?? 0, 2) }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt>الرصيد الحالي</dt>
                        <dd
                            class="font-semibold {{ ($totals['balance'] ?? 0) < 0 ? 'text-red-600' : 'text-green-700' }}">
                            {{ number_format($totals['balance'] ?? 0, 2) }}
                        </dd>
                    </div>
                </dl>
            </div>

            <div class="p-4 bg-white rounded-lg shadow-sm">
                <h3 class="text-sm font-medium text-gray-600">علاقات وسجلات</h3>
                <div class="mt-2 text-sm text-gray-700">
                    <div>عدد الشاحنات (شركة): {{ $company->trucksAsCompany->count() ?? 0 }}</div>
                    <div>عدد الشاحنات (مقاول): {{ $company->trucksAsContractor->count() ?? 0 }}</div>
                    <div>عدد المصروفات المرتبطة: {{ $company->expenses->count() ?? 0 }}</div>
                </div>
            </div>
        </div>

        {{-- Transactions table --}}
        <div class="mt-6 overflow-x-auto bg-white shadow-sm rounded-xl">
            <h3 class="text-sm font-medium text-gray-600">سجل العملات </h3>

            <table class="w-full min-w-[800px] divide-y divide-gray-200 text-sm">
                <thead class="text-gray-700 bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-right">#</th>
                        <th class="px-4 py-3 text-right">التاريخ</th>
                        <th class="px-4 py-3 text-right">النوع</th>
                        <th class="px-4 py-3 text-right">المبلغ</th>
                        <th class="px-4 py-3 text-right">العملة</th>
                        <th class="px-4 py-3 text-left">ملاحظة</th>
                    </tr>
                </thead>

                <tbody class="bg-white divide-y divide-gray-100">
                    @forelse($transactions as $t)
                        <tr>
                            <td class="px-4 py-3 text-right">{{ $t['id'] }}</td>
                            <td class="px-4 py-3 text-right">{{ $t['date'] }}</td>
                            <td class="px-4 py-3 text-right">
                                {{ $t['type']->getLabel() }}</td>
                            <td class="px-4 py-3 text-right">{{ number_format($t['total'], 2) }}</td>
                            <td class="px-4 py-3 text-right">{{ $t['currency'] ?? '-' }}</td>
                            <td class="px-4 py-3 text-left">{{ $t['note'] ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-6 text-sm text-center text-gray-500">لا توجد معاملات</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Optionally show expenses/trucks details --}}
        <div class="grid grid-cols-1 gap-4 mt-6 md:grid-cols-1">
            <div class="p-4 bg-white rounded-lg shadow-sm">
                <h3 class="mb-2 text-sm font-medium text-gray-600">مصروفات مرتبطة</h3>
                <ul class="space-y-2 text-sm text-gray-700">
                    @forelse($company->expenses as $e)
                        <li class="flex justify-between">
                            <span>{{ $e->description ?? 'مصروف' }}</span><span>{{ number_format($e->amount ?? 0, 2) }}</span>
                        </li>
                    @empty
                        <li class="text-gray-500">لا توجد مصروفات</li>
                    @endforelse
                </ul>
            </div>

            <div class="p-4 bg-white rounded-lg shadow-sm">
                <h3 class="mb-2 text-sm font-medium text-gray-600">شاحنات مرتبطة (عينة)</h3>
                <ul class="space-y-2 text-sm text-gray-700">
                    @forelse($company->trucksAsCompany->merge($company->trucksAsContractor) as $truck)
                        <li class="flex justify-between"><span>{{ $truck->driver_name ?? '—' }}
                                ({{ $truck->car_number ?? '—' }})
                            </span><span>{{ $truck->pack_date ?? '' }}</span>
                        </li>
                    @empty
                        <li class="text-gray-500">لا توجد شاحنات</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </x-filament::section>

    <x-filament::section>
        <div>
            @livewire(\App\Filament\Resources\Companies\Widgets\CompanyFinanceOverview::class, ['record' => $company])
        </div>
    </x-filament::section>
</x-filament-panels::page>
