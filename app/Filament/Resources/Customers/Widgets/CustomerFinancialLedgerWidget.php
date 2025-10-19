<?php

namespace App\Filament\Resources\Customers\Widgets;

use App\Models\Customer;
use Filament\Widgets\Widget;
use Illuminate\Support\Carbon;

class CustomerFinancialLedgerWidget extends Widget
{
    protected string $view = 'filament.resources.customers.widgets.customer-financial-ledger-widget';

    public ?Customer $record = null;

    protected int | string | array $columnSpan = 'full';

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

        $this->emitSelf('refreshWidget'); // لو حابب تعيد تحميل الداتا
    }

    public function getLedger(): array
    {
        $customer = $this->record;
        if (!$customer) return [];

        $ledgerItems = $customer->financialLedgerFromTo($this->startDate, $this->endDate);

        $balance = $ledgerItems[0]['balance'] ?? 0; // الرصيد الافتتاحي

        $rows = [];
        foreach ($ledgerItems as $item) {
            $rows[] = $item;
        }

        return $rows;
    }
}
