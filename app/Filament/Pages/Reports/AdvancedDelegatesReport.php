<?php

namespace App\Filament\Pages\Reports;

use App\Filament\Pages\Concerns\HasReport;
use App\Models\User;
use App\Services\DelegateService;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Schemas;
use Filament\Pages\Page;
use Illuminate\Support\Collection;
use Livewire\Attributes\Url;
use Malzariey\FilamentDaterangepickerFilter\Fields\DateRangePicker;

class AdvancedDelegatesReport extends Page implements Forms\Contracts\HasForms
{
    use Forms\Concerns\InteractsWithForms;
    use HasReport;

    protected static ?int $navigationSort = 32;
    protected static ?string $navigationLabel = 'تقرير يومية المندوب';
    protected static ?string $title = 'تقرير يومية المندوب';
    protected ?string $subheading = 'تقرير تفصيلي لحركة الخزينة والعملاء للمندوب';

    protected string $view = 'filament.pages.reports.advanced-delegates-report';

    #[Url] public ?int $delegateId = null;
    #[Url] public ?string $date_range = null;

    public ?User $delegate = null;
    public Collection $ledger;

    public function mount(): void
    {
        $this->ledger = collect();
        $this->form->fill(['delegateId' => $this->delegateId, 'date_range' => $this->date_range]);
        $this->loadLedger();
    }

    protected function getFormSchema(): array
    {
        return [
            Schemas\Components\Grid::make(3)->schema([
                Forms\Components\Select::make('delegateId')
                    ->label('المندوب')
                    ->options(User::role('sales')->pluck('name', 'id'))
                    ->searchable()
                    ->reactive()
                    ->afterStateUpdated(fn($state) => [$this->delegateId = $state, $this->loadLedger()]),
                DateRangePicker::make('date_range')
                    ->label('الفترة الزمنية')
                    //->disableClear(false)
                    ->live()
                    ->suffixAction(
                        Action::make('clear')
                            ->label(__('filament-daterangepicker-filter::message.clear'))
                            ->icon('heroicon-m-calendar-days')
                            ->action(fn() => [$this->date_range = null, $this->loadData()])
                    )
                    ->afterStateUpdated(fn($state) => [$this->date_range = $state, $this->loadData()]),

            ]),
        ];
    }

    public function loadLedger(): void
    {
        [$from, $to] = parseDateRange($this->date_range);


        if (!$this->delegateId) {
            $this->delegate = null;
            $this->ledger = collect();
            return;
        }

        $this->delegate = User::find($this->delegateId);

        if ($this->delegate) {
            // استدعاء الدالة الموحدة الجديدة
            $this->ledger = app(DelegateService::class)->generateUnifiedLedger(
                $this->delegate,
                $from,
                $to
            );
        }
    }

    public function updatePalance(): void
    {
        if ($this->delegateId) {
            app(DelegateService::class)->calculateUserBalances($this->delegate);
            //$this->loadData();
        }
    }
    public function loadData(): void
    {
        $this->loadLedger();
    }
}
