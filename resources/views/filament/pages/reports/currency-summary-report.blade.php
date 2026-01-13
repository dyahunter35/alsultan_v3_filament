<x-filament-panels::page>
    {{-- 1. قسم الفلاتر (يختفي عند الطباعة) --}}
    <div class="mb-6 no-print">
        {{ $this->form }}
    </div>

    @if ($ledger && $ledger->count())
        <div id="report-content" class="print:m-0 print:p-0">

            {{-- 2. الهيدر الموحد --}}
            <x-report-header :label="$this->getHeading()" />

            {{-- 3. لوحة التحكم في أسعار الصرف (مهمة جداً للحساب اللحظي) --}}
            <x-filament::section class="mb-4 no-print">
                <x-slot name="heading">
                    <span class="flex items-center gap-2 text-sm">
                        <x-filament::icon icon="heroicon-m-calculator" class="w-4 h-4 text-primary-600" />
                        تعديل أسعار صرف العملات للتقرير الحالي
                    </span>
                </x-slot>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-center border rounded-lg border-slate-200">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="p-2 border">العملة</th>
                                <th class="p-2 border">جنية سوداني (مرجع)</th>
                                @foreach ($currencies as $case)
                                    <th class="p-2 border">{{ $case->name }}</th>
                                @endforeach
                                <th class="p-2 border bg-blue-50">الإجمالي المحول</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="p-2 italic font-bold bg-slate-50">سعر الصرف</td>
                                <td class="p-2 border">
                                    <input type="number" wire:model.live="keys.sd"
                                        class="w-20 text-center border-gray-300 rounded-md shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm"
                                        min="0.01" step="0.01">
                                </td>
                                @foreach ($currencies as $case)
                                    <td class="p-2 border">
                                        <input type="number" wire:model.live="keys.{{ $case->code }}"
                                            class="w-24 text-center border-gray-300 rounded-md shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm"
                                            min="0.01" step="0.01">
                                    </td>
                                @endforeach
                                <td class="p-2 text-xs italic border bg-blue-50 text-slate-400">بالوحدة المرجعية</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </x-filament::section>

            {{-- 4. جدول الأرصدة الرئيسي --}}
            <div
                class="overflow-x-auto bg-white border border-slate-300 rounded-xl print:border-slate-800 print:rounded-none">
                <table class="w-full text-center border-collapse text-[13px] print:text-[11px]">
                    <thead>
                        <tr class="font-bold text-white bg-slate-800 print:bg-slate-800">
                            <th class="px-3 py-3 border border-slate-700">#</th>
                            <th class="px-3 py-3 border border-slate-700 text-right min-w-[150px]">
                                {{ $type == 'companies' ? 'الشركة' : 'العميل' }}</th>
                            <th class="px-3 py-3 border border-slate-700 bg-slate-700">جنية سوداني</th>
                            @foreach ($currencies as $currency)
                                <th class="px-3 py-3 border border-slate-700">{{ $currency->name }}</th>
                            @endforeach
                            <th class="px-3 py-3 italic text-white bg-blue-700 border border-slate-700">الإجمالي المحول
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 tabular-nums">
                        @foreach ($ledger as $row)
                            <tr class="transition-colors hover:bg-slate-50">
                                <td class="px-2 py-2 border border-slate-200 bg-slate-50 text-slate-500">
                                    {{ $row->id }}</td>
                                <td class="px-2 py-2 font-black text-right border border-slate-200 text-slate-800">
                                    {{ $row->name }}</td>

                                {{-- رصيد الجنيه --}}
                                <td class="px-2 py-2 font-bold text-green-700 border border-slate-200">
                                    {{ number_format($row->balance, 2) }}
                                </td>

                                {{-- أرصدة العملات الأخرى --}}
                                @foreach ($currencies as $currency)
                                    <td class="px-2 py-2 font-bold text-blue-600 border border-slate-200">
                                        {{ number_format($row->currencyValue($currency->id), 2) }}
                                    </td>
                                @endforeach

                                {{-- الإجمالي المحول --}}
                                <td class="px-2 py-2 font-black text-blue-900 border border-slate-300 bg-blue-50/50">
                                    {{ number_format($this->total[$row->id] ?? 0, 2) }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="font-black border-t-2 bg-slate-100 border-slate-800">
                        <tr>
                            <td colspan="2"
                                class="p-3 px-6 tracking-wider text-left uppercase border border-slate-300">المجموع
                                الكلي</td>
                            <td class="p-3 text-green-800 border border-slate-300 bg-green-50">
                                {{ number_format($ledger->sum('balance'), 2) }}</td>

                            @foreach ($currencies as $currency)
                                <td class="p-3 text-blue-800 border border-slate-300">
                                    {{-- ملاحظة: يمكنك هنا إضافة مجموع كل عملة إذا كان الـ Collection يدعم ذلك --}}
                                    -
                                </td>
                            @endforeach

                            <td class="p-3 text-lg text-white bg-blue-600 border border-slate-800 print:text-sm">
                                {{ number_format($this->total_converted ?? 0, 2) }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            {{-- 5. تذييل التقرير --}}
            <div class="justify-between hidden px-6 mt-12 print:flex">
                <div class="w-64 pt-2 text-center border-t border-slate-400">
                    <p class="font-bold">توقيع المحاسب المالي</p>
                </div>
                <div class="w-64 pt-2 text-center border-t border-slate-400">
                    <p class="font-bold">ختم الإدارة</p>
                </div>
            </div>
        </div>

        <div class="fixed bottom-6 left-6 no-print">
            <x-print-button />
        </div>
    @else
        <div class="p-20 text-center bg-white border-2 border-gray-300 border-dashed shadow rounded-xl">
            <x-filament::icon icon="heroicon-o-currency-dollar" class="w-12 h-12 mx-auto mb-4 text-gray-300" />
            <h3 class="text-xl font-bold text-gray-400">لا توجد حركات مالية مسجلة لهذه الفئة حالياً</h3>
        </div>
    @endif

    <style>
        /* الخط المخصص الذي طلبته */
        @font-face {
            font-family: 'FlatJooza';
            src: url('{{ asset('fonts/flat-jooza-regular.woff2') }}') format('woff2');
        }

        #report-content {
            font-family: 'FlatJooza', Amiri, sans-serif;
        }

        @media print {
            @page {
                size: A3 landscape;
                margin: 12mm;
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

            table {
                border-collapse: collapse !important;
                width: 100% !important;
            }

            th,
            td {
                border: 1px solid #000 !important;
            }

            .tabular-nums {
                font-variant-numeric: tabular-nums;
            }
        }
    </style>
</x-filament-panels::page>
