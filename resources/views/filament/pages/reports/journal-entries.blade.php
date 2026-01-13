<x-filament-panels::page>
    {{-- 1. قسم الفلترة (يختفي عند الطباعة) --}}
    <x-filament::section class="mb-4 shadow-sm no-print border-slate-200">
        <div class="flex flex-col gap-4 md:flex-row md:items-end">
            <div class="flex-1">
                {{-- فورم الفلترة الخاص بك (التاريخ، العملة، إلخ) --}}
                {{ $this->form }}
            </div>

            <div class="flex items-center gap-2">
                <x-filament::button wire:click="loadData" color="gray" icon="heroicon-m-arrow-path">
                    تحديث البيانات
                </x-filament::button>

            </div>
        </div>
    </x-filament::section>

    <div class="space-y-6 print:m-0" id="report-content">

        {{-- 2. الهيدر الرسمي الموحد --}}
        <x-report-header label="كشف قيود اليومية التفصيلي" :value="now()->format('Y-m-d')" />

        {{-- 3. كروت الخلاصة المالية (Overview) --}}
        <div class="grid grid-cols-1 gap-4 md:grid-cols-3 print:grid-cols-3 print:gap-2">

            {{-- الرصيد المرحل --}}
            <div class="p-4 border shadow-sm bg-slate-50 rounded-xl print:p-2">
                <div class="text-xs font-bold text-slate-500">الرصيد المرحل (افتتاحي)</div>
                <div
                    class="text-xl font-black tabular-nums {{ $this->getOpeningBalance() >= 0 ? 'text-green-600' : 'text-red-600' }}">
                    {{ number_format($this->getOpeningBalance(), 2) }}
                </div>
            </div>

            {{-- ربح / خسارة اليوم --}}
            <div class="p-4 bg-white border shadow-sm rounded-xl print:p-2">
                <div class="text-xs font-bold text-slate-500">حركة اليوم (صافي)</div>
                <div
                    class="text-xl font-black tabular-nums {{ $this->getTodayProfit() >= 0 ? 'text-blue-600' : 'text-orange-600' }}">
                    {{ number_format($this->getTodayProfit(), 2) }}
                </div>
            </div>

            {{-- الصافي النهائي --}}
            <div class="p-4 border shadow-sm bg-slate-800 rounded-xl print:p-2 print:bg-slate-100">
                <div class="text-xs font-bold text-slate-400 print:text-slate-600">الرصيد الختامي</div>
                <div
                    class="text-xl font-black tabular-nums {{ $this->getFinalBalance() >= 0 ? 'text-green-400 print:text-green-700' : 'text-red-400 print:text-red-700' }}">
                    {{ number_format($this->getFinalBalance(), 2) }}
                </div>
            </div>
        </div>

        {{-- 4. جدول قيود اليومية المصمم للـ A3 --}}
        <x-filament::section class="print:shadow-none print:border-slate-800">
            <x-slot name="heading">تفاصيل القيود والحركات</x-slot>

            <div class="overflow-x-auto">
                <table class="w-full text-center border-collapse text-sm print:text-[11px]">
                    <thead>
                        <tr class="font-bold border-b-2 bg-slate-100 text-slate-700 border-slate-800">
                            <th class="p-3 border">النوع</th>
                            <th class="p-3 text-right border">الوصف والبيان</th>
                            <th class="p-3 text-red-700 border bg-red-50">مدين (-)</th>
                            <th class="p-3 text-green-700 border bg-green-50">دائن (+)</th>
                            <th class="p-3 border">الوقت</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y tabular-nums">
                        @foreach ($this->getJournalEntries() as $item)
                            <tr class="transition-colors border-b hover:bg-slate-50">
                                <td class="p-2 font-bold border text-slate-500">{{ $item->type }}</td>
                                <td class="p-2 px-4 text-right border">{{ $item->description }}</td>
                                <td class="p-2 font-medium text-red-600 border bg-red-50/30">
                                    {{ $item->debit != 0 ? number_format($item->debit, 2) : '-' }}
                                </td>
                                <td class="p-2 font-medium text-green-600 border bg-green-50/30">
                                    {{ $item->credit != 0 ? number_format($item->credit, 2) : '-' }}
                                </td>
                                <td class="p-2 text-xs border text-slate-400">
                                    {{ is_string($item->created_at) ? $item->created_at : $item->created_at->format('H:i:s') }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>

                    {{-- صف المجموع النهائي للجدول --}}
                    <tfoot class="font-bold text-white bg-slate-800">
                        <tr>
                            <td colspan="2" class="p-2 px-4 italic text-left">إجمالي الحركات</td>
                            <td class="p-2 bg-red-900 border border-slate-700">
                                {{ number_format($this->getJournalEntries()->sum('debit'), 2) }}
                            </td>
                            <td class="p-2 bg-green-900 border border-slate-700">
                                {{ number_format($this->getJournalEntries()->sum('credit'), 2) }}
                            </td>
                            <td class="bg-slate-800"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </x-filament::section>

        {{-- تذييل الصفحة للطباعة --}}
        <div class="justify-between hidden px-6 mt-12 text-xs italic print:flex text-slate-400">
            <p>طُبع بواسطة نظام الإدارة - {{ now()->format('Y-m-d H:i') }}</p>
            <p>صفحة 1 من 1</p>
        </div>

    </div>

    <div class="no-print">
        <x-print-button />
    </div>

    <style>
        @media print {
            @page {
                size: A3 portrait;
                /* أو landscape حسب الرغبة */
                margin: 15mm;
            }

            .no-print {
                display: none !important;
            }

            body {
                background: white !important;
            }

            table {
                width: 100% !important;
                border: 1px solid #000 !important;
            }

            th,
            td {
                border: 1px solid #ccc !important;
            }
        }
    </style>
</x-filament-panels::page>
