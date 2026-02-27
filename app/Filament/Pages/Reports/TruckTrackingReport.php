<?php

namespace App\Filament\Pages\Reports;

use App\Enums\Country;
use App\Enums\TruckState;
use App\Filament\Pages\Concerns\HasReport;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Truck;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Filament\Schemas;
use Livewire\Attributes\Url;
use Malzariey\FilamentDaterangepickerFilter\Fields\DateRangePicker;

class TruckTrackingReport extends Page implements HasForms
{
    use HasReport;
    use InteractsWithForms;

    protected static ?int $navigationSort = 38;
    // protected static ?string $navigationIcon = 'heroicon-o-map';
    protected static ?string $title = 'تقرير تتبع الشاحنات';
    protected static ?string $navigationLabel = 'تتبع الشاحنات';

    protected string $view = 'filament.pages.reports.truck-tracking-report';

    #[Url]
    public ?string $status = null;

    #[Url]
    public ?int $contractorId = null;
    public ?array $branches = [];
    public ?array $countries = [];

    #[Url]
    public ?string $dateRange = null;
    public ?string $status_label = null;

    public $trucks;

    public function getReportSubject(): ?string
    {
        return $this->status_label;
    }

    public function mount(): void
    {
        $this->loadData();
    }

    protected function getFormSchema(): array
    {
        return [
            Schemas\Components\Grid::make(4)->schema([
                Forms\Components\Select::make('status')
                    ->label('حالة الشحنة')
                    ->options(TruckState::class)
                    ->reactive()
                    ->afterStateUpdated(fn() => $this->loadData()),

                Forms\Components\Select::make('contractorId')
                    ->label('المقاول')
                    ->options(Company::query()->contractor()->pluck('name', 'id'))
                    ->searchable()
                    ->reactive()
                    ->afterStateUpdated(fn() => $this->loadData()),

                Forms\Components\Select::make('branches')
                    ->label('الفروع')
                    ->options(Branch::query()->pluck('name', 'id'))
                    ->multiple()
                    ->searchable()
                    ->reactive()
                    ->suffixAction(
                        Action::make('clearB')
                            ->label(__('filament-daterangepicker-filter::message.clear'))
                            ->icon('heroicon-m-x-mark')
                            ->action(fn() => [$this->branches = [], $this->loadData()])
                    )
                    ->afterStateUpdated(fn() => $this->loadData()),

                Forms\Components\Select::make('countries')
                    ->label('الدول')
                    ->options(Country::class)
                    ->multiple()
                    ->searchable()
                    ->reactive()
                    ->suffixAction(
                        Action::make('clearB')
                            ->label(__('filament-daterangepicker-filter::message.clear'))
                            ->icon('heroicon-m-x-mark')
                            ->action(fn() => [$this->countries = [], $this->loadData()])
                    )
                    ->afterStateUpdated(fn() => $this->loadData()),

                DateRangePicker::make('dateRange')
                    ->label('الفترة الزمنية (تاريخ الشحن)')
                    ->reactive()
                    ->suffixAction(
                        Action::make('clear')
                            ->label(__('filament-daterangepicker-filter::message.clear'))
                            ->icon('heroicon-m-calendar-days')
                            ->action(fn() => [$this->dateRange = null, $this->loadData()])
                    )
                    ->afterStateUpdated(fn() => $this->loadData()),
            ]),
        ];
    }

    public function loadData(): void
    {
        [$start, $end] = parseDateRange($this->dateRange);

        $this->status_label = ($this->status) ? 'للشاحنات التي  : ' . TruckState::tryFrom($this->status)?->getDescription() ?? '' : 'تقرير الشحن';
        $query = Truck::query()
            ->with(['contractorInfo', 'from', 'toBranch', 'cargos.product', 'companyId'])
            ->out();

        if ($this->status) {
            $query->where('truck_status', $this->status);
        }

        if ($this->contractorId) {
            $query->where('contractor_id', $this->contractorId);
        }

        if ($this->branches) {
            $query->whereIn('branch_to', $this->branches);
        }

        if ($this->countries) {
            $query->whereIn('country', $this->countries);
        }

        if ($start && $end) {
            $query->whereBetween('pack_date', [$start, $end]);
        }

        $this->trucks = $query->orderBy('pack_date', 'desc')->get();
    }
}
