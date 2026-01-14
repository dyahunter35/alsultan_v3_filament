<?php

namespace App\Filament\Pages\Reports;

use App\Filament\Pages\Concerns\HasReport;
use App\Models\User;
use App\Services\DelegateService;
use Filament\Forms;
use Filament\Schemas;
use Filament\Pages\Page;
use Illuminate\Support\Collection;
use Livewire\Attributes\Url;

class DelegatesReport extends Page implements Forms\Contracts\HasForms
{
    use Forms\Concerns\InteractsWithForms;
    use HasReport;

    protected static ?int $navigationSort = 31;
    protected static ?string $navigationLabel = 'كشف حساب مندوب';
    protected static ?string $title = 'كشف حساب مندوب';
    protected ?string $subheading = 'تقرير تفصيلي لحركات المندوب المالية';

    protected string $view = 'filament.pages.reports.delegates-report';

    #[Url] public ?int $delegateId = null;
    #[Url] public ?string $startDate = null;
    #[Url] public ?string $endDate = null;

    public ?User $delegate = null;
    public Collection $ledger;

    public function mount(): void
    {
        $this->ledger = collect();
        $this->form->fill(['delegateId' => $this->delegateId, 'startDate' => $this->startDate, 'endDate' => $this->endDate]);
        $this->loadLedger();
    }

    protected function getFormSchema(): array
    {
        return [
            Schemas\Components\Grid::make(3)->schema([
                Forms\Components\Select::make('delegateId')
                    ->label('المندوب')
                    ->options(User::role('sales')->pluck('name', 'id')) // Filter by sales role
                    ->searchable()
                    ->reactive()
                    ->afterStateUpdated(fn($state) => [$this->delegateId = $state, $this->loadLedger()]),
                Forms\Components\DatePicker::make('startDate')
                    ->label('من تاريخ')
                    ->reactive()
                    ->afterStateUpdated(fn($state) => [$this->startDate = $state, $this->loadLedger()]),
                Forms\Components\DatePicker::make('endDate')
                    ->label('إلى تاريخ')
                    ->reactive()
                    ->afterStateUpdated(fn($state) => [$this->endDate = $state, $this->loadLedger()]),
            ]),
        ];
    }

    public function loadLedger(): void
    {
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
                $this->startDate,
                $this->endDate
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
