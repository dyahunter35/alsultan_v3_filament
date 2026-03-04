<?php

namespace App\Filament\Pages\Reports;

use App\Filament\Pages\Concerns\HasReport;
use Filament\Pages\Page;
use App\Enums\CurrencyType;
use App\Models\Company;
use App\Models\Currency;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Schemas\Schema;
use Illuminate\Support\Collection;

class CompanyCurrencyReport extends Page implements HasForms
{

    use HasReport;
    use InteractsWithForms;

    protected string $view = 'filament.pages.reports.company-currency-report';

    public array $reportData = [];
    public array $exchangeRates = [];
    public ?int $targetCurrencyId = null;
    public float $grandTotalEquivalent = 0;

    public function mount(): void
    {
        $currencies = Currency::all();
        foreach ($currencies as $currency) {
            $this->exchangeRates[$currency->id] = (float) $currency->exchange_rate;
        }

        $this->targetCurrencyId = $currencies->first()?->id;

        $this->form->fill([
            'targetCurrencyId' => $this->targetCurrencyId,
        ]);

        $this->loadData();
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                    Select::make('targetCurrencyId')
                        ->label('العملة المستهدفة للعرض')
                        ->options(Currency::pluck('name', 'id'))
                        ->live()
                        ->afterStateUpdated(function ($state) {
                            $this->targetCurrencyId = $state;
                            $this->loadData();
                        }),
                ]);
    }

    public function loadData(): void
    {
        $currencies = Currency::all();
        $targetCurrency = Currency::find($this->targetCurrencyId);
        $targetRate = $targetCurrency?->exchange_rate ?? 1;

        $this->reportData = [
            'summary' => [],
            'groups' => [],
            'target_currency' => $targetCurrency?->name ?? '',
        ];

        $this->grandTotalEquivalent = 0;

        foreach ($currencies as $currency) {
            $companies = Company::whereHas('currencyTransactions', function ($query) use ($currency) {
                $query->where('currency_id', $currency->id);
            })
                ->with([
                        'currencyTransactions' => function ($query) use ($currency) {
                            $query->where('currency_id', $currency->id);
                        }
                    ])
                ->get()
                ->map(function ($company) {
                    $tx = $company->currencyTransactions;

                    $payments = (float) $tx->where('payer_id', $company->id)
                        ->where('payer_type', Company::class)
                        ->sum('amount');

                    $claims = (float) $tx->where('party_id', $company->id)
                        ->where('party_type', Company::class)
                        ->sum('amount');

                    return [
                        'name' => $company->name,
                        'claims' => $claims,
                        'payments' => $payments,
                        'balance' => $claims - $payments,
                    ];
                });

            if ($companies->isNotEmpty()) {
                $totalClaims = $companies->sum('claims');
                $totalPayments = $companies->sum('payments');
                $totalBalance = $companies->sum('balance');

                $currentRate = $this->exchangeRates[$currency->id] ?? 1;
                $equivalent = ($totalBalance * $currentRate) / ($targetRate ?: 1);

                $this->grandTotalEquivalent += $equivalent;

                $this->reportData['groups'][$currency->id] = [
                    'currency_name' => $currency->name,
                    'currency_code' => $currency->code,
                    'companies' => $companies,
                    'totals' => [
                        'claims' => $totalClaims,
                        'payments' => $totalPayments,
                        'balance' => $totalBalance,
                        'equivalent' => $equivalent,
                    ],
                ];

                $this->reportData['summary'][] = [
                    'currency_id' => $currency->id,
                    'currency_name' => $currency->name,
                    'total_claims' => $totalClaims,
                    'total_payments' => $totalPayments,
                    'total_balance' => $totalBalance,
                    'equivalent' => $equivalent,
                ];
            }
        }
    }

    public function updateRate($currencyId, $value): void
    {
        $this->exchangeRates[$currencyId] = (float) $value;
        $this->loadData();
    }

    public function getTitle(): string
    {
        return 'تقرير أرصدة الشركات والعملات';
    }
}