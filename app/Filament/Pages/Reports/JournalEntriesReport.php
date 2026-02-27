<?php

namespace App\Filament\Pages\Reports;

use App\Filament\Pages\Concerns\HasReport;
use App\Models\CurrencyTransaction;
use App\Models\Expense;
use App\Models\Order;
use App\Models\Supplying;
use Carbon\Carbon;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Filament\Schemas\Components\Grid;
use Livewire\Attributes\Url;

class JournalEntriesReport extends Page implements HasForms
{
    use HasReport;
    use InteractsWithForms;

    protected string $view = 'filament.pages.reports.journal-entries';
    protected static ?int $navigationSort = 35;

    #[Url()]
    public $date;

    #[Url()]
    public $include_previous = true;

    /** 🟢 تعريف الفورم **/
    public function getFormSchema(): array
    {
        return [
            Grid::make(2)->schema([

                DatePicker::make('date')
                    ->label('تاريخ التقرير')
                    ->default(now())
                    ->live() // تحديث لحظي للبيانات عند تغيير التاريخ
                    ->native(false),

                ToggleButtons::make('include_previous')
                    ->label('عرض الرصيد السابق؟')
                    ->options([
                            true => 'نعم (تراكمي)',
                            false => 'لا (حركة اليوم فقط)',
                        ])
                    ->colors([
                            true => 'success',
                            false => 'gray',
                        ])
                    ->icons([
                            true => 'heroicon-m-check-circle',
                            false => 'heroicon-m-x-circle',
                        ])
                    ->default(true)
                    ->live()
                    ->inline()
                    ->columnSpan(1),
            ])

        ];
    }

    public function mount()
    {
        if (!$this->date) {
            $this->date = now()->toDateString();
        }

        // تعبئة الفورم من الـ URL عند التحميل
        $this->form->fill([
            'date' => $this->date,
            'include_previous' => $this->include_previous,
        ]);
    }

    /** 🔵 الرصيد الافتتاحي (قبل التاريخ المحدد) */
    public function getOpeningBalance()
    {
        if (!$this->date)
            return 0;

        $targetDate = Carbon::parse($this->date)->startOfDay();

        // المدخلات (+)
        $sales = Order::where('created_at', '<', $targetDate)->sum('total');
        $supplyings = Supplying::where('created_at', '<', $targetDate)->sum('total_amount'); // تأكد إذا كان التوريد "دخل" أم "خرج" لشركتك
        $currencyConvert = CurrencyTransaction::where('type', 'convert')
            ->where('created_at', '<', $targetDate)
            ->sum('total');

        // المخرجات (-)
        $expenses = Expense::where('created_at', '<', $targetDate)->sum('total_amount');
        $currencySend = CurrencyTransaction::where('type', 'send')
            ->where('created_at', '<', $targetDate)
            ->sum('amount');

        return ($sales + $supplyings + $currencyConvert) - ($expenses + $currencySend);
    }

    /** 🔵 صافي حركة اليوم المحدد */
    public function getTodayProfit()
    {
        if (!$this->date)
            return 0;
        $day = Carbon::parse($this->date);

        // التدفقات النقدية الداخلة
        $in = Order::whereDate('created_at', $day)->sum('total') +
            Supplying::whereDate('created_at', $day)->sum('total_amount');

        // التدفقات النقدية الخارجة
        $out = Expense::whereDate('created_at', $day)->sum('total_amount') +
            CurrencyTransaction::whereDate('created_at', $day)->where('type', 'send')->sum('amount');

        return $in - $out;
    }

    /** 🔵 الصافي النهائي للتقرير */
    public function getFinalBalance()
    {
        return $this->include_previous
            ? $this->getOpeningBalance() + $this->getTodayProfit()
            : $this->getTodayProfit();
    }

    /** 🔵 جلب قيود اليومية */
    public function getJournalEntries()
    {
        if (!$this->date)
            return collect();
        $day = Carbon::parse($this->date);

        $this->js("document.title = '{$this->getReportSubject()}'");
        return collect([
            Expense::selectRaw("'مصروف' AS type, notes AS description, total_amount AS debit, 0 AS credit, created_at")
                ->whereDate('created_at', $day),

            Order::selectRaw("'بيع' AS type, 'عملية بيع رقم ' || id AS description, 0 AS debit, total AS credit, created_at")
                ->whereDate('created_at', $day),

            Supplying::selectRaw("'توريد' AS type, 'عملية توريد' AS description, 0 AS debit, total_amount AS credit, created_at")
                ->whereDate('created_at', $day),

            CurrencyTransaction::selectRaw("'تحويل' AS type, note AS description, amount AS debit, 0 AS credit, created_at")
                ->whereDate('created_at', $day),
        ])
            ->flatMap(fn($query) => $query->get())
            ->sortBy('created_at')
            ->values();
    }
}
