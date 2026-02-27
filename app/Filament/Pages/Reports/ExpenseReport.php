<?php

namespace App\Filament\Pages\Reports;

use App\Models\Branch;
use App\Models\Expense;
use App\Models\ExpenseType;
use App\Models\User;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Schemas;
use Filament\Pages\Page;
use Illuminate\Support\Collection;
use Livewire\Attributes\Url;
use App\Filament\Pages\Concerns\HasReport;
use Malzariey\FilamentDaterangepickerFilter\Fields\DateRangePicker;

class ExpenseReport extends Page implements Forms\Contracts\HasForms
{
    use Forms\Concerns\InteractsWithForms;
    use HasReport;

    protected static ?int $navigationSort = 35;
    protected string $view = 'filament.pages.reports.expense-report';
    protected static ?string $title = 'تقرير المصروفات العام';

    #[Url] public ?int $branchId = null;
    #[Url] public ?int $delegateId = null;
    #[Url] public ?int $typeId = null;
    #[Url] public ?string $date_range = null;
    public Collection $expenses;

    public function mount(): void
    {
        $this->expenses = collect();
        $this->form->fill([
            'branchId' => $this->branchId,
            'delegateId' => $this->delegateId,
            'typeId' => $this->typeId,
        ]);
        $this->loadData();
    }

    protected function getFormSchema(): array
    {
        return [
            Schemas\Components\Grid::make(5)->schema([
                Forms\Components\Select::make('branchId')
                    ->label('الفرع')
                    ->options(Branch::pluck('name', 'id'))
                    ->placeholder('كل الفروع')
                    ->live()
                    ->afterStateUpdated(fn() => $this->loadData()),

                Forms\Components\Select::make('delegateId')
                    ->label('المندوب')
                    ->options(User::role('sales')->pluck('name', 'id'))
                    ->placeholder('كل المناديب')
                    ->live()
                    ->afterStateUpdated(fn() => $this->loadData()),

                Forms\Components\Select::make('typeId')
                    ->label('نوع المصروف')
                    ->options(ExpenseType::pluck('label', 'id'))
                    ->placeholder('كل الأنواع')
                    ->live()
                    ->afterStateUpdated(fn() => $this->loadData()),

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

    public function loadData(): void
    {
        [$from, $to] = parseDateRange($this->date_range);

        //$start = $this->startDate ? Carbon::parse($this->startDate)->startOfDay() : null;
        //$end = $this->endDate ? Carbon::parse($this->endDate)->endOfDay() : null;

        $this->expenses = Expense::with(['type', 'branch', 'representative', 'beneficiary', 'payer'])
            ->when($this->branchId, fn($q) => $q->where('branch_id', $this->branchId))
            ->when($this->delegateId, fn($q) => $q->where('representative_id', $this->delegateId))
            ->when($this->typeId, fn($q) => $q->where('expense_type_id', $this->typeId))
            ->when($from, fn($q) => $q->where('created_at', '>=', $from))
            ->when($to, fn($q) => $q->where('created_at', '<=', $to))
            ->orderBy('created_at', 'desc')
            ->get();

        $this->js("document.title = '{$this->getReportSubject()}'");
        //dd($this->expenses);
    }
}