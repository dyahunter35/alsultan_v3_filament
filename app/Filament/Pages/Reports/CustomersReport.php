<?php

namespace App\Filament\Pages\Reports;

use App\Enums\ExpenseGroup;
use App\Filament\Pages\Concerns\HasReport;
use App\Models\Customer;
use App\Services\CustomerService;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Schemas;
use Filament\Pages\Page;
use Illuminate\Support\Collection;
use Livewire\Attributes\Url;
use Malzariey\FilamentDaterangepickerFilter\Fields\DateRangePicker;

class CustomersReport extends Page implements Forms\Contracts\HasForms
{
    use Forms\Concerns\InteractsWithForms;
    use HasReport;

    protected static ?int $navigationSort = 30;
    protected string $view = 'filament.pages.reports.customers-report';

    #[Url] public ?int $customerId = null;
    #[Url] public ?string $date_range = null;

    public ?Customer $customer = null;
    public Collection $ledger;

    public function mount(): void
    {
        $this->ledger = collect();
        $this->form->fill(['customerId' => $this->customerId, 'date_range' => $this->date_range]);
        $this->loadLedger();
    }

    public function getReportSubject(): string
    {
        return ($this->customer ? 'كشف حساب العميل : ' . $this->customer->name : 'كشف حساب');
    }
    protected function getFormSchema(): array
    {
        return [
            Schemas\Components\Grid::make(3)->schema([
                Forms\Components\Select::make('customerId')
                    ->label('العميل')->options(Customer::per(ExpenseGroup::SALE)->pluck('name', 'id'))
                    ->searchable()
                    ->reactive()
                    ->afterStateUpdated(fn($state) => [$this->customerId = $state, $this->loadLedger()]),
                DateRangePicker::make('date_range')
                    ->label('الفترة الزمنية')
                    //->disableClear(false)
                    ->live()
                    ->suffixAction(
                        Action::make('clear')
                            ->label(__('filament-daterangepicker-filter::message.clear'))
                            ->icon('heroicon-m-calendar-days')
                            ->action(fn() => [$this->date_range = null, $this->loadLedger()])
                    )
                    ->afterStateUpdated(fn($state) => [$this->date_range = $state, $this->loadLedger()]),

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
        [$from, $to] = parseDateRange($this->date_range);

        $this->customer = Customer::find($this->customerId);
        $this->ledger = app(CustomerService::class)->generateLedger($this->customer, $from, $to);
        $this->js("document.title = '{$this->getReportSubject()}'");
    }
    public function updateBalances(): void
    {
        if ($this->customer) {
            app(CustomerService::class)->updateCustomerBalance($this->customer);
            $this->loadLedger();
            Notification::make()
                ->title('تم تحديث بيانات العملاء')
                ->success()
                ->send();
        }

    }
}
