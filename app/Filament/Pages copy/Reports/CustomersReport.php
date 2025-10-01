<?php

namespace App\Filament\Pages\Reports;

use App\Filament\Pages\Concerns\HasPage;
use App\Filament\Pages\Concerns\HasSinglePage;
use App\Filament\Widgets\DateFilterWidget;
use App\Models\Expense;
use App\Models\OrderId;
use App\Models\Supplying;
use App\Models\User;
use Filament\Pages\Page;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;


class CustomersReport extends Page
{
    // protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static string $view = 'filament.pages.reports.customers-report';

    // ADDED: Public properties to hold the filter state on the page.
    public ?int $userId = null;
    public ?string $fromDate = null;
    public ?string $toDate = null;

    // This is just for the user dropdown, separate from the filters
    public $users;

    public static function getNavigationGroup(): ?string
    {
        return 'reports';
    }

    // ADDED: A proper event listener method.
    // This catches the event from the widget and updates the page's properties.
    #[On('update-chart-data')]
    public function updateReportData(?int $userId, ?string $fromDate, ?string $toDate): void
    {
        $this->userId = $userId;
        $this->fromDate = $fromDate;
        $this->toDate = $toDate;
    }

    protected function getViewData(): array
    {
        // Use the page's public properties for filtering.
        $userId = $this->userId ?? User::whereRoleIs('user')->first()?->id;

        // This list is just for populating UI elements if needed, separate from the filter logic.
        $this->users = User::whereRoleIs('user')->orderBy('name')->get();
        $user = User::find($userId);

        // Build the date filter using the page's properties.
        $dateFilter = function ($q) {
            if ($this->fromDate && !$this->toDate) {
                $q->whereDate('created_at', $this->fromDate);
            } elseif ($this->fromDate && $this->toDate) {
                $q->whereBetween('created_at', [$this->fromDate, $this->toDate]);
            }
        };

        // --- The rest of your query logic remains the same ---
        // It will now correctly use the filtered $userId and the $dateFilter closure.
        // For brevity, the rest of the query is omitted, but it should work as is.

        $orders = OrderId::query()->where('user_id', $userId)->when($this->fromDate || $this->toDate, $dateFilter)
            ->select(
                'created_at',
                'id as details_id', // Use order ID for details
                'total_price as amount',
                DB::raw("'order' as transaction_type"),
                DB::raw("order_status = 0 as is_pending"), // Unified status flag
                'order_from as causer_id'
            );


        $supplying = Supplying::query()->where('user_id', $userId)->when($this->fromDate || $this->toDate, $dateFilter)
            ->select(
                'created_at',
                DB::raw("statment COLLATE utf8mb4_unicode_ci as details"),
                'amount',
                DB::raw("'supplying' as transaction_type"),
                DB::raw("status = 1 as is_pending"), // Unified status flag
                'caused_by as causer_id'
            );



        $expenseType = $user?->permanent->value == 'normal' ? 'expense_credit' : 'expense_debit';
        $expenseColumn = $user?->permanent->value == 'normal' ? 'from_id' : 'user_id';

        $exp = Expense::where($expenseColumn, $userId)
            ->when($this->fromDate || $this->toDate, $dateFilter)
            ->select(
                'created_at',
                DB::raw("statement COLLATE utf8mb4_unicode_ci as details"),
                'total as amount',
                DB::raw("'$expenseType' as transaction_type"),
                DB::raw("payed = 0 as is_pending"), // Unified status flag
                'caused_by as causer_id'
            );

        $logs = $orders
            ->unionAll($supplying)
            ->unionAll($exp)
            ->orderBy('created_at', 'asc')
            ->get();

        // Manually Eager Load relationships for performance
        $causer_ids = $logs->pluck('causer_id')->unique()->filter();
        $causers = User::whereIn('id', $causer_ids)->select('id', 'name')->get()->keyBy('id');

        $order_ids = $logs->where('transaction_type', 'order')->pluck('details_id')->unique()->filter();
        $orderDetails = \App\Models\OrderData::whereIn('order_id', $order_ids)->with('product:id,name')->get()->groupBy('order_id');

        $logs->each(function ($log) use ($causers, $orderDetails) {
            $log->causer = $causers->get($log->causer_id);
            if ($log->transaction_type === 'order') {
                $log->orderData = $orderDetails->get($log->details_id);
            }
        });
        return [
            'user' => $user,
            'users' => $this->users,
            'logs' => $logs,
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            DateFilterWidget::class,
        ];
    }
}
