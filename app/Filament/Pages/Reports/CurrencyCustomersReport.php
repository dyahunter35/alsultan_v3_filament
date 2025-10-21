<?php

namespace App\Filament\Pages\Reports;

use App\Enums\ExpenseGroup;
use App\Filament\Pages\Concerns\HasReport;
use App\Filament\Pages\Concerns\HasSinglePage;
use App\Models\Customer;
use App\Services\CustomerService;
use Filament\Forms;
use Filament\Pages\Page;
use Filament\Schemas\Components\Grid;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Collection;
use Livewire\Attributes\Url;

class CurrencyCustomersReport extends Page implements Forms\Contracts\HasForms
{
    use HasReport;
    use Forms\Concerns\InteractsWithForms;

    protected  string $view = 'filament.pages.reports.customers-report';

    #[Url]
    public ?int $customerId = null;

    public ?Customer $customer = null;

    #[Url]
    public ?string $startDate = null;

    #[Url]
    public ?string $endDate = null;

    public ?Collection $ledger = null;

    public function getTitle(): string | Htmlable
    {
        dd(self::getLocalePath());
        return $this->customer
            ? __('customer.reports.ledger.title_for', ['customer' => $this->customer->name])
            : __('customer.reports.ledger.title');
    }


    public function mount(): void
    {
        $this->loadLedger();
    }

    public function updatedCustomerId(): void
    {
        $this->loadLedger();
    }

    public function updatedStartDate(): void
    {
        $this->loadLedger();
    }

    public function updatedEndDate(): void
    {
        $this->loadLedger();
    }

    public function loadLedger(): void
    {
        if (!$this->customerId) {
            $this->ledger = collect();
            return;
        }

        $customer = Customer::find($this->customerId);
        $this->customer = $customer;
        if (!$customer) {
            $this->customer = null;
            $this->ledger = collect();
            return;
        }

        $this->ledger = app(CustomerService::class)
            ->generateLedger($customer, $this->startDate, $this->endDate);
    }

    protected function getFormSchema(): array
    {
        return [
            Grid::make(3)
                ->schema([
                    Forms\Components\Select::make('customerId')
                        ->label('العميل')
                        ->options(Customer::per(ExpenseGroup::DEBTORS)->pluck('name', 'id'))
                        ->searchable()
                        ->reactive()
                        ->afterStateUpdated(fn() => $this->loadLedger()),

                    Forms\Components\DatePicker::make('startDate')
                        ->label('من تاريخ')
                        ->reactive()
                        ->afterStateUpdated(fn() => $this->loadLedger()),

                    Forms\Components\DatePicker::make('endDate')
                        ->label('إلى تاريخ')
                        ->reactive()
                        ->afterStateUpdated(fn() => $this->loadLedger()),
                ])
        ];
    }
}
