<?php

namespace App\Filament\Pages\Reports;

use App\Enums\ExpenseGroup;
use App\Filament\Pages\Concerns\HasReport;
use App\Models\Customer;
use App\Services\CurrencyCustomersService;
use Filament\Forms;
use Filament\Pages\Page;
use Filament\Schemas\Components\Grid;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Collection;
use Livewire\Attributes\Url;

class CurrencyCustomersReport extends Page implements Forms\Contracts\HasForms
{
    use Forms\Concerns\InteractsWithForms;
    use HasReport;

    protected static ?int $navigationSort = 33;
    protected static ?string $navigationLabel = 'تقرير عملاء العملة';
    protected static ?string $title = 'تقرير عملاء العملة';
    protected ?string $subheading = 'تقرير شامل لحركة العملاء والعملات';

    protected string $view = 'filament.pages.reports.currency-customers-report';

    #[Url]
    public ?string $startDate = null;

    #[Url]
    public ?string $endDate = null;

    #[Url]
    public ?string $customerType = null;

    public array $reportData = [];
    public Collection $ledger;
    public array $summary = [];
    public Collection $accountsSummary;

    public function getTitle(): string|Htmlable
    {
        return 'تقرير عملاء العملة';
    }

    public function mount(): void
    {
        $this->ledger = collect();
        $this->accountsSummary = collect();
        $this->form->fill([
            'startDate' => $this->startDate,
            'endDate' => $this->endDate,
            'customerType' => $this->customerType,
        ]);
        $this->loadData();
    }

    public function loadData(): void
    {
        $this->reportData = app(CurrencyCustomersService::class)
            ->generateCurrencyCustomersReport(
                $this->startDate,
                $this->endDate,
                $this->customerType
            );

        $this->ledger = $this->reportData['ledger'] ?? collect();
        $this->summary = $this->reportData['summary'] ?? [];
        $this->accountsSummary = $this->reportData['accounts_summary'] ?? collect();
    }

    protected function getFormSchema(): array
    {
        return [
            Grid::make(3)
                ->schema([
                        Forms\Components\DatePicker::make('startDate')
                            ->label('من تاريخ')
                            ->reactive()
                            ->afterStateUpdated(fn() => $this->loadData()),

                        Forms\Components\DatePicker::make('endDate')
                            ->label('إلى تاريخ')
                            ->reactive()
                            ->afterStateUpdated(fn() => $this->loadData()),

                        Forms\Components\Select::make('customerType')
                            ->label('نوع العميل')
                            ->options([
                                    ExpenseGroup::SALE->value => 'المدينون (عملاء المبيعات)',
                                    ExpenseGroup::DEBTORS->value => 'الدائنون (موردون وآخرون)',
                                ])
                            ->placeholder('الكل')
                            ->reactive()
                            ->afterStateUpdated(fn() => $this->loadData()),
                    ]),
        ];
    }
}
