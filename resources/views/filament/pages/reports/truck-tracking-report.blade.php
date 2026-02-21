<x-filament-panels::page>
    <div class="no-print">
        <x-filament::section class="mb-4 shadow-sm border-slate-200">
            {{ $this->form }}
        </x-filament::section>
    </div>

    <div id="report-content" class="p-6 bg-white shadow rounded-xl print:shadow-none print:p-0">
        {{-- Header Section --}}
        <x-report-header label="اعمال الشحن والتخليص الجمركي"/>
        
        {{-- Table Section --}}
        <div class="overflow-x-auto">
            <table class="w-full text-[12px] text-center border-collapse border border-slate-800 print:text-[10px]">
                <thead>
                    <tr class="bg-slate-100 font-bold border border-slate-800">
                        <th class="p-2 border border-slate-800" rowspan="2">رقم الشحنة</th>
                        <th class="p-2 border border-slate-800" colspan="3">بيانات الشاحنة</th>
                        <th class="p-2 border border-slate-800 bg-green-50" colspan="7">بيانات الحمولة</th>
                    </tr>
                    <tr class="bg-slate-800 text-white font-bold border border-slate-800">
                        <th class="p-1 border border-slate-600">اسم مقاول الشحن</th>
                        <th class="p-1 border border-slate-600">نوع الشاحنة</th>
                        <th class="p-1 border border-slate-600">رقم اللوحة</th>

                        <th class="p-1 border border-slate-600 bg-green-900">نوع الحمولة</th>
                        <th class="p-1 border border-slate-600 bg-green-900">المصنع</th>
                        <th class="p-1 border border-slate-600 bg-green-900">موقع التحميل</th>
                        <th class="p-1 border border-slate-600 bg-green-900">تاريخ الشحن</th>
                        <th class="p-1 border border-slate-600 bg-green-900">تاريخ التفريغ</th>
                        <th class="p-1 border border-slate-600 bg-green-900">موقع التفريغ</th>
                        <th class="p-1 border border-slate-600 bg-green-900">المعبر</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($trucks as $truck)
                        <tr class="hover:bg-slate-50 transition-colors bg-white">
                            <td class="p-2 border border-slate-500 font-bold">{{ $truck->code ?? $truck->id }}</td>
                            <td class="p-2 border border-slate-300">{{ $truck->contractorInfo?->name ?? 'N/A' }}</td>
                            <td class="p-2 border border-slate-300 text-[10px]">{{ $truck->truck_model ?? 'تريلة 13 متر' }}
                            </td>
                            <td class="p-2 border border-slate-300 font-mono">{{ $truck->car_number }}</td>

                            {{-- Cargo Data --}}
                            <td class="p-2 border border-slate-300">
                                {{ $truck->category?->name }}
                                </td>
                            <td class="p-2 border border-slate-300">{{ $truck->companyId?->name ?? 'N/A' }}</td>
                            <td class="p-2 border border-slate-300">{{ $truck->city }}</td>
                            <td class="p-2 border border-slate-300 tabular-nums">{{ $truck->pack_date?->format('Y-m-d') }}
                            </td>
                            <td class="p-2 border border-slate-300 tabular-nums">
                                {{ $truck->arrive_date?->format('Y-m-d') ?? '-' }}</td>

                            {{-- Discharge Location with Highlight --}}
                            @php
                                $to = $truck->toBranch?->name;
                                $isHighlighted = '';//str_contains($to, 'الدبة') || str_contains($to, 'عطبرة');
                            @endphp
                            <td class="p-2 border border-slate-300 {{ $isHighlighted ? 'bg-yellow-300 font-bold' : '' }}">
                                {{ $to }}
                            </td>

                            <td class="p-2 border border-slate-300 font-bold text-blue-900">{{ $truck->from?->name }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11" class="p-10 text-center text-gray-400 italic">لا توجد بيانات متاحة حالياً</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <x-print-button/>

        {{-- Signature Section --}}
        <div class="hidden print:grid grid-cols-2 gap-20 px-10 mt-12">
            <div class="pt-2 text-center border-t-2 border-slate-300 font-bold">
                توقيع المحاسب
            </div>
            <div class="pt-2 text-center border-t-2 border-slate-300 font-bold">
                ختم الشركة
            </div>
        </div>
    </div>

    <style>
        @media print {
            @page {
                size: A3 landscape;
                margin: 10mm;
            }

            .no-print {
                display: none !important;
            }

            body {
                background: white !important;
            }

            .fi-main-ctn {
                padding: 0 !important;
                margin: 0 !important;
                width: 100% !important;
            }

            table {
                border-color: #000 !important;
            }

            th,
            td {
                border-color: #000 !important;
            }
        }
    </style>
</x-filament-panels::page>