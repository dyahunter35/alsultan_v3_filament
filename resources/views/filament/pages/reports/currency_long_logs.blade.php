@php
    use App\Enums\CurrencyOption;
@endphp

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>
        {{(!is_null(request()->get('from_date') && is_null(request()->get('to_date')))) ? \Carbon::parse(request()->get('from_date'))->format('Y-m-d') : \Carbon::now()->format('Y-m-d') }}
        كشف حساب {{$user->name}}</title>
    <link rel="stylesheet" href="{{ asset('assets/css/invoice.css') }}" media="all" />
    <link href="{{ asset('assets/css/fontawesome/all.css') }}" rel="stylesheet">

    <style>
        * {
            font-size: 0.9rem
        }
    </style>
</head>

<body>
    <header class="clearfix" style="text-align: center">
        <div id="logo" class="row">
            <img width="50%" src="{{ asset('/images/default/logo.png') }}">
            <h3>{{ env('APP_NAME') }} </h3>

        </div>

        <h2 class="company">{{ $user?->name }}</h2>
        {{-- <h4 dir="ltr">تاريخ طباعة الفاتورة : {{ \Carbon::now()->format('Y-m-d h:m') }}</h4> --}}
        <div id="company" class="row" dir="rtl">
            <div class="column">

                <div><span>تاريخ اصدار التقرير</span><b dir="ltr">
                        {{ \Carbon::now()->format('Y-m-d') }}
                    </b>
                </div>
            </div>
            <div class="column">

            </div>
        </div>
    </header>
    <br>
    <main>
        {{-- Brands --}}
        <table>
            <thead>
                <tr class="text-success">
                    <th>العمله</th>
                    <th>جنية سوداني</th>
                    @foreach (CurrencyOption::cases() as $case)
                        <th>{{ $case->arabic() }}</th>
                    @endforeach
                </tr>
                <tr>
                    <th>
                        المبلغ
                    </th>
                    <th>{{ number_format($user?->plance, 2) }}</th>
                    @foreach (CurrencyOption::cases() as $case)
                        <th>{{ number_format($user->account($case->value), 1) }}</th>
                    @endforeach
                </tr>
            </thead>
        </table>

        <br>
        <hr style="border-style:dashed" />
        <br>

        {{-- Purches --}}
        <h2 class="company">شراء العملات</h2>
        <br>
        <table>
            <thead>
                <thead>
                    <tr class="text-success">
                        <th colspan="2"></th>

                        <th colspan="{{ count(CurrencyOption::cases()) * 2 + 3 }}">الشراء</th>

                    </tr>
                    <tr>
                        <th colspan="2"></th>
                        <th colspan="2">جنيه سوداني</th>

                        @foreach (CurrencyOption::cases() as $case)
                            <th colspan="2">{{ $case->arabic() }}</th>
                        @endforeach
                    </tr>
                    <tr>
                        <th>#</th>
                        <th>التاريخ</th>
                        <th>الرصيد</th>
                        <th>مبلغ الشراء</th>

                        {{-- <th>السعر المحول</th> --}}
                        @foreach (CurrencyOption::cases() as $case)
                            <th>المعادل</th>
                            <th colspan="1"> المبلغ </th>
                        @endforeach
                    </tr>
                </thead>
            <tbody>
                @php
                    $counter = 0;
                    $start_plance = -$user->expanseFrom(request('from_date', null));
                    $last_plance = 0;
                    $totals = [];
                    foreach (CurrencyOption::cases() as $case) {
                        $totals[$case->value] = 0;
                    }
                    $totals['sd'] = 0;
                @endphp
                @foreach ($currencies->where('type', 'pay') as $currency)
                    @php
                        ++$counter;
                        if ($counter > 1) {
                            $start_plance -= $last_plance;
                        }
                        $last_plance = $currency->total;

                    @endphp
                    <tr>
                        <td>{{ $counter }}</td>
                        <td>{{ \Carbon::parse($currency->created_at)->format('Y-m-d') }}</td>
                        <td class="total">{{ number_format($start_plance, 2) }}</td>
                        <td class="total">{{ number_format($currency->total, 2) }}</td>
                        @php
                            $totals['sd'] += $currency->total;
                        @endphp

                        {{-- <td>{{ number_format($currency->total, 2) }}</td> --}}
                        @foreach (CurrencyOption::cases() as $case)
                            @if ($case == $currency->code)
                                @php
                                    $totals[$case->value] += $currency->balance;
                                @endphp
                                <td>{{ number_format($currency->rate, 2) }}</td>
                                <td>{{ number_format($currency->balance, 2) }}</td>
                            @else
                                <td>0</td>
                                <td>0</td>
                            @endif
                        @endforeach
                    </tr>
                @endforeach
                <tr>
                    <td colspan="2">المجموع</td>
                    <td>{{ number_format($user->plance, 2) }}</td>

                    <td>{{ number_format($totals['sd'], 2) }}</td>
                    @foreach (CurrencyOption::cases() as $case)
                        <td colspan="2">{{ number_format($totals[$case->value], 2) }}</td>
                    @endforeach
                </tr>
            </tbody>
        </table>
        {{-- <div class="bagebreak"></div> --}}

        {{-- Payed --}}
        <br>
        <h2 class="company">صرف العملات</h2>
        <br>
        <table>
            <thead>
                <thead>
                    <tr class="text-success">
                        <th colspan="4"></th>

                        <th colspan="{{ count(CurrencyOption::cases()) + 1 }}">الدفع</th>

                    </tr>
                    <tr>
                        <th>#</th>
                        <th>التاريخ</th>
                        <th>البيان</th>
                        <th>الشركة</th>
                        @foreach (CurrencyOption::cases() as $case)
                            <th>{{ $case->arabic() }}</th>
                        @endforeach

                    </tr>


                </thead>
            <tbody>
                @php
                    $counter = 0;
                    $totals = [];
                    foreach (CurrencyOption::cases() as $case) {
                        $totals[$case->value] = 0;
                    }
                @endphp
                @foreach ($currencies->where('type', 'min') as $currency)
                    <tr>
                        <td>{{ ++$counter }}</td>
                        <td>{{ \Carbon::parse($currency->created_at)->format('Y-m-d') }}</td>
                        <td>{{ $currency->note }}</td>
                        <td>{{ $currency->company?->name }}</td>

                        {{-- <td>{{ number_format($currency->total, 2) }}</td> --}}
                        @foreach (CurrencyOption::cases() as $case)
                            @php
                                if ($case == $currency->code) {
                                    $totals[$case->value] += $currency->balance;
                                }
                            @endphp
                            @if ($case == $currency->code)
                                <td>{{ number_format($currency->balance, 2) }}</td>
                            @else
                                <td>0</td>
                            @endif
                        @endforeach

                        {{-- <td class="total">{{ number_format($currency->total, 2) }}</td> --}}
                    </tr>
                @endforeach
                <tr>
                    <td colspan="4">المجموع</td>
                    @foreach (CurrencyOption::cases() as $case)
                        <td colspan="">{{ number_format($totals[$case->value], 2) }}</td>
                    @endforeach
                </tr>
            </tbody>
        </table>
    </main>
    <a onClick="window.print()" class="float right no-print">
        <i class="fal fa-print my-float"></i>
    </a>
    <a onClick="window.close()" class="float  no-print">
        <i class="my-float close">X</i>
    </a>
</body>

</html>