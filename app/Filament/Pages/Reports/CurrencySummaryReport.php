<?php

namespace App\Filament\Pages\Reports;

use App\Filament\Pages\Concerns\HasReport;
use App\Models\Company;
use App\Models\Currency;
use App\Models\Customer;
use Filament\Pages\Page;
use Filament\Schemas\Components\Grid;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Illuminate\Database\Eloquent\Model;
use Livewire\Attributes\Url;
use Filament\Forms\Components\DatePicker;
use Carbon\Carbon;

class CurrencySummaryReport extends Page implements Forms\Contracts\HasForms
{
    use HasReport;

    use InteractsWithForms;
    protected string $view = 'filament.pages.reports.currency-summary-report';

    public $ledger;
    public $type = 'customers';
    public $currencies;

    public $total = [];
    public $total_converted = 0;

    // الإجمالي الكلي لكل عملة على حدة
    public $currencyTotals = [];

    #[Url(as: 'rates', except: [])]
    public $keys;

    // خاصية التاريخ للفلترة الزمنية
    #[Url(except: '')]
    public ?string $date = null;

    public function mount()
    {
        // تهيئة التاريخ الافتراضي
        if (is_null($this->date)) {
            $this->date = now()->format('Y-m-d');
        }

        $this->currencies = Currency::get();
        if (empty($this->keys)) {
            // القيمة الافتراضية للوحدة الأساسية (SDG)
            $this->keys['sd'] = 1;

            // تحميل أسعار الصرف الافتراضية
            foreach ($this->currencies as $currency) {
                $this->keys[$currency->code] = $currency->exchange_rate;
            }
        }

        // استخدام دالة محدثة للتاريخ لضمان تدفق الحساب الصحيح
        $this->updatedDate($this->date);
    }

    public function updatedType($type): void
    {
        $this->loadLedger($type);
        $this->updatedKeys();
    }

    public function updatedDate($date): void
    {
        $this->loadLedger($this->type);
        $this->updatedKeys();
    }

    public function loadLedger($type = 'customers'): void
    {
        if (!$type) {
            $this->ledger = collect();
            return;
        }

        // ملاحظة لتحسين الأداء:
        // لتقليل الاستعلامات، يفضل هنا استخدام Eager Loading إذا كانت أرصدة العملات تأتي من علاقة
        // مثال: $this->ledger = Customer::with('balances')->get();

        if ($type == 'customers') {
            $this->ledger = Customer::get();
        } else {
            $this->ledger = Company::get();
        }

        // ملاحظة: إذا كان `$user->balance` و `$user->currencyValue($id)` يعتمد على التاريخ،
        // يجب تمرير `$this->date` لتلك الدوال.
    }

    /**
     * حساب إجمالي أرصدة المستخدم المحولة إلى الوحدة المرجعية (SDG).
     */
    public function calculate(Model $user)
    {
        $this->total[$user->id] = 0;

        // 1. حساب الرصيد الأساسي (جنية سوداني) بالوحدة المرجعية (SDG / keys['sd'])
        $this->total[$user->id] += $this->average($user->balance ?? 0, $this->keys['sd']);

        // 2. حساب أرصدة العملات الأخرى وتحويلها
        foreach ($this->currencies as $currency) {
            $currencyCode = $currency->code;
            $accountId = $currency->id;

            $accountBalance = $user->currencyValue($accountId);

            // التعامل مع الدولار (USD) كعملة مرجعية أجنبية
            if ($currencyCode == 'USD') {
                $this->total[$user->id] += $accountBalance;
                continue;
            }

            // لجميع العملات الأخرى: التحويل إلى الوحدة المرجعية
            $rate = $this->keys[$currencyCode] ?? 1;
            $this->total[$user->id] += $this->average($accountBalance, $rate);
        }

        return $this->total[$user->id];
    }

    /**
     * يتم تشغيل هذه الدالة تلقائياً بواسطة Livewire عند تغيير أي قيمة في مصفوفة $keys.
     */
    public function updatedKeys(): void
    {
        // إعادة تشغيل الحساب لكل عميل/شركة في الـ ledger
        if ($this->ledger) {
            foreach ($this->ledger as $user) {
                $this->calculate($user);
            }
        }

        // تحديث الإجمالي الكلي المحول وإجمالي العملات
        $this->getTotalConverted();
        $this->calculateCurrencyTotals();
    }

    /**
     * حساب الإجمالي الكلي المحول لجميع المستخدمين.
     */
    public function getTotalConverted()
    {
        $this->total_converted = 0;

        if ($this->ledger) {
            foreach ($this->ledger as $user) {
                // نستخدم القيمة المخزنة في $this->total بعد أن تم حسابها في updatedKeys
                $this->total_converted += $this->total[$user->id] ?? 0;
            }
        }
        return $this->total_converted;
    }

    /**
     * حساب إجمالي الأرصدة لكل عملة على حدة (التحسين رقم 2).
     */
    public function calculateCurrencyTotals(): void
    {
        $this->currencyTotals = [];

        // إجمالي الجنيه السوداني
        $this->currencyTotals['sd'] = $this->ledger?->sum('balance') ?? 0;

        foreach ($this->currencies as $currency) {
            $total = 0;
            $currencyId = $currency->id;

            if ($this->ledger) {
                // يجب هنا تجميع قيمة currencyValue() لكل المستخدمين
                foreach ($this->ledger as $user) {
                    $total += $user->currencyValue($currencyId) ?? 0;
                }
            }
            $this->currencyTotals[$currency->code] = $total;
        }
    }

    public function average($account, $rate)
    {
        $rate = (is_numeric($rate) && $rate > 0) ? $rate : 1;

        if ($account == 0) {
            return 0;
        }
        return $account / $rate;
    }

    protected function getFormSchema(): array
    {
        return [
            Grid::make(4)
                ->schema([
                    Forms\Components\Select::make('type')
                        ->label('عرض')
                        ->options([
                            'customers' => __('customer.navigation.plural_label'),
                            'companies' => __('company.navigation.plural_label')
                        ])
                        ->searchable()
                        ->reactive(),

                    // التحسين رقم 3: إضافة حقل التاريخ
                    DatePicker::make('date')
                        ->label('عرض الأرصدة حتى تاريخ')
                        ->default(now()->format('Y-m-d'))
                        ->reactive()
                        ->required()
                        ->columnSpan(2),
                ])
        ];
    }
}
