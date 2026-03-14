<?php

namespace App\Filament\Pages\Reports;

use App\Enums\ExpenseGroup;
use App\Filament\Pages\Concerns\HasReport;
use App\Models\Expense;
use App\Models\ExpenseType;
use App\Models\Truck;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Filament\Schemas;
use Livewire\Attributes\Url;
use Malzariey\FilamentDaterangepickerFilter\Fields\DateRangePicker;

class TruckExpensesReport extends Page implements HasForms
{
    use HasReport;
    use InteractsWithForms;

    protected static ?int $navigationSort = 40;
    protected static ?string $title = 'تفاصيل منصرفات تخصيم الشاحنات (تخليص)';
    protected static ?string $navigationLabel = 'منصرفات تخصيم الشاحنات';

    protected string $view = 'filament.pages.reports.truck-expenses-report';

    #[Url]
    public ?array $truckId = [];

    #[Url]
    public ?string $dateRange = null;

    public $expenses;
    public float $totalAmount = 0;

    public function getReportSubject(): ?string
    {
        return 'تقرير تفاصيل منصرفات التخليص للشاحنات';
    }

    public function mount(): void
    {
        $this->loadData();
    }

    protected function getFormSchema(): array
    {
        return [
            Schemas\Components\Grid::make(2)->schema([
                Forms\Components\Select::make('truckId')
                    ->label('الشاحنة (رقم اللوحة)')
                    ->options(Truck::query()->orderBy('code')->get()->pluck('code', 'id'))
                    ->searchable()
                    ->multiple()
                    ->reactive()
                    ->afterStateUpdated(fn() => $this->loadData())
                    ->suffixAction(
                        Action::make('clearTruck')
                            ->label(__('filament-daterangepicker-filter::message.clear'))
                            ->icon('heroicon-m-x-mark')
                            ->action(fn() => [$this->truckId = null, $this->loadData()])
                    ),

                DateRangePicker::make('dateRange')
                    ->label('الفترة الزمنية (تاريخ المنصرف)')
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

        $query = Expense::query()
            ->types(ExpenseGroup::SHIPMENT_CLEARANCE)
            ->with(['truck', 'type', 'representative', 'branch'])
            ->whereNotNull('truck_id');

        if ($this->truckId) {
            $query->whereIn('truck_id', $this->truckId);
        }

        if ($start && $end) {
            $query->whereBetween('created_at', [$start, $end]);
        }

        $this->expenses = $query->orderBy('created_at', 'desc')->get();
        $this->totalAmount = $this->expenses->sum('total_amount');

        $this->js("document.title = '{$this->getPrintTitle()}'");
    }
}
