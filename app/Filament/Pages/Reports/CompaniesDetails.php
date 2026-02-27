<?php

namespace App\Filament\Pages\Reports;

use App\Filament\Pages\Concerns\HasReport;
use App\Filament\Resources\Companies\Widgets\CompanyFinanceOverview;
use App\Filament\Resources\Companies\Widgets\CurrencyWidget;
use App\Models\Company;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms;
use Filament\Pages\Page;
use Livewire\Attributes\Url;

class CompaniesDetails extends Page implements HasForms
{
    use HasReport;
    use InteractsWithForms;

    protected static ?int $navigationSort = 34;
    protected string $view = 'filament.pages.reports.companies-details';

    #[Url()]
    public $companyId;

    // ملاحظة: الـ Widgets تتوقع وجود متغير باسم record في الغالب
    public ?Company $company = null;


    public $transactions = [];

    public $totals = [
        'total_in' => 0,
        'total_out' => 0,
        'balance' => 0,
    ];

    public function getReportSubject(): string
    {
        $title = 'تقرير تفاصيل شركة ' . $this->_company->name;
        /* if ($this->date_range) {
            $title .= ' للفترة (' . $this->date_range . ')';
        } */
        return $title;
    }
    // 1. تعريف الفورم بشكل صحيح في فيلامينت 3/4
    protected function getFormSchema(): array
    {
        return [

            Forms\Components\Select::make('companyId')
                ->label('الشركة')
                ->options(Company::query()->latest()->pluck('name', 'id'))
                ->searchable()
                ->columnSpanFull()
                ->reactive()
                ->afterStateUpdated(fn() => $this->loadData()),

        ];
    }

    public function mount(): void
    {
        // تهيئة الفورم بالبيانات القادمة من الرابط (URL)
        $this->form->fill([
            'companyId' => $this->companyId,
        ]);

        if ($this->companyId) {
            $this->loadData();
        }
    }

    public function loadData(): void
    {
        if (!$this->companyId) {
            $this->company = null;
            return;
        }

        $this->company = Company::with([
            'currencyTransactions.currency',
            'trucksAsCompany',
            'trucksAsContractor',
            'expenses',
        ])->find($this->companyId);

        if (!$this->company)
            return;

        $tx = $this->company->currencyTransactions->sortByDesc('created_at')->values();

        $this->transactions = $tx->map(fn($t) => [
            'id' => $t->id,
            'date' => optional($t->created_at)->toDateTimeString(),
            'type' => $t->type,
            'amount' => $t->amount,
            'rate' => $t->rate,
            'total' => (float) $t->total,
            'currency' => optional($t->currency)->code,
            'note' => $t->note ?? null,
        ])->toArray();

        $this->totals = [
            'total_in' => (float) $tx->where('total', '>', 0)->sum('total'),
            'total_out' => abs((float) $tx->where('total', '<', 0)->sum('total')),
            'balance' => (float) $tx->sum('total'),
        ];

        // 2. إرسال حدث لتحديث الـ Widgets إذا كانت تستمع
        $this->dispatch('updateCompany', companyId: $this->companyId);
        $this->js("document.title = '{$this->getReportSubject()}'");
    }

    // 3. لضمان عرض الودجات في الصفحة
    protected function getHeaderWidgets(): array
    {
        return [
            // إذا كنت تريد عرضها في رأس الصفحة تلقائياً
            // CompanyFinanceOverview::class,
        ];
    }
}
