<x-filament-panels::page>
    {{-- 1. قسم الفلاتر (يختفي عند الطباعة) --}}
    <x-filament::section class="mb-4 no-print shadow-sm border-slate-200">
        <div class="flex flex-col gap-4 md:flex-row md:items-end">
            <div class="flex-1">
                {{ $this->form }}
            </div>

            <div class="flex items-center gap-2">
                <x-filament::button wire:click="refreshQty" color="gray" icon="heroicon-m-arrow-path">
                    تحديث الكميات
                </x-filament::button>
            </div>
        </div>
    </x-filament::section>

    @if ($products)
    <div id="report-content" class="space-y-6 print:m-0">
        
        {{-- 2. الهيدر الموحد الخاص بك --}}
        <x-report-header :label="$this->getTitle()" />

        <div class="overflow-hidden bg-white border shadow-sm rounded-xl print:border-slate-800 print:rounded-none">
            
            <div class="p-4 bg-slate-50/50 border-b border-gray-200 flex justify-between items-center print:py-2">
                <div>
                    <h2 class="text-lg font-bold text-slate-800 print:text-sm">سجل جرد المنتجات التراكمي</h2>
                    <p class="text-xs text-slate-500">توزيع المخزون على كافة الفروع المتاحة</p>
                </div>
                <div class="text-left font-mono text-xs text-slate-400">
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm text-right text-gray-600 border-collapse">
                    <thead class="text-xs text-white uppercase bg-slate-800">
                        <tr>
                            <th scope="col" class="px-6 py-4 font-bold border-l border-slate-700">اسم المنتج</th>
                            @foreach ($branches as $branch)
                                <th scope="col" class="px-6 py-4 font-bold text-center border-l border-slate-700">
                                    {{ $branch->name }}
                                </th>
                            @endforeach
                            <th scope="col" class="px-6 py-4 font-black text-center bg-slate-900 text-green-400">
                                المجموع الكلي
                            </th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-200 tabular-nums">
                        @foreach ($products as $product)
                            <tr class="transition-colors duration-200 bg-white hover:bg-slate-50">
                                <td class="px-6 py-4 font-bold text-slate-900 border-l border-slate-100 whitespace-nowrap">
                                    {{ $product->name }}
                                </td>
                                
                                @foreach ($branches as $branch)
                                    @php
                                        // البحث عن الكمية في الفرع
                                        $branchPivot = $product->branches->firstWhere('id', $branch->id);
                                        $quantityInBranch = $branchPivot?->pivot->total_quantity ?? 0;
                                    @endphp
                                    <td class="px-6 py-4 text-center border-l border-slate-50 {{ $quantityInBranch <= 0 ? 'text-slate-300' : 'text-slate-700' }}">
                                        {{ number_format($quantityInBranch) }}
                                    </td>
                                @endforeach

                                <td class="px-6 py-4 font-black text-center text-slate-900 bg-slate-50/50">
                                    {{ number_format($product->totalStock ?? 0) }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>

                    {{-- تذييل الجدول لحساب إجمالي كل فرع --}}
                    <tfoot class="bg-slate-800 text-white font-bold">
                        <tr>
                            <td class="px-6 py-3 border-l border-slate-700">إجمالي المخزون</td>
                            @foreach ($branches as $branch)
                                <td class="px-6 py-3 text-center border-l border-slate-700">
                                    {{-- ملاحظة: تحتاج لحساب مجموع العمود في الكنترولر أو تمريره --}}
                                    {{ number_format($products->sum(fn($p) => $p->branches->firstWhere('id', $branch->id)?->pivot->total_quantity ?? 0)) }}
                                </td>
                            @endforeach
                            <td class="px-6 py-3 text-center bg-slate-900 text-green-400">
                                {{ number_format($products->sum('totalStock')) }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        {{-- توقيعات الطباعة --}}
        <div class="hidden print:flex justify-between px-10 mt-12 text-xs italic text-slate-500">
            <p>توقيع أمين المخزن: ............................</p>
            <p>اعتماد الإدارة: ............................</p>
        </div>
    </div>

    <x-print-button />
    @else
        <div class="p-20 text-center bg-white border-2 border-gray-300 border-dashed shadow-sm rounded-xl">
            <x-filament::icon icon="heroicon-o-archive-box-x-mark" class="w-12 h-12 mx-auto mb-4 text-gray-300" />
            <h3 class="text-xl font-bold tracking-tight text-gray-400">لا توجد بيانات متوفرة للمنتجات حالياً</h3>
        </div>
    @endif

    <style>
        /* الخط المخصص الذي تستخدمه */
        @font-face {
            font-family: 'FlatJooza';
            src: url('{{ asset('fonts/flat-jooza-regular.woff2') }}') format('woff2');
        }

        #report-content {
            font-family: 'FlatJooza', sans-serif;
        }

        @media print {
            @page {
                size: A4 landscape; /* التقرير العرضي أفضل لكثرة الفروع */
                margin: 10mm;
            }

            .no-print {
                display: none !important;
            }

            body {
                background: white !important;
                color: black;
            }

            .tabular-nums {
                font-variant-numeric: tabular-nums;
            }
        }
    </style>
</x-filament-panels::page>