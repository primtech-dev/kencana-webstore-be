<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class StockMovement extends Model
{
    use HasFactory;

    public $timestamps = false;
    protected $table = 'stock_movements';

    protected $fillable = [
        'variant_id','branch_id','change','resulting_on_hand','reason','reference_type','reference_id','performed_by','metadata','created_at'
    ];

    protected $casts = [
        'metadata' => 'array',
        'created_at' => 'datetime',
    ];

    public function variant()
    {
        return $this->belongsTo(ProductVariant::class, 'variant_id');
    }
}
