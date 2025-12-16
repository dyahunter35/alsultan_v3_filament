<x-filament-panels::page>
    <x-filament::section id="report-content">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-xl font-bold">تقرير مالي شامل - الشركات</h1>
                <p class="text-sm text-gray-500">{{ now()->format('Y-m-d H:i') }}</p>
            </div>

            <div class="space-x-2">
                <x-filament::button wire:click="loadData">تحديث</x-filament::button>
                <button onclick="window.print()"
                    class="inline-flex items-center px-3 py-1.5 bg-primary-600 text-white rounded">طباعة</button>
            </div>
        </div>

        <div class="mt-6 overflow-x-auto bg-white shadow-sm rounded-xl">
            <table class="w-full min-w-[900px] divide-y divide-gray-200 text-sm">
                <thead class="text-gray-700 bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-right">#</th>
                        <th class="px-4 py-3 text-right">اسم الشركة</th>
                        <th class="px-4 py-3 text-right">المجموع الوارد</th>
                        <th class="px-4 py-3 text-right">المجموع الصادر</th>
                        <th class="px-4 py-3 text-right">المدفوع</th>
                        <th class="px-4 py-3 text-right">مصروفات الشركة</th>
                        <th class="px-4 py-3 text-right">التحويلات (SDG)</th>
                        <th class="px-4 py-3 text-right">الرصيد (عام)</th>
                        <th class="px-4 py-3 text-right">الرصيد (صيغة)</th>
                        <th class="px-4 py-3 text-right">عدد الحركات</th>
                        <th class="px-4 py-3 text-left">عملات الحركات</th>
                    </tr>
                </thead>

                <tbody class="bg-white divide-y divide-gray-100">
                    @forelse($companies as $company)
                        <tr>
                            <td class="px-4 py-3 text-right">{{ $company['id'] }}</td>
                            <td class="px-4 py-3 text-right">{{ $company['name'] }}</td>
                            <td class="px-4 py-3 text-right">{{ number_format($company['total_in'], 2) }}</td>
                            <td class="px-4 py-3 text-right">{{ number_format($company['total_out'], 2) }}</td>
                            <td class="px-4 py-3 text-right">{{ number_format($company['paid'], 2) }}</td>
                            <td class="px-4 py-3 text-right">{{ number_format($company['company_expense'], 2) }}</td>
                            <td class="px-4 py-3 text-right">{{ number_format($company['converted'], 2) }}</td>
                            <td
                                class="px-4 py-3 text-right font-semibold {{ $company['generic_balance'] < 0 ? 'text-red-600' : 'text-green-700' }}">
                                {{ number_format($company['generic_balance'], 2) }}</td>
                            <td
                                class="px-4 py-3 text-right font-semibold {{ $company['formula_balance'] < 0 ? 'text-red-600' : 'text-green-700' }}">
                                {{ number_format($company['formula_balance'], 2) }}</td>
                            <td class="px-4 py-3 text-right">{{ $company['transactions_count'] }}</td>
                            <td class="px-4 py-3 text-left">{{ implode(', ', $company['currencies'] ?: ['-']) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11" class="px-4 py-6 text-sm text-center text-gray-500">لا توجد شركات</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-filament::section>
</x-filament-panels::page>
