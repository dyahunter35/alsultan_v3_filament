<?php

namespace App\Filament\Pages\Reports;

use App\Filament\Pages\Concerns\HasReport;
use App\Models\Company;
use App\Services\Reports\ShippingContractorService;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Filament\Schemas;
use Livewire\Attributes\Url;
use Malzariey\FilamentDaterangepickerFilter\Fields\DateRangePicker;

class ShippingContractorReport extends Page implements HasForms
{
    use HasReport;
    use InteractsWithForms;

    protected static ?int $navigationSort = 37;
    // protected static ?string $navigationIcon = 'heroicon-o-document-chart-bar';
    protected static ?string $title = 'تقرير مقاولي الشحن';
    protected static ?string $navigationLabel = 'تقرير مقاولي الشحن';

    protected string $view = 'filament.pages.reports.shipping-contractor-report';

    #[Url]
    public ?int $contractorId = null;

    #[Url]
    public ?string $dateRange = null;

    public array $rows = [];
    public array $summary = [
        'total_claims' => 0,
        'total_paid' => 0,
        'balance' => 0,
    ];

    public ?Company $contractor = null;

    public function getReportSubject(): ?string
    {
        return "المقاول : " . $this->contractor?->name ?? 'تقرير مقاولي الشحن';
    }

    public function mount(): void
    {
        if ($this->contractorId) {
            $this->loadData();
        }
    }

    protected function getFormSchema(): array
    {
        return [
            Schemas\Components\Grid::make(4)->schema([
                Forms\Components\Select::make('contractorId')
                    ->label('المقاول')
                    ->options(Company::query()->contractor()->pluck('name', 'id'))
                    ->searchable()
                    ->reactive()
                    ->required()
                    ->afterStateUpdated(fn($state) => $this->loadData()),

                DateRangePicker::make('dateRange')
                    ->label('الفترة الزمنية')
                    ->reactive()
                    ->suffixAction(
                        Action::make('clear')
                            ->label(__('filament-daterangepicker-filter::message.clear'))
                            ->icon('heroicon-m-calendar-days')
                            ->action(fn() => [$this->dateRange = null, $this->loadData()])
                    )
                    ->afterStateUpdated(fn($state) => $this->loadData()),
            ]),
        ];
    }

    public function loadData(): void
    {
        if (!$this->contractorId) {
            return;
        }

        $this->contractor = Company::find($this->contractorId);

        $service = new ShippingContractorService();
        $data = $service->getReportData($this->contractorId, $this->dateRange);

        $this->rows = $data['rows'];
        $this->summary = $data['summary'];
        $this->js("document.title = '{$this->getPrintTitle()}'");

    }
}
