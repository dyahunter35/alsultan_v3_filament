<?php

namespace App\Models;

use App\Enums\PaymentOptions;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Supplying extends Model
{
    use HasFactory;

    /**
     * الحقول القابلة للتعبئة
     */
    /* protected $fillable = [
        'customer_id',
        'representative_id',
        'is_completed',
        'payment_method',
        'paid_amount',
        'statement',
        'payment_reference',
        'total_amount',
        'created_by',
    ]; */

    protected $guarded = [];

    /**
     * التحويل التلقائي للأنواع
     */
    protected $casts = [
        'is_completed' => 'boolean',
        'paid_amount' => 'double',
        'total_amount' => 'double',
        'payment_reference' => 'string',
        'payment_method' => PaymentOptions::class
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($supplying) {
            // تعيين المستخدم الذي أنشأ السجل تلقائيًا
            if (auth()->check()) {
                $supplying->created_by = auth()->id();
            }
            if ($supplying->is_completed)
                $supplying->paid_amount = $supplying->total_amount;
        });
        static::created(function ($supplying) {
            app('App\Services\CustomerService')->updateCustomerBalance(Customer::find($supplying->customer_id));
        });
        static::updated(function ($supplying) {
            app('App\Services\CustomerService')->updateCustomerBalance(Customer::find($supplying->customer_id));
        });
    }

    /**
     * العلاقة مع المستخدم المنفذ (User)
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    /**
     * العلاقة مع المندوب (Representative)
     */
    public function representative()
    {
        return $this->belongsTo(User::class, 'representative_id');
    }

    /**
     * العلاقة مع المستخدم الذي أنشأ السجل
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * سكوب لتصفية السجلات المكتملة فقط
     */
    public function scopeCompleted($query)
    {
        return $query->where('is_completed', true);
    }

    /**
     * سكوب لتصفية السجلات غير المكتملة فقط
     */
    public function scopeNotCompleted($query)
    {
        return $query->where('is_completed', false);
    }
}
