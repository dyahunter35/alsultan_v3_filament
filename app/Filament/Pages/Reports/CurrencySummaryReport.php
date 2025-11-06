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

    #[Url(as: 'rates', except: [])]
    public $keys;

    public function mount()
    {
        $this->currencies = Currency::get();
        if (empty($this->keys)) {
            // القيمة الافتراضية للوحدة الأساسية (sd) - نفترض أنها الجنيه السوداني (SDG)
            $this->keys['sd'] = 1;

            // تحميل أسعار الصرف الافتراضية
            foreach ($this->currencies as $currency) {
                $this->keys[$currency->code] = $currency->exchange_rate;
            }
        }

        $this->loadLedger();

        // **هام:** إجراء الحساب الأولي عند تحميل الصفحة
        $this->updatedKeys();
    }

    public function updatedType($type): void
    {
        $this->loadLedger($type);
        // إعادة الحساب بالبيانات الجديدة
        $this->updatedKeys();
    }

    public function loadLedger($type = 'customers'): void
    {
        if (!$type) {
            $this->ledger = collect();
            return;
        }

        if ($type == 'customers') {
            $this->ledger = Customer::limit(5)->get();
        } else {
            $this->ledger = Company::get();
        }
    }

    /**
     * حساب إجمالي أرصدة المستخدم المحولة إلى الوحدة المرجعية (SDG).
     */
    public function calculate(Model $row)
    {
        // تهيئة الإجمالي لهذا المستخدم
        $this->total[$row->id] = 0;

        // 1. حساب الرصيد الأساسي (جنية سوداني) بالوحدة المرجعية (SDG / keys['sd'])
        // نفترض أن $row->balance هو الرصيد بالجنية السوداني
        $this->total[$row->id] += $this->average($row->balance ?? 0, $this->keys['sd']);

        // 2. حساب أرصدة العملات الأخرى وتحويلها
        foreach ($this->currencies as $currency) {
            $currencyCode = $currency->code;
            $accountId = $currency->id;

            // الحصول على رصيد المستخدم بالعملة الحالية
            // نفترض أن currencyValue يأخذ ID العملة ويعيد الرصيد
            $accountBalance = $row->currencyValue($accountId);

            // إذا كانت العملة هي الدولار (USD)، نضيف رصيدها مباشرة
            // نفترض أن الدولار هو عملة التحويل الأساسية للعملات الأجنبية الأخرى
            // **هنا يجب التأكد من عملة التحويل النهائية التي تريدها**
            if ($currencyCode == 'USD') {
                $this->total[$row->id] += $accountBalance;
                continue;
            }

            // لجميع العملات الأخرى: قم بالتحويل إلى الوحدة المرجعية
            // $this->keys[$currencyCode] يجب أن يكون سعر صرف العملة مقابل الوحدة المرجعية (SDG)
            $rate = $this->keys[$currencyCode] ?? 1; // استخدام 1 كقيمة افتراضية إذا لم يتم العثور على سعر الصرف

            // التحويل: المبلغ / سعر الصرف
            $this->total[$row->id] += $this->average($accountBalance, $rate);
        }

        return $this->total[$row->id];
    }

    /**
     * يتم تشغيل هذه الدالة تلقائياً بواسطة Livewire عند تغيير أي قيمة في مصفوفة $keys.
     */
    public function updatedKeys(): void
    {
        // إعادة تشغيل الحساب لكل عميل/شركة في الـ ledger
        if ($this->ledger) {
            foreach ($this->ledger as $row) {
                $this->calculate($row);
            }
        }

        // تحديث الإجمالي الكلي المحول
        $this->getTotalConverted();
    }

    /**
     * حساب الإجمالي المحول لجميع المستخدمين.
     */
    public function getTotalConverted()
    {
        $this->total_converted = 0;

        // يجب استخدام $this->ledger وليس $this->rows (تم التصحيح)
        if ($this->ledger) {
            foreach ($this->ledger as $row) {
                // نعتمد على القيمة المخزنة في $this->total لتجنب إعادة الحساب
                $this->total_converted += $this->total[$row->id] ?? 0;
            }
        }
        return $this->total_converted;
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
            Grid::make(3)
                ->schema([
                    Forms\Components\Select::make('type')
                        ->label('عرض')
                        ->options([
                            'customers' => __('customer.navigation.plural_label'),
                            'companies' => __('company.navigation.plural_label')
                        ])
                        ->searchable()
                        ->reactive(),

                ])
        ];
    }
}
