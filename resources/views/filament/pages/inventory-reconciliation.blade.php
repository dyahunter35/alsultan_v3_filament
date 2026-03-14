<x-filament-panels::page>
    {{-- قسم الفلترة - بتصميم أنظف --}}
    <div class="no-print">
        <x-filament::section class="mb-6 border-slate-200 shadow-sm ring-1 ring-slate-950/5">
            {{ $this->form }}
        </x-filament::section>
    </div>

    @if($branchId)
        <div class="space-y-4">
            {{-- الهيدر العلوي للجدول --}}
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 px-2">
                <div>
                    <h3 class="text-lg font-bold tracking-tight text-slate-950 dark:text-white flex items-center gap-2">
                        <x-filament::icon icon="heroicon-m-clipboard-document-check" class="w-5 h-5 text-primary-500" />
                        مطابقة مخزون الفرع
                    </h3>
                    <p class="text-xs text-slate-500 mt-1">قم بإدخال الكميات الفعلية لمطابقتها مع الكميات المسجلة في النظام.</p>
                </div>
                
                <div class="flex items-center gap-3">
                    <x-filament::button 
                        wire:click="updateQuantities" 
                        color="gray" 
                        variant="outline"
                        icon="heroicon-o-arrow-path"
                        class="shadow-sm bg-white"
                    >
                        تحديث البيانات
                    </x-filament::button>
                </div>
            </div>

            {{-- حاوية الجدول --}}
            <div class="bg-white dark:bg-gray-900 border border-slate-200 dark:border-gray-800 rounded-xl shadow-sm overflow-hidden transition-all">
                <div class="overflow-x-auto overflow-y-visible">
                    <table class="w-full text-right text-sm">
                        <thead>
                            <tr class="bg-slate-50/80 dark:bg-gray-800/50 text-slate-500 dark:text-gray-400 border-b border-slate-200 dark:border-gray-800">
                                <th class="p-4 font-bold w-12 text-center">#</th>
                                <th class="p-4 font-bold">المنتج</th>
                                <th class="p-4 font-bold text-center w-36">كمية النظام</th>
                                <th class="p-4 font-bold text-center w-48">الكمية الفعلية</th>
                                <th class="p-4 font-bold text-center w-32">الفرق</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-gray-800">
                            @forelse($productsData as $index => $product)
                                @php
                                    $sysQty = (float) $product['system_quantity'];
                                    $actualQty = isset($actualQuantities[$product['id']]) && $actualQuantities[$product['id']] !== '' ? (float) $actualQuantities[$product['id']] : $sysQty;
                                    $diff = $actualQty - $sysQty;
                                @endphp
                                <tr class="hover:bg-slate-50/50 dark:hover:bg-gray-800/30 transition-colors group">
                                    <td class="p-4 text-center text-slate-400 font-mono text-xs">{{ $index + 1 }}</td>
                                    <td class="p-4">
                                        <div class="flex flex-col">
                                            <span class="font-bold text-slate-700 dark:text-gray-200 group-hover:text-primary-600 transition-colors">
                                                {{ $product['name'] }}
                                            </span>
                                            <span class="text-[10px] text-slate-400 font-mono tracking-tighter uppercase">ID: #{{ $product['id'] }}</span>
                                        </div>
                                    </td>
                                    <td class="p-4 text-center">
                                        <div class="inline-flex items-center px-2.5 py-1 rounded-full bg-slate-100 dark:bg-gray-800 text-slate-600 dark:text-gray-400 font-mono text-xs font-semibold tabular-nums">
                                            {{ $sysQty }}
                                        </div>
                                    </td>
                                    <td class="p-4">
                                        <div class="relative max-w-[160px] mx-auto">
                                            <input type="number" step="any"
                                                wire:model.live.debounce.500ms="actualQuantities.{{ $product['id'] }}"
                                                class="block w-full text-center py-2 px-3 text-sm border-slate-200 dark:border-gray-700 dark:bg-gray-950 rounded-lg shadow-sm focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all font-mono tabular-nums @if($diff != 0) ring-1 ring-primary-500/50 @endif">
                                        </div>
                                    </td>
                                    <td class="p-4 text-center font-mono tabular-nums">
                                        @if($diff > 0)
                                            <div class="flex items-center justify-center gap-1 text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-900/20 py-1.5 px-2 rounded-lg border border-emerald-100 dark:border-emerald-800 font-bold">
                                                <x-filament::icon icon="heroicon-m-plus-small" class="w-4 h-4" />
                                                {{ $diff }}
                                            </div>
                                        @elseif($diff < 0)
                                            <div class="flex items-center justify-center gap-1 text-rose-600 dark:text-rose-400 bg-rose-50 dark:bg-rose-900/20 py-1.5 px-2 rounded-lg border border-rose-100 dark:border-rose-800 font-bold">
                                                <x-filament::icon icon="heroicon-m-minus-small" class="w-4 h-4" />
                                                {{ abs($diff) }}
                                            </div>
                                        @else
                                            <span class="text-slate-300 dark:text-gray-600 font-medium">—</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="p-12 text-center">
                                        <div class="flex flex-col items-center gap-2">
                                            <x-filament::icon icon="heroicon-o-archive-box-x-mark" class="w-10 h-10 text-slate-200" />
                                            <p class="text-slate-400 italic">لا توجد منتجات مسجلة في هذا الفرع.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- تذييل الجدول - شريط الإجراءات --}}
                @if(count($productsData) > 0)
                    <div class="p-4 border-t border-slate-100 dark:border-gray-800 bg-slate-50/50 dark:bg-gray-800/30 flex items-center justify-between gap-4">
                        <div class="text-xs text-slate-500">
                             تم العثور على <span class="font-bold text-slate-700 dark:text-gray-300">{{ count($productsData) }}</span> منتج.
                        </div>
                        <x-filament::button 
                            wire:click="saveReconciliation" 
                            size="md"
                            icon="heroicon-m-check-badge"
                            class="px-8 shadow-md"
                        >
                            حفظ واعتماد الجرد النهائي
                        </x-filament::button>
                    </div>
                @endif
            </div>
        </div>
    @else
        {{-- حالة عدم اختيار فرع - تصميم "Empty State" احترافي --}}
        <div class="flex flex-col items-center justify-center py-20 px-4 bg-white dark:bg-gray-900 border border-dashed border-slate-300 dark:border-gray-700 rounded-2xl">
            <div class="p-4 bg-slate-50 dark:bg-gray-800 rounded-full mb-4 ring-8 ring-slate-50 dark:ring-gray-800/50">
                <x-filament::icon icon="heroicon-o-building-storefront" class="w-12 h-12 text-primary-500" />
            </div>
            <h2 class="text-xl font-bold text-slate-800 dark:text-white mb-2">اختيار الفرع المطلوب</h2>
            <p class="text-slate-500 dark:text-gray-400 max-w-sm text-center">الرجاء تحديد الفرع من القائمة العلوية للبدء في عملية جرد المخزون ومطابقة الكميات الفعلية.</p>
        </div>
    @endif

    <style>
        /* إخفاء أسهم الـ Number input لتعطي مظهراً أنظف */
        input::-webkit-outer-spin-button,
        input::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }
        input[type=number] {
            -moz-appearance: textfield;
        }
    </style>
</x-filament-panels::page>