<?php

namespace App\Models;

use App\Enums\ExpansesType;
use App\Enums\Payment;
use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    protected $guarded = [];

    protected $casts = [
        'expense_type' => ExpansesType::class,
        'payment_method' => Payment::class,
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (auth()->check()) {
                $model->created_by = auth()->id();
            }
        });
    }

    public function beneficiary()
    {
        return $this->belongsTo(User::class, 'beneficiary_id');
    }
    public function payer()
    {
        return $this->belongsTo(User::class, 'payer_id');
    }
    public function representative()
    {
        return $this->belongsTo(User::class, 'representative_id');
    }
    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }
}
