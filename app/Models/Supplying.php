<?php

namespace App\Models;

use App\Enums\PaymentOptions;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Supplying extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'user_id','rep_id', 'amount', 'total_amount', 'caused_by',
        'payment_serial', 'payment_function', 'statment', 'status', 'created_at',
    ];

    protected $hidden = [
        'updated_at', 'user_id'
    ];

    protected $attributes = [
        'total_amount' => 0,
    ];
    protected $casts = [
        'payment_function' => PaymentOptions::class,
    ];

    public function users()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function causers()
    {
        return $this->belongsTo(User::class, 'caused_by', 'id');
    }
    public function to()
    {
        return $this->belongsTo(User::class, 'rep_id', 'id');
    }

    public function getUserNameAttribute()
    {
        return User::find($this->user_id)->name;
    }
}
