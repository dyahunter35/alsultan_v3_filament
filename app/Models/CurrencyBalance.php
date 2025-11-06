<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Enums\CurrencyType;


class CurrencyBalance extends Model
{
    protected $fillable = [
        'owner_id',
        'owner_type',
        'currency_id',
        'amount',
        'total_in_sdg'
    ];

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }

    public function owner()
    {
        return $this->morphTo();
    }

    public function updateTotal()
    {
        $this->total_in_sdg = $this->amount * $this->currency->exchange_rate;
        $this->save();
    }


    public static function refreshAllBalances()
    {
        // 1️⃣ تأثير الـ payer
        $payerBalances = DB::table('currency_transactions as ct')
            ->join('currencies as c', 'ct.currency_id', '=', 'c.id')
            ->select(
                'ct.payer_id as owner_id',
                'ct.payer_type as owner_type',
                'ct.currency_id',
                'c.exchange_rate',
                DB::raw('-ct.amount as net_amount')
            )
            ->whereNotNull('ct.payer_id')
            ->whereNotNull('ct.payer_type');

        // 2️⃣ تأثير الـ party
        $partyBalances = DB::table('currency_transactions as ct')
            ->join('currencies as c', 'ct.currency_id', '=', 'c.id')
            ->select(
                'ct.party_id as owner_id',
                'ct.party_type as owner_type',
                'ct.currency_id',
                'c.exchange_rate',
                DB::raw('ct.amount as net_amount')

            )
            ->whereNotNull('ct.party_id')
            ->whereNotNull('ct.party_type');

        // 3️⃣ دمج النتائج باستخدام unionAll
        $allBalances = $payerBalances->unionAll($partyBalances)->get();

        // 4️⃣ تجميع النتائج في PHP لكل owner/عملة
        $grouped = [];
        foreach ($allBalances as $row) {
            $key = $row->owner_type . ':' . $row->owner_id . ':' . $row->currency_id;
            if (!isset($grouped[$key])) {
                $grouped[$key] = [
                    'owner_id' => $row->owner_id,
                    'owner_type' => $row->owner_type,
                    'currency_id' => $row->currency_id,
                    'exchange_rate' => $row->exchange_rate,
                    'net_amount' => 0,
                ];
            }
            $grouped[$key]['net_amount'] += $row->net_amount;
        }

        // 5️⃣ تحديث أو إنشاء السجلات
        foreach ($grouped as $row) {
            static::updateOrCreate(
                [
                    'owner_id' => $row['owner_id'],
                    'owner_type' => $row['owner_type'],
                    'currency_id' => $row['currency_id'],
                ],
                [
                    'amount' => $row['net_amount'],
                    'total_in_sdg' => $row['net_amount'] * $row['exchange_rate'],
                ]
            );
        }
    }

    public static function refreshBalances(array $owners = []): void
    {
        // دالة مساعدة لتطبيق شرط المالكين إذا تم تمريرهم
        $applyOwnerFilter = function ($query) use ($owners) {
            if (empty($owners)) {
                return;
            }

            $query->where(function ($q) use ($owners) {
                foreach ($owners as $owner) {
                    [$type, $id] = $owner;
                    $q->orWhere(function ($q2) use ($type, $id) {
                        $q2->where('ct.payer_type', $type)
                            ->where('ct.payer_id', $id)
                            ->orWhere(function ($q3) use ($type, $id) {
                                $q3->where('ct.party_type', $type)
                                    ->where('ct.party_id', $id);
                            });
                    });
                }
            });
        };

        // 1️⃣ تأثير الـ payer (بالسالب)
        $payerBalances = DB::table('currency_transactions as ct')
            ->join('currencies as c', 'ct.currency_id', '=', 'c.id')
            ->select(
                'ct.payer_id as owner_id',
                'ct.payer_type as owner_type',
                'ct.currency_id',
                'c.exchange_rate',
                DB::raw("
                    CASE
                        WHEN ct.type = '" . CurrencyType::SEND->value . "' THEN -ct.amount
                        WHEN ct.type = '" . CurrencyType::Convert->value . "' THEN ct.amount
                        WHEN ct.type = '" . CurrencyType::CompanyExpense->value . "' THEN -ct.amount
                        ELSE 0
                    END as net_amount
                ")
            )
            ->whereNotNull('ct.payer_id')
            ->whereNotNull('ct.payer_type');

        $applyOwnerFilter($payerBalances);

        // 2️⃣ تأثير الـ party (بالموجب)
        $partyBalances = DB::table('currency_transactions as ct')
            ->join('currencies as c', 'ct.currency_id', '=', 'c.id')
            ->select(
                'ct.party_id as owner_id',
                'ct.party_type as owner_type',
                'ct.currency_id',
                'c.exchange_rate',
                DB::raw('ct.amount as net_amount')
            )
            ->whereNotNull('ct.party_id')
            ->whereNotNull('ct.party_type');

        $applyOwnerFilter($partyBalances);

        // 3️⃣ دمج النتائج
        $allBalances = $payerBalances->unionAll($partyBalances)->get();

        // 4️⃣ تجميع النتائج في PHP لكل owner/عملة
        $grouped = [];
        foreach ($allBalances as $row) {
            $key = $row->owner_type . ':' . $row->owner_id . ':' . $row->currency_id;
            if (!isset($grouped[$key])) {
                $grouped[$key] = [
                    'owner_id' => $row->owner_id,
                    'owner_type' => $row->owner_type,
                    'currency_id' => $row->currency_id,
                    'exchange_rate' => $row->exchange_rate,
                    'net_amount' => 0,
                ];
            }
            $grouped[$key]['net_amount'] += $row->net_amount;
        }

        // 5️⃣ تحديث أو إنشاء السجلات
        foreach ($grouped as $row) {
            CurrencyBalance::updateOrCreate(
                [
                    'owner_id' => $row['owner_id'],
                    'owner_type' => $row['owner_type'],
                    'currency_id' => $row['currency_id'],
                ],
                [
                    'amount' => $row['net_amount'],
                    'total_in_sdg' => $row['net_amount'] * $row['exchange_rate'],
                ]
            );
        }
    }
}
