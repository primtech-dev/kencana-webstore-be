<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class MetaKeyword extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name', 'slug', 'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected static function booted()
    {
        static::saving(function ($metaKeyword) {
            if (empty($metaKeyword->slug) && !empty($metaKeyword->name)) {
                $base = Str::slug($metaKeyword->name);
                $slug = $base;
                $i = 1;
                while (self::where('slug', $slug)->where('id', '<>', $metaKeyword->id ?? 0)->withTrashed()->exists()) {
                    $slug = $base . '-' . $i++;
                }
                $metaKeyword->slug = $slug;
            }
        });
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'product_meta_keywords')->withTimestamps()->using(ProductMetaKeyword::class);
    }
}
