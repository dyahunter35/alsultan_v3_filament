<?php

namespace App\Filament\Pages\Reports;

use App\Filament\Pages\Concerns\HasReport;
use App\Models\Customer;
use App\Services\CustomerService;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Schemas;
use Filament\Pages\Page;
use Illuminate\Support\Collection;
use Livewire\Attributes\Url;

class CustomersReport extends Page implements Forms\Contracts\HasForms
{
    use Forms\Concerns\InteractsWithForms;
    use HasReport;

    protected static ?int $navigationSort = 30;
    protected string $view = 'filament.pages.reports.customers-report';

    #[Url] public ?int $customerId = null;
    #[Url] public ?string $startDate = null;
    #[Url] public ?string $endDate = null;

    public ?Customer $customer = null;
    public Collection $ledger;

    public function mount(): void
    {
        $this->ledger = collect();
        $this->form->fill(['customerId' => $this->customerId, 'startDate' => $this->startDate, 'endDate' => $this->endDate]);
        $this->loadLedger();
    }

    protected function getFormSchema(): array
    {
        return [
            Schemas\Components\Grid::make(3)->schema([
                Forms\Components\Select::make('customerId')
                    ->label('العميل')->options(Customer::all()->pluck('name', 'id'))
                    ->searchable()->reactive()
                    ->afterStateUpdated(fn($state) => [$this->customerId = $state, $this->loadLedger()]),
                Forms\Components\DatePicker::make('startDate')
                    ->label('من تاريخ')->reactive()
                    ->afterStateUpdated(fn($state) => [$this->startDate = $state, $this->loadLedger()]),
                Forms\Components\DatePicker::make('endDate')
                    ->label('إلى تاريخ')->reactive()
                    ->afterStateUpdated(fn($state) => [$this->endDate = $state, $this->loadLedger()]),
            ]),
        ];
    }

    public function loadLedger(): void
    {
        if (!$this->customerId) {
            $this->customer = null;
            $this->ledger = collect();
            return;
        }
        $this->customer = Customer::find($this->customerId);
        $this->ledger = app(CustomerService::class)->generateLedger($this->customer, $this->startDate, $this->endDate);
    }
    public function updateBalances(): void
    {
        app(CustomerService::class)->updateCustomersBalance();
        $this->loadLedger();
        Notification::make()
            ->title('تم تحديث بيانات العملاء')
            ->success()
            ->send();
    }
}
