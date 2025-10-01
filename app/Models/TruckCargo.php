<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TruckCargo extends Model
{
    use HasFactory;

    protected $fillable = [
        'id', 'truck_id', 'details_id', 'quantity', 'real_quantity', 'weight', 'unit_id', 'cateogrie_id', 'note'
    ];

    public function product()
    {
        return $this->hasOne(\App\Models\Product::class);
    }

    public function truck()
    {
        return $this->belongsTo(\App\Models\Truck::class); //->converte();
    }
}
