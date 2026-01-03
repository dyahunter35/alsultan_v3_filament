<?php

namespace App\Models;

use App\Enums\ContractStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class Contract extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'title',
        'company_id',
        'reference_no',
        'effective_date',
        'duration_months',
        'total_amount',
        'scope_of_services',
        'confidentiality_clause',
        'termination_clause',
        'governing_law',
        'status',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'effective_date' => 'date',
        'status' => ContractStatus::class,

    ];

    protected static function booted(): void
    {
        // This event fires AFTER a new StockHistory record is created.
        static::creating(function (Contract $history) {

            $history->reference_no = self::generateContractNumber();
        });
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function documents()
    {
        return $this->morphMany(Document::class, 'documentable');
    }

    public function items()
    {
        return $this->hasMany(ContractItem::class);
    }

    public static function generateContractNumber(): string
    {
        $prefix = 'REF-';
        $year = date('Y');
        $month = date('m');

        $lastInvoice = self::whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->orderBy('id', 'desc')
            ->withoutGlobalScope(SoftDeletingScope::class)
            ->first();

        $nextNumber = 1;

        if ($lastInvoice) {
            $parts = explode('-', $lastInvoice->reference_no);
            $lastNumber = (int) end($parts);
            $nextNumber = $lastNumber + 1;
        }

        return $prefix.$year.$month.'-'.str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }
}
