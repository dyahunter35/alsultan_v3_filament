<?php

namespace App\Filament\Pages\Reports;

use App\Filament\Pages\Concerns\HasReport;
use App\Models\Customer;
use App\Models\Supplying;
use App\Models\User;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Schemas;
use Filament\Pages\Page;
use Illuminate\Support\Collection;
use Livewire\Attributes\Url;

class SupplyReport extends Page implements Forms\Contracts\HasForms
{
    use Forms\Concerns\InteractsWithForms;
    use HasReport;

    protected static ?int $navigationSort = 31;
    protected string $view = 'filament.pages.reports.supply-report';
    protected static ?string $title = 'تقرير التوريدات';

    #[Url] public ?int $customerId = null;
    #[Url] public ?int $delegateId = null;
    #[Url] public ?string $startDate = null;
    #[Url] public ?string $endDate = null;

    public Collection $supplyings;
    public ?Customer $customer = null; // لإظهار بيانات العميل في الهيدر

    public function getReportSubject(): ?string
    {
        return $this->customer ? 'تقرير التوريدات من العميل  ' . $this->customer?->name : 'تقرير التوريدات';
    }
    public function mount(): void
    {
        $this->supplyings = collect();
        $this->form->fill([
            'startDate' => $this->startDate,
            'endDate' => $this->endDate,
            'customerId' => $this->customerId,
            'delegateId' => $this->delegateId,
        ]);
        $this->loadData();
    }

    protected function getFormSchema(): array
    {
        return [
            Schemas\Components\Grid::make(4)->schema([
                Forms\Components\Select::make('customerId')
                    ->label('العميل')
                    ->options(Customer::sale()
                        ->get()
                        ->mapWithKeys(fn(Customer $customer) => [
                            $customer->id => sprintf(
                                '%s [%s]',
                                $customer->name,
                                $customer->address,

                            ),
                        ]))
                    ->searchable()
                    ->live()
                    ->afterStateUpdated(fn($state) => [$this->customerId = $state, $this->loadData()]),

                Forms\Components\Select::make('delegateId')
                    ->label('المندوب')
                    ->options(User::role('sales')->pluck('name', 'id'))
                    ->searchable()
                    ->live()
                    ->afterStateUpdated(fn($state) => [$this->delegateId = $state, $this->loadData()]),


                Forms\Components\DatePicker::make('startDate')
                    ->label('من تاريخ')
                    ->live()
                    ->afterStateUpdated(fn($state) => [$this->startDate = $state, $this->loadData()]),

                Forms\Components\DatePicker::make('endDate')
                    ->label('إلى تاريخ')
                    ->live()
                    ->afterStateUpdated(fn($state) => [$this->endDate = $state, $this->loadData()]),
            ]),
        ];
    }

    public function loadData(): void
    {
        $this->customer = $this->customerId ? Customer::find($this->customerId) : null;

        $startDate = $this->startDate ? Carbon::parse($this->startDate)->startOfDay() : null;
        $endDate = $this->endDate ? Carbon::parse($this->endDate)->endOfDay() : null;

        $this->supplyings = Supplying::with(['customer', 'representative'])
            ->when($this->delegateId, fn($q) => $q->where('representative_id', $this->delegateId))
            ->when($this->customerId, fn($q) => $q->where('customer_id', $this->customerId))
            ->when($startDate, fn($q) => $q->where('created_at', '>=', $startDate))
            ->when($endDate, fn($q) => $q->where('created_at', '<=', $endDate))
            ->orderBy('created_at', 'desc')
            ->get();
    }
}