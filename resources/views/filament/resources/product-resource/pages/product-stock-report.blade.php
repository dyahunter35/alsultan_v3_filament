<x-filament-panels::page>
    <x-filament::section>
        <div>


            <div class="font-sans text-gray-800">

                <!-- Main Container -->
                <main class="w-full " id="report-content">
                    <div
                        class="flex items-center justify-center gap-4 xl:gap-60 lg:gap-48 md:gap-16 sm:gap-8 sm:flex-row print:mt-3">
                        <div class="w-full ">

                            {{-- <img src="{{ asset('asset/images/logo/gas 200.png') }}" class="w-16"> --}}
                            <div class="flex flex-row items-center justify-center gap-2">
                                <img alt="" src="{{ asset('asset/images/manifest-icon-192.maskable.png') }}"
                                    class="w-14 print:w-6" />
                                <div>
                                    <div class="text-2xl font-bold">
                                        {{ __('app.name') }}
                                    </div>

                                </div>
                            </div>

                            <div class="flex justify-center font-bold text-md " dir="ltr">
                                {{ __('app.phone') }}
                            </div>
                        </div>
                    </div>
                    <!-- Report Card -->
                    <div class="overflow-hidden bg-white shadow-lg rounded-xl">

                        <!-- Header Section -->
                        <header class="p-6 border-b border-gray-200">
                            <div class="flex flex-col items-start justify-between sm:flex-row sm:items-center">
                                <div>
                                    <h1 class="text-2xl font-bold text-gray-900">تقرير المنتجات </h1>
                                    <p class="mt-1 text-sm text-gray-500">سجل المنتجات لكل الفروع</p>
                                </div>
                                <div class="mt-4 text-sm text-left text-gray-600 sm:mt-0 sm:text-right">
                                    <p class="font-semibold">تاريخ التقرير:</p>
                                    <p>{{ now()->format('Y-m-d') }}</p>
                                </div>
                            </div>
                        </header>

                        <!-- Table Container for Responsiveness -->
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm text-right text-gray-600">
                                <!-- Table Head -->
                                <thead class="text-xs text-gray-700 uppercase border-b border-gray-200 bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-4 font-semibold">المنتج</th>
                                        @foreach ($branches as $branch)
                                            <th scope="col" class="px-6 py-4 font-semibold text-center">
                                                {{ $branch->name }}
                                            </th>
                                        @endforeach
                                        <th scope="col" class="px-6 py-4 font-semibold text-center">المجموع الكلي
                                        </th>
                                    </tr>
                                </thead>

                                <!-- Table Body -->
                                <tbody>
                                    @forelse ($products as $product)

                                        <tr
                                            class="transition-colors duration-200 bg-white border-b border-gray-200 hover:bg-gray-50">
                                            <td class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap">
                                                {{ $product->name }}
                                            </td>
                                            @foreach ($branches as $branch)
                                                @php
                                                    // البحث عن بيانات الفرع المحدد داخل علاقة المنتج بالفروع
                                                    $branchPivot = $product->branches->firstWhere('id', $branch->id);
                                                    // جلب الكمية من حقل الـ pivot أو عرض 0 إذا لم يكن المنتج مرتبطاً بالفرع
                                                    $quantityInBranch = $branchPivot?->pivot->total_quantity ?? 0;
                                                @endphp
                                                <td class="px-6 py-4 text-center">
                                                    {{ $quantityInBranch }}
                                                </td>
                                            @endforeach
                                            <td class="px-6 py-4 font-semibold text-center text-gray-900">
                                                {{ number_format($product->totalStock ?? 0) }}</td>
                                            {{-- <td class="px-6 py-4 text-center">
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            أداء مرتفع
                                        </span>
                                    </td> --}}
                                        </tr>
                                    @endforeach
                                </tbody>

                                <!-- Table Footer -->
                                {{-- <tfoot class="bg-gray-50">
                            <tr class="font-semibold text-gray-900">
                                <td class="px-6 py-4 text-base">الإجمالي</td>
                                <td class="px-6 py-4 text-center">5,920</td>
                                <td class="px-6 py-4 text-center">--</td>
                                <td class="px-6 py-4 text-base text-center">$2,117,780</td>
                                <td class="px-6 py-4 text-center">--</td>
                            </tr>
                        </tfoot> --}}
                            </table>
                        </div>

                    </div>

                </main>

            </div>

            <style>
                @media print {
                    body * {
                        visibility: hidden;
                    }

                    #report-content,
                    #report-content * {
                        visibility: visible;
                    }

                    #report-content {
                        position: absolute;
                        left: 0;
                        top: 0;
                        width: 100%;
                        margin: 0;
                        padding: 0;
                        border: none;
                        box-shadow: none;
                    }
                }
            </style>
        </div>
    </x-filament::section>

</x-filament-panels::page>
