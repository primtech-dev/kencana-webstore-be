<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ArticlePost extends Model
{
    protected $table = 'article_posts';

    protected $fillable = [
        'title', 'content', 'author_id', 'image_path', 'image_alt_text', 'seo_url', 'meta_title', 'meta_description', 'meta_keywords'
    ];

    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s'
    ];

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id', 'id');
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'article_post_tag', 'article_post_id', 'tag_id');
    }
}
