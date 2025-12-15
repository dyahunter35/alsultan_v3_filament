<?php

namespace App\Filament\Pages\Reports;

use App\Filament\Pages\Concerns\HasReport;
use App\Filament\Resources\Companies\Widgets\CompanyFinanceOverview;
use App\Models\Company;
use Filament\Pages\Page;
use Livewire\Attributes\Url;

class CompaniesDetails extends Page
{
    use HasReport;


    protected string $view = 'filament.pages.reports.companies-details';

    // input (mount with company id)
    #[Url()]
    public $companyId;

    // loaded data
    public $company;
    public $companies;
    public $transactions = [];
    public $totals = [
        'total_in' => 0,
        'total_out' => 0,
        'balance' => 0,
    ];

    public function mount(): void
    {
        $this->companies = Company::select('id', 'name')->get();
        $companyId = request()->get('company_id', Company::first()?->id ?? null);
        $this->companyId = $companyId;
        if ($this->companyId)
            $this->loadData();
    }

    public function loadData(): void
    {
        $company = Company::with([
            'currencyTransactions.currency',
            'trucksAsCompany',
            'trucksAsContractor',
            'expenses',
        ])->findOrFail($this->companyId);
        //dd($company);

        $tx = $company->currencyTransactions->sortByDesc('created_at')->values();

        $totalIn = (float) $tx->where('total', '>', 0)->sum('total');
        $totalOut = abs((float) $tx->where('total', '<', 0)->sum('total'));
        $balance = (float) $tx->sum('total');

        $this->company = $company;
        $this->transactions = $tx->map(fn($t) => [
            'id' => $t->id,
            'date' => optional($t->created_at)->toDateTimeString(),
            'type' => $t->type,
            'total' => (float) $t->total,
            'currency' => optional($t->currency)->code,
            'note' => $t->note ?? null,
            'meta' => $t->meta ?? null,
        ])->toArray();

        $this->totals = [
            'total_in' => $totalIn,
            'total_out' => $totalOut,
            'balance' => $balance,
        ];
    }

    public static function getWidgets(): array
    {
        return [
            // أضف الـ Widget الخاص بك هنا
            CompanyFinanceOverview::class,
            // يمكنك إضافة ويدجت آخر، مثلاً:
            // \App\Filament\Widgets\AnotherWidget::class,
        ];
    }
}
