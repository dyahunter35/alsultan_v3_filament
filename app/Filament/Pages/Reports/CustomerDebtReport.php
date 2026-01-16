<?php

namespace App\Filament\Pages\Reports;

use App\Enums\ExpenseGroup;
use App\Filament\Pages\Concerns\HasReport;
use App\Models\Customer;
use App\Services\CustomerService;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Collection;

class CustomerDebtReport extends Page implements HasForms
{
    use InteractsWithForms;
    use HasReport;

    // protected static ?string $navigationIcon = 'heroicon-o-document-text';

    // protected static ?string $navigationLabel = 'تقرير كشف الديون للعملاء';

    // protected static ?string $title = 'تقرير كشف الديون للعملاء';

    protected static ?int $navigationSort = 35;

    protected string $view = 'filament.pages.reports.customer-debt-report';

    public Collection $customers;

    public function mount(): void
    {
        $this->loadData();
    }

    public function loadData(): void
    {
        // Get all customers and calculate their balance
        // We use getNetBalanceAttribute via the model's accessors

        // We might want to filter only customers who have debts or credits?
        // The request says "Report of debts", but usually that implies a full ledger status.
        // Let's fetch all for now, or maybe only those with non-zero balance if requested later.

        $this->customers = Customer::where('balance', '!=', 0)
            ->where('permanent', ExpenseGroup::SALE)
            ->get()
            ->map(
                fn($customer) => [
                    'name' => $customer->name,
                    'region' => $customer->address ?? '-', // Region not found in model
                    'balance' => $customer->balance,
                ]
            );
    }

    public function updateBalances(): void
    {
        app(CustomerService::class)->updateCustomersBalance();
        $this->loadData();
        Notification::make()
            ->title('تم تحديث بيانات العملاء')
            ->success()
            ->send();
    }
}
