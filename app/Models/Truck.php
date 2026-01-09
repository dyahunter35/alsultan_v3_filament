<?php

namespace App\Models;

use App\Enums\Country;
use App\Enums\ExpenseGroup;
use App\Enums\TruckState;
use App\Enums\TruckType;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Truck extends Model implements HasMedia
{
    use HasFactory;
    use InteractsWithMedia;
    use SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        'truck_status' => TruckState::class,
        // 'arrive_date' =>  Carbon::class,
        'type' => TruckType::class,
        'country' => Country::class,
    ];

    public function scopeConverte($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeLocal($query)
    {
        return $query->where('type', 2);
    }

    public function scopeFromBy(Builder $query, Model $from): Builder
    {
        return $query
            ->where('from_type', $from->getMorphClass())
            ->where('from_id', $from->getKey());
    }

    public function scopeOut($query)
    {
        return $query->where('type', 1);
    }

    public function from(): MorphTo
    {
        return $this->morphTo();
    }

    public function cargos()
    {
        return $this->hasMany(\App\Models\TruckCargo::class, 'truck_id', 'id')->orderBy('id', 'DESC');
    }

    public function documents()
    {
        return $this->morphMany(Document::class, 'documentable');
    }

    public function trans()
    {
        return $this->hasMany(\App\Models\StoreTransaction::class, 'truck_id', 'id')->orderBy('id', 'DESC');
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function notes()
    {
        return $this->hasMany(\App\Models\TruckNote::class, 'truck_id', 'id')->orderBy('id', 'DESC');
    }

    public function toBranch()
    {
        return $this->hasOne(\App\Models\Branch::class, 'id', 'branch_to');
    }

    public function companyId()
    {
        return $this->belongsTo(\App\Models\Company::class, 'company_id');
    }

    public function contractorInfo()
    {
        return $this->belongsTo(\App\Models\Company::class, 'contractor_id');
    }

    public function isConverted(): bool
    {
        return boolval($this->is_converted);
    }

    // add new method
    public function convertOuter()
    {
        $cargos = $this->cargos;

        foreach ($cargos as $cargo) {
            Product::create([
                'details_id' => $cargo->details_id,
                'store_id' => $this->record->to,
                'stored_by' => \Auth::user()->id,
                'quantity' => $cargo->quantity,
                'price' => '0',
                'note' => $cargo->note,
                'truck_id' => $this->record->id,
            ]);
        }

        $this->is_converted = true;
    }

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function contractor()
    {
        return $this->belongsTo(Company::class, 'contractor_id');
    }

    public function expenses()
    {
        return $this->hasMany(Expense::class);
    }

    public function stockHistory()
    {
        return $this->hasMany(StockHistory::class, 'truck_id');
    }

    public function taxExpenses()
    {
        return $this->hasMany(Expense::class)->whereIn(
            'expense_type_id',
            ExpenseType::where('group', ExpenseGroup::TAX)->pluck('id')
        );
    }

    public function shipmentExpenses()
    {
        return $this->hasMany(Expense::class)->whereIn(
            'expense_type_id',
            ExpenseType::where('group', ExpenseGroup::SHIPMENT_CLEARANCE)->pluck('id')
        );
    }

    public function customExpenses()
    {
        return $this->hasMany(Expense::class)->whereIn(
            'expense_type_id',
            ExpenseType::where('group', ExpenseGroup::CUSTOMS)->pluck('id')
        );
    }

    public function certificateExpenses()
    {
        return $this->hasMany(Expense::class)->whereIn(
            'expense_type_id',
            ExpenseType::where('group', ExpenseGroup::CERTIFICATES)->pluck('id')
        );
    }

    public function calculateCostPerGram(): float
    {
        // 🧾 1. جمع جميع المصاريف المرتبطة بالشاحنة
        $totalExpenses = $this->expenses()->sum('total_amount');

        // 🚛 2. أجرة النولون من نفس الشاحنة
        $freightCost = floatval($this->truck_fare ?? 0);

        // ⏱️ 3. تكلفة الأيام الإضافية
        $extraDaysCost = ($this->diff_trip ?? 0) * ($this->delay_day_value ?? 0);

        // 🧮 4. حساب الوزن الكلي للبضائع
        $totalWeight = $this->cargos()->sum('weight');

        if ($totalWeight <= 0) {
            return 0; // تجنب القسمة على صفر
        }

        // 💰 5. إجمالي التكاليف الفعلية
        $totalCost = $totalExpenses + $freightCost + $extraDaysCost;

        // ⚖️ 6. حساب تكلفة الجرام الواحد
        return $totalCost / $totalWeight;
    }

    public function calculateProductsCosts(): array
    {
        $costPerGram = $this->calculateCostPerGram();
        $productsCosts = [];

        foreach ($this->cargos as $cargo) {
            $productWeight = $cargo->weight ?? 0;
            $productCost = $productWeight * $costPerGram;

            $productsCosts[] = [
                'cargo_id' => $cargo->id,
                'product_id' => $cargo->details_id,
                'weight' => $productWeight,
                'cost_per_gram' => round($costPerGram, 4),
                'total_cost' => round($productCost, 2),
            ];
        }

        return $productsCosts;
    }

    protected function totalWeight(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->cargos()->sum('weight'),
            // set: fn (string $value) => strtolower($value),
        );
    }

    protected function totalTonWeight(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->cargos()->sum('ton_weight'),
            // set: fn (string $value) => strtolower($value),
        );
    }

    protected function truckFareSum(): Attribute
    {
        $nolon = (float) $this->truck_fare ?? 0;
        $extraDaysCost = (float) $this->delay_value ?? 0;
        $totalExpenses = $nolon + $extraDaysCost;

        return Attribute::make(
            get: fn () => $totalExpenses,
            // set: fn (string $value) => strtolower($value),
        );
    }
}
