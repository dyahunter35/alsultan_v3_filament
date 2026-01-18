<?php

namespace App\Filament\Pages\Reports;

use App\Enums\ExpenseGroup;
use App\Filament\Pages\Concerns\HasReport;
use App\Models\CurrencyBalance;
use App\Models\Customer;
use App\Services\CurrencyLogsService;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Grid;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Collection;
use Livewire\Attributes\Url;

class CurrencyLogsReport extends Page implements Forms\Contracts\HasForms
{
    use Forms\Concerns\InteractsWithForms;
    use HasReport;

    protected static ?int $navigationSort = 34;
    protected static ?string $navigationLabel = 'تقرير سجلات العملات';
    protected static ?string $title = 'تقرير سجلات العملات';
    protected ?string $subheading = 'تقرير تفصيلي لشراء وصرف العملات';

    protected string $view = 'filament.pages.reports.currency-logs-report';

    #[Url]
    public ?int $customerId = null;

    #[Url]
    public ?string $startDate = null;

    #[Url]
    public ?string $endDate = null;

    public ?Customer $customer = null;
    public array $balances = [];
    public Collection $purchaseTransactions;
    public Collection $paymentTransactions;

    public $reportData;
    public function getTitle(): string|Htmlable
    {
        return $this->customer
            ? 'تقرير سجلات العملات - ' . $this->customer->name
            : 'تقرير سجلات العملات';
    }

    public function mount(): void
    {
        $this->purchaseTransactions = collect();
        $this->paymentTransactions = collect();

        $this->form->fill([
            'customerId' => $this->customerId,
            'startDate' => $this->startDate,
            'endDate' => $this->endDate,
        ]);

        $this->loadData();
    }



    protected function getFormSchema(): array
    {
        return [
            Grid::make(3)
                ->schema([
                        Forms\Components\Select::make('customerId')
                            ->label('العميل')
                            ->options(Customer::per(ExpenseGroup::CURRENCY)->pluck('name', 'id'))
                            ->searchable()
                            ->reactive()
                            ->afterStateUpdated(fn() => $this->loadData()),

                        Forms\Components\DatePicker::make('startDate')
                            ->label('من تاريخ')
                            ->reactive()
                            ->afterStateUpdated(fn() => $this->loadData()),

                        Forms\Components\DatePicker::make('endDate')
                            ->label('إلى تاريخ')
                            ->reactive()
                            ->afterStateUpdated(fn() => $this->loadData()),
                    ]),
        ];
    }
    public function loadData(): void
    {
        if (!$this->customerId) {
            $this->reportData = [];
            return;
        }
        $this->reportData = app(CurrencyLogsService::class)->generateCurrencyLogsReport($this->customerId, $this->startDate, $this->endDate);
        // ربط أسعار الصرف للتعديل اللحظي 
        /* if (isset($this->reportData['purchase_transactions'])) {
            foreach ($this->reportData['purchase_transactions'] as $tr) {
                $this->editableRates[$tr['id']] = $tr['rate'];
            }
        } */
    }
    public function refreshBalance()
    {
        if ($this->customerId) {
            $customer = Customer::find($this->customerId);
            app(CurrencyLogsService::class)->updateCustomerBalance($customer);
            $this->loadData();
            $this->dispatch('notify', ['message' => 'تم تحديث الأرصدة بنجاح']);
        }
    }
}
