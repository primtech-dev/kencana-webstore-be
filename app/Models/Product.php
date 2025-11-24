<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use SoftDeletes;

    protected $table = 'products';

    protected $fillable = [
        'name',
        'slug',
        'image_path',
        'alt_text',
        'content',
        'terms_and_condition',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
