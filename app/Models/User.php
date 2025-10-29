<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Traits\HasFinancials;
use Attribute;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\HasTenants;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements HasTenants
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;
    use SoftDeletes;
    use HasRoles;
    use HasFinancials;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];
    protected $appends = [
        'rep_name',
    ];
    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }


    public function scopeSales($query)
    {
        return User::role('sales')->pluck('name', 'id');
    }

    public function getNameAttribute($value): string
    {
        return ($this->hasRole('sales')) ?  $value .  " (مندوب)" : $value;
    }

    protected function repName(): Attribute
    {
        // #TODO :: update role
        return Attribute::make(
            get: fn() => $this->name . ($this->hasRole('super_admin') ? ' - مندوب' : ''),
        );
    }
    public function getRepNameAttribute()
    {
        return  $this->name . ($this->hasRole('مندوب') ? ' - مندوب' : '');
    }

    public function branch(): BelongsToMany
    {
        return $this->belongsToMany(Branch::class);
    }

    public function getTenants(Panel $panel): Collection
    {
        return $this->branch;
    }

    public function canAccessTenant(Model $tenant): bool
    {
        return $this->branch()->whereKey($tenant)->exists();
    }

    // المصاريف التي دفعها هذا المستخدم
    public function expensesPaid(): MorphMany
    {
        return $this->morphMany(Expense::class, 'payer');
    }

    // المصاريف التي استفاد منها
    public function expensesBeneficiary()
    {
        return $this->morphMany(Expense::class, 'beneficiary');
    }

    // المصاريف التي أنشأها
    public function expensesCreated()
    {
        return $this->hasMany(Expense::class, 'created_by');
    }
}
