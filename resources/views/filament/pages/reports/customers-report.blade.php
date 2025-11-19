<div>
    @php
        use Carbon\Carbon;

    @endphp

    <head>
        <meta charset="utf-8">
        <title>
            {{ $startDate ? \Carbon\Carbon::parse($startDate)->format('Y-m-d') : now()->format('Y-m-d') }}_كشف حساب
            العميل
            {{ $customer?->name }}
        </title>

        <style>
            @font-face {
                font-family: 'Amiri';
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
                        {{-- كشف حساب العميل: {{ $customer?->name ?? '—' }} --}}
                        {{ $this->getTitle() }}
                    </h2>

                    <div class="border row" style="border:1px dashed #999; padding:6px;">
                        <div style="display:flex; justify-content:space-between;">
                            <div>
                                <div>📍 <b>المنطقة:</b> {{ $customer?->address ?? '-' }}</div>
                                <div>📞 <b>الهاتف:</b> {{ $customer?->phone ?? '-' }}</div>
                            </div>
                            <div style="text-align:left;">
                                <div><b>تاريخ التقرير:</b> {{ now()->format('Y/m/d') }}</div>
                                <div><b>الرصيد الحالي:</b> {{ number_format($customer?->balance ?? 0, 2) }}</div>
                            </div>
                        </div>
                    </div>
                </header>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm border border-gray-200 dark:border-gray-700">
                        <thead class="text-gray-700 bg-gray-100 dark:bg-gray-900 dark:text-gray-300">
                            <tr>
                                <th class="px-3 py-2 text-start">التاريخ</th>
                                <th class="px-3 py-2 text-start">الوصف</th>
                                <th class="px-3 py-2 text-center">دائن</th>
                                <th class="px-3 py-2 text-center">مدين</th>
                                <th class="px-3 py-2 text-center">الرصيد</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($ledger as $row)
                                <tr
                                    class="border-t border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-800">
                                    <td class="px-3 py-2 whitespace-nowrap">{{ $row['date'] }}</td>
                                    <td class="px-3 py-2">{{ $row['description'] }}</td>
                                    <td class="px-3 py-2 font-medium text-center text-green-600">
                                        {{ number_format($row['amount_in'], 2) }}
                                    </td>
                                    <td class="px-3 py-2 font-medium text-center text-red-600">
                                        {{ number_format($row['amount_out'], 2) }}
                                    </td>
                                    <td class="px-3 py-2 font-semibold text-center">
                                        {{ number_format($row['balance'], 2) }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="p-6 text-center text-gray-500">
                    لا توجد بيانات لعرضها.
                </div>
        @endif
    </div>
    <div class="my-7" style="text-align:center;">
        <x-filament::button icon='heroicon-o-printer' onclick="window.print()">طباعة التقرير</x-filament::button>
    </div>

</div>
