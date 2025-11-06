<div>
    @php
        use Carbon\Carbon;

    @endphp

    <head>
        <meta charset="utf-8">
        <title>
            {{ now()->format('Y-m-d') }} كشف
            تقرير صافي العملات
        </title>

        <style>
            @font-face {
                font-family: 'Amiri';
                /* تأكد من أن المسار صحيح ومتاح للطباعة والعرض */
                src: url('{{ asset('fonts/flat-jooza-regular.woff2') }}') format('woff2');
            }

            .content {
                font-family: Amiri, sans-serif;
                width: 100%;
                margin: 0 auto;
                margin-top: 33px;
            }

            h3,
            h2 {

                font-style: bold;
            }

            header {
                text-align: center;
            }

            table {
                width: 100%;
                border-collapse: collapse;
                font-size: 0.8rem;
            }

            th,
            td {
                border: 1px solid #999;
                padding: 6px;
                text-align: center;
            }

            th {
                background-color: #f5f5f5;
            }

            .no-print button {
                padding: 8px 12px;
                border: none;
                background-color: #007bff;
                color: white;
                border-radius: 4px;
                cursor: pointer;
            }

            .total {
                font-weight: bold;
                background-color: #f0f0ff;
            }

            @media print {
                .no-print {
                    display: none;
                }
            }
        </style>
    </head>
    <div class="mt-6 space-y-6 content">
        {{-- 🔹 الفلاتر --}}
        <div class="p-4 bg-white shadow-sm dark:bg-gray-800 rounded-xl">
            {{ $this->form }}
        </div>


        {{-- 🔹 الجدول --}}
        @if ($ledger && $ledger->count())
            <div id="report-content" class="p-4 space-y-4 bg-white shadow-sm dark:bg-gray-800 rounded-xl">
                <header class="clearfix">
                    <div id="logo" style="text-align:center; margin-top:10px;">
                        <img width="80" src="{{ asset('asset/logo.png') }}" alt="logo" class="mx-auto" />
                        <h2 class="text-bold">{{ __('app.name') }}</h2>
                        <h3>{{ __('app.address') }}</h3>
                    </div>
                    <h2 style="margin:10px 0; border-top:1px solid #aaa; border-bottom:1px solid #aaa; padding:4px;">
                        {{ $this->getHeading() }}
                    </h2>

                    <div class="border row" style="border:1px dashed #999; padding:6px;">
                        <div style="display:flex; justify-content:space-between;">
                            <div>
                                {{-- <div>📍 <b>المنطقة:</b> {{ $customer?->address ?? '-' }}</div>
                                <div>📞 <b>الهاتف:</b> {{ $customer?->phone ?? '-' }}</div> --}}
                            </div>
                            <div style="text-align:left;">
                                <div><b>تاريخ التقرير:</b> {{ now()->format('Y/m/d') }}</div>
                                {{-- <div><b>الرصيد الحالي:</b> {{ number_format($customer?->balance ?? 0, 2) }}</div> --}}
                            </div>
                        </div>
                    </div>
                </header>

                <table>
                    <thead>
                        <tr class="text-success">
                            <th>العمله</th>
                            <th>جنية سوداني</th>
                            @foreach ($currencies as $case)
                                <th>{{ $case->name }}</th>
                            @endforeach
                            <!-- عمود الإجمالي المحول -->
                            <th>الإجمالي المحول</th>
                        </tr>
                        <tr>
                            <th>
                                المبلغ
                            </th>
                            <th>
                                {{-- سعر صرف الجنيه السوداني (الوحدة المرجعية) --}}
                                <input type="number" value="1" wire:model.live="keys.sd"
                                    style="text-align: center;border: none;padding: 5px;" min="0.01" step="0.01"
                                    x-data="{
                                        checkPositive() {
                                            if (this.$el.value <= 0) {
                                                this.$el.value = 1;
                                            }
                                        }
                                    }" x-on:input="checkPositive()" />
                            </th>
                            @foreach ($currencies as $case)
                                <th>
                                    {{-- أسعار صرف العملات الأخرى --}}
                                    <input type="number" value="{{ $case->exchange_rate }}"
                                        wire:model.live="keys.{{ $case->code }}"
                                        style="text-align: center;border: none;padding: 5px;" min="0.01"
                                        step="0.01" x-data="{
                                            checkPositive() {
                                                if (this.$el.value <= 0) {
                                                    this.$el.value = 1;
                                                }
                                            }
                                        }" x-on:input="checkPositive()" />
                                </th>
                            @endforeach
                            <th>
                                (بالوحدة المرجعية)
                            </th>
                        </tr>
                    </thead>
                </table>

                <br>
                <hr style="border-style:dashed" />
                <br>


                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm border border-gray-200 dark:border-gray-700">
                        <thead class="text-gray-700 bg-gray-100 dark:bg-gray-900 dark:text-gray-300">
                            <tr>
                                <th class="px-3 py-2 text-start">#</th>
                                <th class="px-3 py-2 text-start">{{ $type == 'companies' ? 'الشركة' : 'العميل' }}</th>
                                <th class="px-3 py-2 text-center">جنية سوداني</th>
                                @foreach ($currencies as $currency)
                                    <th class="px-3 py-2 text-center">{{ $currency->name }}</th>
                                @endforeach
                                <th class="px-3 py-2 text-center bg-blue-100 dark:bg-blue-900">
                                    الإجمالي المحول
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($ledger as $row)
                                <tr
                                    class="border-t border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-800">
                                    <td class="px-3 py-2 whitespace-nowrap">{{ $row->id }}</td>
                                    <td class="px-3 py-2">{{ $row->name }}</td>
                                    {{-- رصيد الجنيه السوداني --}}
                                    <td class="px-3 py-2 font-medium text-center text-green-600">
                                        {{ number_format($row->balance, 2) }}
                                    </td>
                                    {{-- أرصدة العملات الأخرى --}}
                                    @foreach ($currencies as $currency)
                                        <td class="px-3 py-2 font-medium text-center text-green-600">
                                            {{ number_format($row->currencyValue($currency->id), 2) }}
                                        </td>
                                    @endforeach
                                    {{-- الإجمالي المحول لكل صف --}}
                                    <td
                                        class="px-3 py-2 font-semibold text-center text-blue-700 bg-blue-50 dark:bg-blue-800">
                                        {{ number_format($this->total[$row->id] ?? 0, 2) }}
                                    </td>
                                </tr>
                            @endforeach
                            <tr>
                                <td colspan="2" class="total">المجموع الكلي</td>
                                {{-- مجموع أرصدة الجنيه السوداني --}}
                                <td class="total">{{ number_format($ledger->sum('balance'), 2) }}</td>
                                {{-- مجموع أرصدة العملات الأخرى (يمكن إضافة منطق حساب الإجمالي هنا لاحقاً) --}}
                                @foreach ($currencies as $case)
                                    <td class="total">...</td>
                                @endforeach
                                {{-- الإجمالي الكلي المحول لجميع الصفوف --}}
                                <td class="text-lg font-bold text-red-700 bg-blue-200 total dark:bg-blue-700">
                                    {{ number_format($this->total_converted ?? 0, 2) }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="no-print" style="margin-top:20px; text-align:center;">
                    <button onclick="window.print()">طباعة التقرير</button>
                </div>
            </div>
        @else
            <div class="p-6 text-center text-gray-500">
                لا توجد بيانات لعرضها.
            </div>
        @endif
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
