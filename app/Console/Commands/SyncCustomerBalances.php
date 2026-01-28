<?php

namespace App\Console\Commands;

use App\Models\Customer;
use App\Services\CurrencyLogsService;
use Illuminate\Console\Command;

class SyncCustomerBalances extends Command
{
    /**
     * اسم الأمر الذي ستكتبه في الـ Terminal
     */
    protected $signature = 'app:sync-balances {--customer= : تحديث عميل محدد فقط بواسطة ID}';

    /**
     * وصف الأمر
     */
    protected $description = 'إعادة حساب وتحديث الأرصدة الحقيقية للعملاء بالسوداني بناءً على كافة العمليات';

    public function handle(CurrencyLogsService $service)
    {
        $customerId = $this->option('customer');

        if ($customerId) {
            $customer = Customer::find($customerId);
            if (!$customer) {
                $this->error("العميل رقم {$customerId} غير موجود!");
                return;
            }
            $this->sync($customer, $service);
            $this->info("✅ تم تحديث رصيد العميل: {$customer->name}");
            return;
        }

        $customers = Customer::all();
        $count = $customers->count();

        $this->info("🚀 جاري بدء تحديث أرصدة ({$count}) عميل...");

        // شريط تقدم بصري في الـ Terminal
        $bar = $this->output->createProgressBar($count);
        $bar->start();

        foreach ($customers as $customer) {
            $service->updateCustomerBalance($customer);
            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);
        $this->info("🎯 تمت العملية بنجاح! جميع الأرصدة الآن مطابقة للواقع.");
    }

    private function sync($customer, $service)
    {
        $service->syncCustomerRealBalance($customer->id);
    }
}