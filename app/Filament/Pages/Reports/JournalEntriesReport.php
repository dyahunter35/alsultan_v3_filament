<?php

namespace App\Filament\Pages\Reports;

use App\Filament\Pages\Concerns\HasReport;
use App\Models\CurrencyTransaction;
use App\Models\Expense;
use App\Models\Order;
use App\Models\Supplying;
use Carbon\Carbon;
use Filament\Pages\Page;
use Livewire\Attributes\Url;

class JournalEntriesReport extends Page
{
    use HasReport;

    protected string $view = 'filament.pages.reports.journal-entries';

    protected static ?int $navigationSort = 35;

    #[Url()]
    public $date;

    public $include_previous = true; // toggle button default = yes

    public function mount()
    {
        if (! $this->date) {
            $this->date = now()->toDateString();
        }
    }

    /** 🔵 الرصيد المرحل قبل اليوم */
    public function getOpeningBalance()
    {
        $day = Carbon::parse($this->date)->startOfDay();

        // 🔵 المخرجات: (تقلل الرصيد)
        $expenses = Expense::where('created_at', '<', $day)->sum('total_amount');
        $supplyings = Supplying::where('created_at', '<', $day)->sum('total_amount');

        // 🔵 المدخلات: (تزيد الرصيد)
        $sales = Order::where('created_at', '<', $day)->sum('total');

        // 🔵 تحويلات العملة — SEND تنقص, CONVERT تتحسب بالـ total (وليس amount)
        $currencySend = CurrencyTransaction::where('type', 'send')
            ->where('created_at', '<', $day)
            ->sum('amount');

        $currencyConvert = CurrencyTransaction::where('type', 'convert')
            ->where('created_at', '<', $day)
            ->sum('total'); // ← الصحيح

        // 🔵 الصيغة النهائية
        return ($sales + $currencyConvert) - ($expenses + $supplyings + $currencySend);
    }

    /** 🔵 أرباح/خسائر اليوم فقط */
    public function getTodayProfit()
    {
        $day = Carbon::parse($this->date);

        $expenses = Expense::whereDate('created_at', $day)->sum('total_amount');
        $sales = Order::whereDate('created_at', $day)->sum('total');
        $supplyings = Supplying::whereDate('created_at', $day)->sum('total_amount');
        $currency = CurrencyTransaction::whereDate('created_at', $day)->sum('amount');

        return $sales - ($expenses + $supplyings + $currency);
    }

    /** 🔵 الصافي النهائي بناءً على التوجّل */
    public function getFinalBalance()
    {
        return $this->include_previous
            ? $this->getOpeningBalance() + $this->getTodayProfit()
            : $this->getTodayProfit();
    }

    /** 🔵 قيود اليومية */
    public function getJournalEntries()
    {
        $day = Carbon::parse($this->date);

        return collect([
            Expense::selectRaw("'مصروف' AS type, notes, total_amount AS debit, 0 AS credit, created_at")
                ->whereDate('created_at', $day),

            Order::selectRaw("'بيع' AS type, 'عملية بيع' AS notes, 0 AS debit, total AS credit, created_at")
                ->whereDate('created_at', $day),

            Supplying::selectRaw("'توريد' AS type, 'عملية توريد' AS notes, 0 AS debit, total_amount AS credit, created_at")
                ->whereDate('created_at', $day),

            CurrencyTransaction::selectRaw("'تحويل' AS type, note AS notes, amount AS debit, 0 AS credit, created_at")
                ->whereDate('created_at', $day),
        ])
            ->flatMap(fn ($query) => $query->get())
            ->sortBy('created_at')
            ->values();
    }
}
