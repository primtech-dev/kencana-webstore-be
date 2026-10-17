<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Branch extends Model
{
    use SoftDeletes;

    protected $table = 'branches';

    protected $casts = [
        'latitude' => 'double',
        'longitude' => 'double',
        'is_active' => 'boolean',
    ];

    protected $fillable = [
        'code',
        'name',
        'address',
        'city',
        'province',
        'phone',
        'latitude',
        'longitude',
        'manager_user_id',
        'is_active',
    ];

    public function manager()
    {
        return $this->belongsTo(\App\Models\User::class, 'manager_user_id');
    }

    public function users()
    {
        return $this->hasMany(\App\Models\User::class, 'branch_id');
    }

    public function orders()
    {
        return $this->hasMany(\App\Models\Order::class, 'branch_id');
    }

    public function stockReceipts()
    {
        return $this->hasMany(\App\Models\StockReceipt::class, 'branch_id');
    }

    public function inventories()
    {
        return $this->hasMany(\App\Models\Inventory::class, 'branch_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Cabang yang boleh dipilih/dilihat $user (pusat: semua, cabang: miliknya).
     */
    public function scopeAccessibleBy($query, ?\App\Models\User $user)
    {
        if (! $user) {
            return $query->whereRaw('1 = 0');
        }

        if ($user->hasAllBranchScope()) {
            return $query;
        }

        return $query->where('id', (int) $user->branch_id);
    }
}
