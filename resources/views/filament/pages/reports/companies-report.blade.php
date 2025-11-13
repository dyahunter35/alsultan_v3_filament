<x-filament-panels::page>
    <x-filament::section>

        <div class="space-y-4">
            <div class="flex items-center justify-between">
                <h2 class="text-2xl font-bold text-primary-600">تقرير المعاملات المالية للشركات</h2>

                <x-filament::button wire:click="loadData" color="primary" icon="heroicon-o-arrow-path">
                    تحديث البيانات
                </x-filament::button>
            </div>

            <div class="overflow-x-auto bg-white shadow-sm rounded-xl">
                <table class="min-w-full text-sm text-right border-collapse">
                    <thead class="font-semibold text-gray-700 bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 border-b">#</th>
                            <th class="px-4 py-2 border-b">اسم الشركة</th>
                            <th class="px-4 py-2 text-red-600 border-b">المدفوعات (SEND)</th>
                            <th class="px-4 py-2 text-orange-600 border-b">مصروفات الشركة</th>
                            <th class="px-4 py-2 text-blue-600 border-b">تحويلات (Convert)</th>
                            <th class="px-4 py-2 text-gray-800 border-b">الرصيد النهائي</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($companies as $company)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-2 border-b">{{ $company['id'] }}</td>
                                <td class="px-4 py-2 font-medium border-b">{{ $company['name'] }}</td>

                                <td class="px-4 py-2 font-semibold text-red-700 border-b">
                                    {{ number_format($company['paid'], 2) }}
                                </td>

                                <td class="px-4 py-2 font-semibold text-orange-700 border-b">
                                    {{ number_format($company['company_expense'], 2) }}
                                </td>

                                <td class="px-4 py-2 font-semibold text-blue-700 border-b">
                                    {{ number_format($company['converted'], 2) }}
                                </td>

                                <td
                                    class="px-4 py-2 border-b font-bold
                                {{ $company['final_balance'] < 0 ? 'text-red-600' : 'text-green-700' }}">
                                    {{ number_format($company['final_balance'], 2) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-3 text-center text-gray-500">
                                    لا توجد بيانات متاحة.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </x-filament::section>
</x-filament-panels::page>
