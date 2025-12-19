<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ReviewImage extends Model
{
    use SoftDeletes;

    protected $fillable = ['review_id','image_path','position'];

    public function review()
    {
        return $this->belongsTo(Review::class);
    }

    public function getUrlAttribute(): string
    {
        return asset('storage/'.$this->image_path);
    }
}
