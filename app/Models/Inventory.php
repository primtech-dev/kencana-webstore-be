<?php

namespace App\Models;

use App\Models\Concerns\BelongsToBranch;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Inventory extends Model
{
    use HasFactory, SoftDeletes, BelongsToBranch;

    protected $table = 'inventory';

    protected $fillable = [
        'variant_id','branch_id','on_hand','available','reserved'
    ];

    protected $casts = [
        'on_hand' => 'decimal:2',
        'available' => 'decimal:2',
        'reserved' => 'decimal:2',
    ];

    public function variant()
    {
        return $this->belongsTo(ProductVariant::class, 'variant_id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }
}
