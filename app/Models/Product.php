<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use SoftDeletes;

    public const PURCHASE_TYPE_ONLINE = 'online';
    public const PURCHASE_TYPE_MANUAL = 'manual';

    public const PURCHASE_TYPES = [
        self::PURCHASE_TYPE_ONLINE,
        self::PURCHASE_TYPE_MANUAL,
    ];

    protected $fillable = [
        'sku','name','short_description','description','attributes','weight_gram','is_active', 'unit_id', 'purchase_type'
    ];

    protected $casts = [
        'attributes' => 'array',
        'is_active' => 'boolean',
    ];

    public function variants()
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class);
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'product_categories')->withTimestamps()->using(\App\Models\ProductCategory::class);
    }

    public function metaKeywords()
    {
        return $this->belongsToMany(MetaKeyword::class, 'product_meta_keywords')->withTimestamps()->using(\App\Models\ProductMetaKeyword::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class, 'unit_id');
    }
}
