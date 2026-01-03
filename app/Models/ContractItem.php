<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContractItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'contract_id',
        'description',
        'size',
        'weight',
        'quantity',
        'unit_price',
        'machine_count',
    ];

    protected $casts = [
        'weight' => 'decimal:2',
        'quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
    ];

    // ✅ علاقة بالعقد
    public function contract()
    {
        return $this->belongsTo(Contract::class);
    }

    // ✅ Accessors لحساب الإجماليات (احتياطي لو استخدمتها في الواجهة)
    public function getTotalPriceAttribute()
    {
        return $this->quantity * $this->unit_price;
    }

    public function getTotalWeightAttribute()
    {
        return $this->quantity * $this->weight;
    }
}
