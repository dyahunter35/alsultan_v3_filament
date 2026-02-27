<?php

namespace App\Filament\Pages\Reports;

use App\Enums\ExpenseGroup;
use App\Filament\Pages\Concerns\HasReport;
use App\Models\Currency;
use App\Models\CurrencyBalance;
use App\Models\Customer;
use App\Services\CurrencyLogsService;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Grid;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Url;
use Malzariey\FilamentDaterangepickerFilter\Fields\DateRangePicker;

class CurrencyLogsReport extends Page implements Forms\Contracts\HasForms
{
    use Forms\Concerns\InteractsWithForms;
    use HasReport;

    protected static ?int $navigationSort = 33;

    protected string $view = 'filament.pages.reports.currency-logs-report';

    #[Url]
    public ?int $customerId = null;

    public ?Customer $customer = null;
    public array $balances = [];

    #[Url] public ?string $date_range = null;

    public Collection $purchaseTransactions;
    public Collection $paymentTransactions;

    public Collection $currencys;
    public $reportData;

    public function getReportSubject(): string
    {
        return $this->getTitle();
    }
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
        $this->currencys = Currency::select('id', 'code', 'name')->get();

        $this->form->fill([
            'customerId' => $this->customerId,
            'date_range' => $this->date_range,
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
        if (!$this->customerId) {
            $this->reportData = [];
            return;
        }
        [$from, $to] = parseDateRange($this->date_range);

        $this->reportData = app(CurrencyLogsService::class)->generateCurrencyLogsReport($this->customerId, $from, $to);
        // ربط أسعار الصرف للتعديل اللحظي 
        /* if (isset($this->reportData['purchase_transactions'])) {
            foreach ($this->reportData['purchase_transactions'] as $tr) {
                $this->editableRates[$tr['id']] = $tr['rate'];
            }
        } */
        $this->js("document.title = '{$this->getPrintTitle()}'");


    }
    public function refreshBalance()
    {
        if ($this->customerId) {
            DB::transaction(function () {
                $customer = Customer::find($this->customerId);
                app(CurrencyLogsService::class)->updateCustomerBalance($customer);
                CurrencyBalance::refreshBalances();
                $this->loadData();
                Notification::make()
                    ->title('تم تحديث بيانات العملاء')
                    ->success()
                    ->send();
            });
        }
    }
}
