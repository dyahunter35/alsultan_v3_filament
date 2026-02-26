<x-filament-panels::page>
    <div class="no-print">
        <x-filament::section class="mb-4 shadow-sm border-slate-200">
            {{ $this->form }}
        </x-filament::section>
    </div>

    <div id="report-content" class="bg-white p-2 print:p-0">
        {{-- Header Section - محاذاة رسمية --}}
            <x-report-header label="بيان حركة الشحن والتخليص" :value="$status_label" />

        <div class="flex justify-between items-start mb-6 border-b-4 border-slate-900 pb-4">
            <div class="w-1/3">
                <h1 class="text-2xl font-black text-slate-900 leading-tight tracking-tighter uppercase">
                    بيان حركة الشحن والتخليص
                </h1>
                <p class="text-[10px] text-slate-500 font-bold uppercase tracking-widest mt-1">
                    Logistics & Customs Clearance Manifest
                </p>
            </div>
            
            <div class="w-1/3 text-center">
                <div class="inline-block border-2 border-slate-900 p-2 rounded-sm bg-slate-50">
                    <span class="text-[9px] block font-bold text-slate-400">إجمالي عدد الشاحنات</span>
                    <span class="text-xl font-black text-slate-900 tabular-nums">{{ $trucks->count() }}</span>
                </div>
            </div>

            <div class="w-1/3 text-left">
                <p>-</p>
                <p class="text-[10px] font-bold text-slate-700 leading-none mt-1">وقت التقرير: {{ now()->format('H:i') }}</p>
                <div class="mt-2 text-[9px] bg-slate-900 text-white px-2 py-0.5 inline-block uppercase font-bold">Official Document</div>
            </div>
        </div>
        
        {{-- Table Section --}}
        <div class="overflow-x-auto border-t-2 border-slate-950">
            <table class="w-full text-[11px] text-center border-collapse border border-slate-950 print:text-[10px]">
                <thead>
                    {{-- Row 1: High Level Categories --}}
                    <tr class="bg-slate-950 text-white">
                        <th class="p-2 border border-slate-700 w-12" rowspan="2">#</th>
                        <th class="p-2 border border-slate-700" rowspan="2">رقم الشحنة</th>
                        <th class="p-2 border border-slate-700 bg-slate-800" colspan="3">بيانات الناقل والشاحنة</th>
                        <th class="p-2 border border-slate-700 bg-emerald-900" colspan="7">تفاصيل الحمولة والمسار الجمركي</th>
                    </tr>
                    {{-- Row 2: Detail Headers --}}
                    <tr class="bg-slate-100 font-bold border-b-2 border-slate-950 text-slate-900">
                        <th class="p-1.5 border border-slate-400">المقاول</th>
                        <th class="p-1.5 border border-slate-400">النوع</th>
                        <th class="p-1.5 border border-slate-400">رقم اللوحة</th>

                        <th class="p-1.5 border border-slate-400 bg-emerald-50">نوع الحمولة</th>
                        <th class="p-1.5 border border-slate-400 bg-emerald-50">المصنع</th>
                        <th class="p-1.5 border border-slate-400 bg-emerald-50">موقع التحميل</th>
                        <th class="p-1.5 border border-slate-400 bg-emerald-50 text-blue-800">تاريخ الشحن</th>
                        <th class="p-1.5 border border-slate-400 bg-emerald-50 text-rose-800">تاريخ التفريغ</th>
                        <th class="p-1.5 border border-slate-400 bg-emerald-50">وجهة التفريغ</th>
                        <th class="p-1.5 border border-slate-400 bg-amber-50 font-black">المعبر الجمركي</th>
                    </tr>
                </thead>
                <tbody class="tabular-nums font-medium text-slate-900">
                    @forelse ($trucks as $index => $truck)
                        <tr class="hover:bg-slate-50 transition-colors border-b border-slate-300">
                            <td class="p-2 border-x border-slate-300 bg-slate-50/50 text-slate-400">{{ $index + 1 }}</td>
                            <td class="p-2 border-x border-slate-300 font-black text-slate-900 uppercase">{{ $truck->code ?? $truck->id }}</td>
                            <td class="p-2 border-x border-slate-200 text-right font-bold">{{ $truck->contractorInfo?->name ?? 'N/A' }}</td>
                            <td class="p-2 border-x border-slate-200 text-[9px] text-slate-500">{{ $truck->truck_model ?? 'تريلة 13 متر' }}</td>
                            <td class="p-2 border-x border-slate-200 font-black tracking-widest">{{ $truck->car_number }}</td>

                            <td class="p-2 border-x border-slate-200 font-bold">{{ $truck->category?->name }}</td>
                            <td class="p-2 border-x border-slate-200 text-[10px]">{{ $truck->companyId?->name ?? 'N/A' }}</td>
                            <td class="p-2 border-x border-slate-200">{{ $truck->city }}</td>
                            <td class="p-2 border-x border-slate-200 text-blue-700 font-bold">{{ $truck->pack_date?->format('Y-m-d') }}</td>
                            <td class="p-2 border-x border-slate-200 text-rose-700 font-bold">{{ $truck->arrive_date?->format('Y-m-d') ?? '-' }}</td>
                            <td class="p-2 border-x border-slate-200 bg-slate-50/50 font-bold">{{ $truck->toBranch?->name }}</td>
                            <td class="p-2 border-x border-slate-200 bg-amber-50/50 font-black text-amber-900 underline decoration-slate-400">{{ $truck->from?->name }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="12" class="p-20 text-center bg-slate-50">
                                <x-filament::icon icon="heroicon-o-truck" class="w-12 h-12 mx-auto text-slate-300 mb-2"/>
                                <span class="text-slate-400 italic">لا توجد بيانات متاحة لهذا النطاق من البحث</span>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Signature Section - نمط الأختام الرسمية --}}
        <div class="hidden print:grid grid-cols-4 gap-4 mt-16 px-4">
            <div class="text-center border-t-2 border-slate-200 pt-2">
                <span class="text-[9px] font-bold text-slate-400 uppercase block mb-12">ضابط الحركة / Dispatcher</span>
                <div class="border-b border-dotted border-slate-300 w-3/4 mx-auto"></div>
            </div>
            <div class="text-center border-t-2 border-slate-200 pt-2">
                <span class="text-[9px] font-bold text-slate-400 uppercase block mb-12">التخليص الجمركي / Customs</span>
                <div class="border-b border-dotted border-slate-300 w-3/4 mx-auto"></div>
            </div>
            <div class="text-center border-t-2 border-slate-200 pt-2">
                <span class="text-[9px] font-bold text-slate-400 uppercase block mb-12">مدير العمليات / Ops Manager</span>
                <div class="border-b border-dotted border-slate-300 w-3/4 mx-auto"></div>
            </div>
            <div class="text-center border-t-2 border-slate-900 pt-2 bg-slate-50">
                <span class="text-[9px] font-black text-slate-900 uppercase block mb-12">ختم الإدارة / Official Stamp</span>
            </div>
        </div>

        <div class="fixed bottom-6 left-6 no-print">
            <x-print-button/>
        </div>
    </div>

    <style>
        #report-content { font-family: 'FlatJooza', sans-serif; }
        @media print {
            @page { size: A3 landscape; margin: 8mm; }
            .no-print { display: none !important; }
            body { background: white !important; }
            .fi-main-ctn { padding: 0 !important; margin: 0 !important; width: 100% !important; }
            table { border-collapse: collapse !important; border: 2px solid #000 !important; }
            th, td { border: 1px solid #000 !important; }
            .bg-slate-950 { background-color: #020617 !important; color: white !important; -webkit-print-color-adjust: exact; }
            .bg-emerald-900 { background-color: #064e3b !important; color: white !important; -webkit-print-color-adjust: exact; }
            .bg-slate-800 { background-color: #1e293b !important; color: white !important; -webkit-print-color-adjust: exact; }
            .bg-amber-50 { background-color: #fffbeb !important; -webkit-print-color-adjust: exact; }
            .bg-emerald-50 { background-color: #ecfdf5 !important; -webkit-print-color-adjust: exact; }
        }
    </style>
</x-filament-panels::page>