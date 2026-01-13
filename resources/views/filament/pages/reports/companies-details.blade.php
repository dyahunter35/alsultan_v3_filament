<x-filament-panels::page>
    {{-- 1. قسم الفلترة (يختفي عند الطباعة) --}}
    <x-filament::section class="mb-4 shadow-sm no-print border-slate-200">
        <div class="flex flex-col gap-4 md:flex-row md:items-end">
            <div class="flex-1">
                {{-- فورم الفلترة الخاص بـ Filament --}}
                {{ $this->form }}
            </div>

            <div class="flex items-center gap-2">
                <x-filament::button wire:click="loadData" color="gray" icon="heroicon-m-arrow-path">
                    تحديث
                </x-filament::button>
            </div>
        </div>
    </x-filament::section>

    @if ($companyId)
        <div id="report-content" class="space-y-6 print:m-0">

            {{-- 2. الهيدر الموحد --}}
            <x-report-header label="تقرير تفصيلي للشركة:" :value="$company->name" />



            {{-- 4. كروت الملخص المالي والمعلومات --}}
            <div class="grid grid-cols-1 gap-4 md:grid-cols-3 print:grid-cols-3 print:gap-2">
                {{-- معلومات الشركة --}}
                <div class="p-4 bg-white border shadow-sm border-slate-200 rounded-xl print:p-2 print:border-slate-300">
                    <h3 class="pb-1 mb-2 text-xs font-bold uppercase border-b text-slate-400">بيانات التواصل</h3>
                    <dl class="space-y-1 text-sm print:text-[11px]">
                        <div class="flex justify-between">
                            <dt class="text-slate-500">الهاتف:</dt>
                            <dd class="font-bold tabular-nums">{{ $company->phone ?? '-' }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-slate-500">التصنيف:</dt>
                            <dd class="font-bold">{{ $company->type->getLabel() ?? '-' }}</dd>
                        </div>
                    </dl>
                </div>

                {{-- ملخص الأرصدة الموحد --}}
                <div
                    class="p-4 border shadow-sm bg-slate-50 border-slate-200 rounded-xl print:p-2 print:border-slate-300">
                    <h3 class="pb-1 mb-2 text-xs font-bold uppercase border-b text-slate-400">الوضع المالي الموحد</h3>
                    <dl class="space-y-1 text-sm print:text-[11px] tabular-nums">
                        <div class="flex justify-between">
                            <dt class="text-slate-500">إجمالي الوارد:</dt>
                            <dd class="font-bold text-green-700">{{ number_format($totals['total_in'] ?? 0, 2) }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-slate-500">إجمالي الصادر:</dt>
                            <dd class="font-bold text-red-700">{{ number_format($totals['total_out'] ?? 0, 2) }}</dd>
                        </div>
                        <div class="flex justify-between pt-1 mt-1 border-t border-dashed border-slate-300">
                            <dt class="font-black text-slate-800">الرصيد النهائي:</dt>
                            <dd
                                class="font-black text-lg print:text-sm {{ ($totals['balance'] ?? 0) < 0 ? 'text-red-600' : 'text-green-700' }}">
                                {{ number_format($totals['balance'] ?? 0, 2) }}
                            </dd>
                        </div>
                    </dl>
                </div>

                {{-- إحصائيات النشاط --}}
                <div class="p-4 bg-white border shadow-sm border-slate-200 rounded-xl print:p-2 print:border-slate-300">
                    <h3 class="pb-1 mb-2 text-xs font-bold uppercase border-b text-slate-400">حجم النشاط</h3>
                    <ul class="text-sm print:text-[11px] space-y-1">
                        <li class="flex justify-between"><span>شاحنات (شركة):</span> <span
                                class="font-bold tabular-nums">{{ $company->trucksAsCompany->count() }}</span></li>
                        <li class="flex justify-between"><span>شاحنات (مقاول):</span> <span
                                class="font-bold tabular-nums">{{ $company->trucksAsContractor->count() }}</span></li>
                        <li class="flex justify-between text-blue-600"><span>عدد المصروفات:</span> <span
                                class="font-bold tabular-nums">{{ $company->expenses->count() }}</span></li>
                    </ul>
                </div>
            </div>

            {{-- 5. جدول سجل العملات والمعاملات --}}
            <x-filament::section class="print:shadow-none print:border-slate-400">
                <x-slot name="heading">سجل العملات والمعاملات المكتملة</x-slot>
                <div class="overflow-x-auto">
                    <table class="w-full text-center border-collapse text-sm print:text-[10px]">
                        <thead class="text-white bg-slate-800">
                            <tr>
                                <th class="p-2 border border-slate-600">#</th>
                                <th class="p-2 border border-slate-600">التاريخ</th>
                                <th class="p-2 border border-slate-600">البيان / النوع</th>
                                <th class="p-2 border border-slate-600">القيمة</th>
                                <th class="p-2 border border-slate-600">المعامل</th>
                                <th class="p-2 border border-slate-600">المبلغ بالسوداني</th>
                                <th class="p-2 border border-slate-600">العملة</th>
                                <th class="p-2 px-4 text-right border border-slate-600">ملاحظات</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 tabular-nums">
                            @forelse($transactions as $t)
                                <tr class="transition-colors border-b hover:bg-slate-50">
                                    <td class="p-2 font-medium border border-slate-200 bg-slate-50 text-slate-500">
                                        {{ $t['id'] }}</td>
                                    <td class="p-2 border border-slate-200">{{ $t['date'] }}</td>
                                    <td class="p-2 italic font-bold border border-slate-200 text-slate-700">
                                        {{ $t['type']->getLabel() }}</td>
                                    <td class="p-2 font-black border border-slate-200 text-slate-900">
                                        {{ number_format($t['amount'], 2) }}</td>
                                    <td class="p-2 font-black border border-slate-200 text-slate-900">
                                        {{ number_format($t['rate'], 2) }}</td>
                                    <td class="p-2 font-black border border-slate-200 text-slate-900">
                                        {{ number_format($t['total'], 2) }}</td>
                                    <td class="p-2 font-bold text-blue-700 border border-slate-200">
                                        {{ $t['currency'] ?? '-' }}</td>
                                    <td
                                        class="p-2 px-4 text-xs italic text-right border border-slate-200 text-slate-500">
                                        {{ $t['note'] ?? '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="p-10 italic text-slate-400">لا توجد معاملات مسجلة حالياً
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-filament::section>

            {{-- 6. تفاصيل المصروفات والشاحنات (جنباً إلى جنب في A3) --}}
            <div class="grid grid-cols-1 gap-6 md:grid-cols-2 print:grid-cols-2 print:gap-4">
                {{-- كشف المصروفات --}}
                <div class="p-4 bg-white border shadow-sm border-slate-300 rounded-xl print:p-2">
                    <h3 class="pr-2 mb-3 font-black border-r-4 border-red-500 text-slate-800 print:text-sm">سجل آخر
                        المصروفات</h3>
                    <table class="w-full text-xs print:text-[9px]">
                        <tbody class="divide-y divide-slate-100">
                            @forelse($company->expenses->take(15) as $e)
                                <tr>
                                    <td class="py-1.5 italic text-slate-600">{{ $e->description ?? 'مصروف تشغيلي' }}
                                    </td>
                                    <td class="py-1.5 font-bold text-left tabular-nums text-red-700">
                                        {{ number_format($e->amount ?? 0, 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td class="py-2 text-slate-400">لا توجد مصروفات مرتبطة</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- سجل الشاحنات --}}
                <div class="p-4 bg-white border shadow-sm border-slate-300 rounded-xl print:p-2">
                    <h3 class="pr-2 mb-3 font-black border-r-4 border-blue-500 text-slate-800 print:text-sm">سجل
                        الشاحنات المرتبطة</h3>
                    <table class="w-full text-xs print:text-[9px]">
                        <tbody class="divide-y divide-slate-100">
                            @forelse($company->trucksAsCompany->merge($company->trucksAsContractor)->take(15) as $truck)
                                <tr>
                                    <td class="py-1.5">
                                        <span class="font-bold text-slate-700">{{ $truck->car_number }}</span>
                                        <span class="mx-1 text-slate-300">|</span>
                                        <span class="text-slate-600">{{ $truck->driver_name }}</span>
                                    </td>
                                    <td class="py-1.5 text-left text-slate-400 tabular-nums">{{ $truck->pack_date }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td class="py-2 text-slate-400">لا توجد شاحنات مسجلة</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- 3. ودجت العملات (تظهر في الطباعة) --}}
            <div class="print:mb-4">
                @livewire(\App\Filament\Resources\Companies\Widgets\CurrencyWidget::class, ['record' => $company], key('currency-widget-' . $companyId))
            </div>

            {{-- 7. ودجت النظرة العامة المالية (الخلاصة) --}}
            <div class="mt-6 print:mt-4 no-break">
                @livewire(\App\Filament\Resources\Companies\Widgets\CompanyFinanceOverview::class, ['record' => $company], key('finance-widget-' . $companyId))
            </div>

        </div>

        {{-- زر الطباعة العائم --}}
        <div class="fixed bottom-6 left-6 no-print">
            <x-print-button />
        </div>
    @else
        <div class="p-20 text-center bg-white border-2 border-gray-300 border-dashed shadow-sm rounded-xl">
            <x-filament::icon icon="heroicon-o-building-office-2" class="w-12 h-12 mx-auto mb-4 text-gray-300" />
            <h3 class="text-xl font-bold tracking-tight text-gray-400">الرجاء اختيار شركة من القائمة العلوية لتوليد
                التقرير</h3>
        </div>
    @endif

    <style>
        @media print {
            @page {
                size: A3 landscape;
                margin: 10mm;
            }

            .no-print {
                display: none !important;
            }

            .fi-main-ctn {
                padding: 0 !important;
                width: 100% !important;
            }

            body {
                background: white !important;
                -webkit-print-color-adjust: exact !important;
            }

            .tabular-nums {
                font-variant-numeric: tabular-nums;
            }

            .no-break {
                page-break-inside: avoid;
            }

            /* تحسين مظهر الودجات في الطباعة */
            .fi-wi-widget {
                border: 1px solid #e2e8f0 !important;
                box-shadow: none !important;
                margin-bottom: 1rem !important;
            }
        }
    </style>
</x-filament-panels::page>
