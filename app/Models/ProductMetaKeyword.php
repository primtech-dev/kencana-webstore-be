<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class ProductMetaKeyword extends Pivot
{
    protected $table = 'product_meta_keywords';
}
