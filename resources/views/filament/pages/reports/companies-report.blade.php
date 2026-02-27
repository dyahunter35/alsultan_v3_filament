<x-filament-panels::page>
    {{-- 1. الهيدر الاحترافي --}}

    <x-filament::section id="report-content" class="print:shadow-none print:border-slate-300 print:p-0">
        {{-- 2. شريط الأدوات (يختفي عند الطباعة) --}}
        <x-report-header label="تقرير مالي شامل -" value="كشف أرصدة الشركات" />

        <div class="flex items-center justify-between mb-6 no-print">
            <div>

            </div>

            <div class="flex items-center gap-2">
                <x-filament::button wire:click="loadData" icon="heroicon-m-arrow-path" color="gray" size="sm">
                    تحديث البيانات
                </x-filament::button>
            </div>
        </div>

        {{-- 3. الجدول المالي الشامل --}}
        <div
            class="mt-4 overflow-x-auto bg-white border border-gray-200 rounded-xl print:border-slate-400 print:rounded-none">
            <table class="w-full text-center border-collapse divide-y divide-gray-200 text-[13px] print:text-[10px]">
                <thead class="text-white bg-slate-800 print:bg-slate-800">
                    <tr class="divide-x divide-x-reverse divide-slate-700">
                        <th class="px-3 py-3 font-bold border border-slate-600">#</th>
                        <th class="px-3 py-3 font-bold border border-slate-600 text-right min-w-[150px]">اسم الشركة</th>
                        <th class="px-3 py-3 font-bold border border-slate-600 bg-slate-700">المجموع الوارد</th>
                        <th class="px-3 py-3 font-bold border border-slate-600 bg-slate-700">المجموع الصادر</th>
                        <th class="px-3 py-3 font-bold border border-slate-600">المدفوع</th>
                        <th class="px-3 py-3 font-bold border border-slate-600">مصروفات الشركة</th>
                        <th class="px-3 py-3 font-bold text-blue-200 border border-slate-600">التحويلات (SDG)</th>
                        <th class="px-3 py-3 italic font-bold border border-slate-600 bg-slate-900">الرصيد (عام)</th>
                        <th class="px-3 py-3 italic font-bold border border-slate-600 bg-slate-900">الرصيد (صيغة)</th>
                        <th class="px-3 py-3 font-bold border border-slate-600">حركات</th>
                        <th class="px-3 py-3 font-bold text-left border border-slate-600">العملات</th>
                    </tr>
                </thead>

                <tbody class="bg-white divide-y divide-gray-200 tabular-nums">
                    @forelse($companies as $company)
                        <tr class="transition-colors border-b border-gray-300 hover:bg-slate-50">
                            <td class="px-2 py-2 font-bold text-gray-600 border border-gray-300 bg-gray-50">
                                {{ $company['id'] }}</td>
                            <td class="px-2 py-2 font-black text-right border border-gray-300 text-slate-800">
                                {{ $company['name'] }}</td>
                            <td class="px-2 py-2 font-bold text-green-700 border border-gray-300">
                                {{ number_format($company['total_in'], 2) }}</td>
                            <td class="px-2 py-2 font-bold text-red-700 border border-gray-300">
                                {{ number_format($company['total_out'], 2) }}</td>
                            <td class="px-2 py-2 font-medium border border-gray-300">
                                {{ number_format($company['paid'], 2) }}</td>
                            <td class="px-2 py-2 text-orange-700 border border-gray-300">
                                {{ number_format($company['company_expense'], 2) }}</td>
                            <td class="px-2 py-2 border border-gray-300 bg-blue-50/50">
                                {{ number_format($company['converted'], 2) }}</td>

                            {{-- رصيد عام --}}
                            <td
                                class="px-2 py-2 border border-gray-300 font-black text-[14px] print:text-[11px] {{ $company['generic_balance'] < 0 ? 'text-red-600 bg-red-50' : 'text-green-700 bg-green-50' }}">
                                {{ number_format($company['generic_balance'], 2) }}
                            </td>

                            {{-- رصيد صيغة --}}
                            <td
                                class="px-2 py-2 border border-gray-300 font-black text-[14px] print:text-[11px] {{ $company['formula_balance'] < 0 ? 'text-red-600 bg-red-50' : 'text-green-700 bg-green-50' }}">
                                {{ number_format($company['formula_balance'], 2) }}
                            </td>

                            <td class="px-2 py-2 text-gray-500 border border-gray-300">
                                {{ $company['transactions_count'] }}</td>
                            <td
                                class="px-2 py-2 border border-gray-300 text-left text-[10px] font-medium text-slate-400">
                                {{ implode(', ', $company['currencies'] ?: ['-']) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11"
                                class="px-4 py-10 text-sm italic tracking-widest text-center text-gray-400 uppercase bg-gray-50">
                                <x-filament::icon icon="heroicon-o-exclamation-circle"
                                    class="w-10 h-10 mx-auto mb-2 text-gray-300" />
                                لا توجد سجلات مالية متاحة حالياً
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            <x-print-button/>
        </div>

        {{-- 4. ذيل التقرير للطباعة --}}
        <div class="hidden mt-8 print:flex justify-between items-center px-4 border-t pt-4 text-[10px] text-slate-500">
            <div>نظام {{ __('app.name') }} - كشف الأرصدة الختامي</div>
            <div class="flex gap-10">
                <span>توقيع المحاسب: ..........................</span>
                <span>الختم الرسمي: ..........................</span>
            </div>
            <div>صفحة رقم: 1 من 1</div>
        </div>
    </x-filament::section>

    {{-- 5. إعدادات الطباعة المخصصة A3 Landscape --}}
    <style>
        @media print {
            @page {
                size: A3 landscape;
                margin: 10mm;
            }

            /* إخفاء واجهة فيلامينت */
            .no-print,
            .fi-sidebar,
            .fi-topbar,
            .fi-header,
            .fi-main-ctn>header {
                display: none !important;
            }

            .fi-main-ctn {
                padding: 0 !important;
                margin: 0 !important;
                width: 100% !important;
            }

            body {
                background: white !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            /* تحسين الجدول ليكون عريضاً وواضحاً */
            table {
                width: 100% !important;
                border-collapse: collapse !important;
                border: 1px solid #64748b !important;
            }

            th,
            td {
                padding: 4px 2px !important;
                border: 1px solid #94a3b8 !important;
                line-height: 1 !important;
            }

            .bg-slate-800 {
                background-color: #1e293b !important;
                color: white !important;
            }

            .bg-slate-700 {
                background-color: #334155 !important;
            }

            .bg-slate-900 {
                background-color: #0f172a !important;
            }

            .bg-green-50 {
                background-color: #f0fdf4 !important;
            }

            .bg-red-50 {
                background-color: #fef2f2 !important;
            }

            .tabular-nums {
                font-variant-numeric: tabular-nums;
            }
        }
    </style>
</x-filament-panels::page>
