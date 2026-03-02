<?php

namespace App\Filament\Pages\Reports;

use App\Enums\CurrencyType;
use App\Filament\Pages\Concerns\HasReport;
use App\Models\Company;
use App\Models\Currency;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Collection;

class CompaniesReport extends Page
{
    use HasReport;

    protected string $view = 'filament.pages.reports.companies-report';

    public $companies;

    public Collection $all_currencies;

    protected static ?int $navigationSort = 33;

    public function mount()
    {
        $this->all_currencies = Currency::all();
        $this->loadData();
    }

    public function loadData(): void
    {
        $this->all_currencies = Currency::all();

        $this->companies = Company::with([
            'trucksAsCompany.cargos',
            'trucksAsContractor.cargos',
            'currencyTransactions.currency'
        ])->get()->map(function ($company) {
            $tx = $company->currencyTransactions;

            // SDG Charges (Debit) - from shipping bills
            $trucks = $company->trucksAsCompany->concat($company->trucksAsContractor);
            $sdg_charges = 0;
            foreach ($trucks as $truck) {
                $sdg_charges += (float) $truck->cargos->sum(function ($c) {
                    return (float) ($c->ton_price * $c->ton_weight);
                });
            }

            // SDG Payments/Conversions (Credit) - from transactions total field (SDG equivalent)
            $sdg_payments = (float) $tx->sum('total');

            // SDG Final Balance (Debit - Credit)
            $sdg_balance = $sdg_charges - $sdg_payments;

            // Foreign Currency Balances
            $currency_balances = [];
            foreach ($this->all_currencies as $currency) {
                // For companies, they are usually the 'party' receiving the currency.
                // Their balance is the sum of 'amount' they received.
                $balance = (float) $tx->where('currency_id', $currency->id)->sum('amount');
                $currency_balances[$currency->id] = $balance;
            }

            return [
                'id' => $company->id,
                'name' => $company->name,
                'sdg_charges' => $sdg_charges,
                'sdg_payments' => $sdg_payments,
                'sdg_balance' => $sdg_balance,
                'currency_balances' => $currency_balances,
                'transactions_count' => $tx->count(),
                'currencies' => $tx->pluck('currency.code')->unique()->filter()->values()->all(),
            ];
        })->toArray();
        $this->js("document.title = '{$this->getReportSubject()}'");
    }

    // update currency balances 
    public function updateCurrencyBalance()
    {
        \App\Models\CurrencyBalance::refreshBalances();
        app(\App\Services\CustomerService::class)->updateCustomersBalance();
        Notification::make()
            ->title('تم تحديث أرصدة العملات')
            ->success()
            ->send();
    }
}
