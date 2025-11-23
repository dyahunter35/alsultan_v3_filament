<?php

namespace App\Filament\Pages\Reports;

use App\Enums\CurrencyOption;
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
    public function mount()
    {
        $this->sudaneseCurrencyId = Currency::where('code', 'sdg')->value('id');
        $this->loadData();
    }

    public function loadData(): void
    {
        $this->companies = Company::with(['currencyTransactions' => function ($q) {
            //$q->where('currency_id', $this->sudaneseCurrencyId);
        }])->get()->map(function ($company) {
            // 🔹 إجمالي المدفوعات (إرسال)
            $paid = $company->currencyTransactions
                ->where('type', CurrencyType::SEND->value)
                ->sum('total');

            // 🔹 مصروفات الشركة (CompanyExpense)
            $companyExpense = $company->currencyTransactions
                ->where('type', CurrencyType::CompanyExpense->value)
                ->sum('total');

            // 🔹 التحويلات بالعملة السودانية (Convert)
            $converted = $company->currencyTransactions
                ->where('type', CurrencyType::Convert->value)
                ->sum('total');

            // 🔹 الرصيد النهائي = التحويلات - المدفوعات - مصروفات الشركة
            $finalBalance = $converted - ($paid + $companyExpense);

            return [
                'id' => $company->id,
                'name' => $company->name,
                'paid' => $paid,
                'company_expense' => $companyExpense,
                'converted' => $converted,
                'final_balance' => $finalBalance,
            ];
        });
    }
}
