<?php

namespace App\Filament\Pages\Reports;

use App\Enums\CurrencyType;
use App\Filament\Pages\Concerns\HasReport;
use App\Models\Company;
use App\Models\Currency;
use Filament\Pages\Page;

class CompaniesReport extends Page
{
    use HasReport;

    protected string $view = 'filament.pages.reports.companies-report';

    public $companies;

    public $sudaneseCurrencyId;

    protected static ?int $navigationSort = 33;

    public function mount()
    {
        $this->sudaneseCurrencyId = Currency::where('code', 'sdg')->value('id');
        $this->loadData();
    }

    public function loadData(): void
    {
        $this->companies = Company::with(['currencyTransactions' => function ($q) {
            $q->orderBy('created_at', 'desc');
        }, 'currencyTransactions.currency'])->get()->map(function ($company) {
            $tx = $company->currencyTransactions;

            // Totals by sign
            $total_in = (float) $tx->where('total', '>', 0)->sum('total');
            $total_out = abs((float) $tx->where('total', '<', 0)->sum('total'));

            // Specific types (use your enum values)
            $paid = (float) $tx->where('type', CurrencyType::SEND->value)->sum('total');
            $companyExpense = (float) $tx->where('type', CurrencyType::CompanyExpense->value)->sum('total');
            $converted = (float) $tx->where('type', CurrencyType::Convert->value)->sum('total');

            // Two balances: generic balance and domain-specific formula
            $generic_balance = $total_in - $total_out;
            $formula_balance = $converted - ($paid + $companyExpense);

            // Currency summary (list unique currency codes used in transactions)
            $currencies = $tx->pluck('currency.code')->unique()->filter()->values()->all();

            return [
                'id' => $company->id,
                'name' => $company->name,
                'total_in' => $total_in,
                'total_out' => $total_out,
                'paid' => $paid,
                'company_expense' => $companyExpense,
                'converted' => $converted,
                'generic_balance' => $generic_balance,
                'formula_balance' => $formula_balance,
                'transactions_count' => $tx->count(),
                'currencies' => $currencies,
            ];
        })->toArray();
    }
}
