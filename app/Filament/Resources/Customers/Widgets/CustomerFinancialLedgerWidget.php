<?php

namespace App\Filament\Resources\Customers\Widgets;

use App\Models\Customer;
use Filament\Widgets\Widget;
use Illuminate\Support\Carbon;

class CustomerFinancialLedgerWidget extends Widget
{
    protected string $view = 'filament.resources.customers.widgets.customer-financial-ledger-widget';

    public ?Customer $record = null;

    protected int|string|array $columnSpan = 'full';

    public ?string $startDate = null;
    public ?string $endDate = null;

    protected function getListeners(): array
    {
        return [
            'updateLedgerDates' => 'updateDates',
        ];
    }

    public function updateDates($start, $end)
    {
        $this->startDate = $start;
        $this->endDate = $end;

        $this->emitSelf('$refresh');
    }

    public function getLedger(): array
    {
        if (!$this->record) {
            return [];
        }

        // استخدام الدالة الجديدة
        $ledgerItems = $this->record->financialLedger($this->startDate, $this->endDate);

        return $ledgerItems->toArray();
    }
}
