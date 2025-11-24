<?php

namespace App\Repositories\ContentManagement;

use App\Helpers\ExcerptHelper;
use App\Interfaces\ContentManagement\ArticleInterface;
use App\Models\ArticlePost;
use App\Traits\HandlesImages;
use Illuminate\Support\Facades\DB;

class ArticleRepository implements ArticleInterface
{
    use HandlesImages;

    public function __construct(private ArticlePost $articlePost) {}

    public function get()
    {
        return $this->articlePost->with('author:id,name')->get();
    }

    public function getArticleLatest($limit = 3)
    {
        return $this->articlePost->with('author:id,name')->latest()->limit($limit)->get();
    }

    public function getArticlePaginated($limit = 6)
    {
        $articles = $this->articlePost
            ->with('author:id,name')
            ->latest()
            ->paginate($limit);

        $articles->getCollection()->transform(function ($article) {
            $article->excerpt = ExcerptHelper::makeExcerpt($article->content, 150);
            return $article;
        });

        return $articles;
    }

    public function getArticleByTag(string $slugTag, $limit = 6)
    {
        $articles = $this->articlePost
            ->with('author:id,name')
            ->whereHas('tags', function ($query) use ($slugTag) {
                $query->where('name', $slugTag);
            })
            ->latest()
            ->paginate($limit);

        $articles->getCollection()->transform(function ($article) {
            $article->excerpt = ExcerptHelper::makeExcerpt($article->content, 150);
            return $article;
        });

        return $articles;
    }

    public function getArticleBySlug(string $slug)
    {
        return $this->articlePost
            ->with(['author:id,name', 'tags:id,name'])
            ->where('seo_url', $slug)
            ->first();
    }

    public function getLatestArticles(int $limit = 5)
    {
        $articles = $this->articlePost
            ->with('author:id,name')
            ->latest()
            ->limit($limit)
            ->get();

        return $articles->transform(function ($article) {
            $article->excerpt = ExcerptHelper::makeExcerpt($article->content, 150);
            return $article;
        });
    }

    public function searchArticles(string $query, int $perPage = 6)
    {
        $articles = $this->articlePost
            ->with('author:id,name')
            ->where('title', 'like', "%{$query}%")
            ->orWhere('content', 'like', "%{$query}%")
            ->latest()
            ->paginate($perPage);

        $articles->getCollection()->transform(function ($article) {
            $article->excerpt = ExcerptHelper::makeExcerpt($article->content, 150);
            return $article;
        });

        return $articles;
    }

    public function getById($id)
    {
        return $this->articlePost->with(['author:id,name', 'tags:id,name'])->find($id);
    }

    public function store($data, $image)
    {
        return DB::transaction(function () use ($data, $image) {
            $imagePath = $this->saveImageAsWebp($image, 'articles');

            $articlePost = $this->articlePost->create([
                'title' => $data['title'],
                'content' => $data['content'],
                'author_id' => request()->user()->id,
                'image_path' => $imagePath,
                'image_alt_text' => $data['image_alt_text'],
                'seo_url' => $data['seo_url'],
                'meta_title' => $data['meta_title'],
                'meta_description' => $data['meta_description'],
                'meta_keywords' => $data['meta_keywords']
            ]);

            if (!empty($data['tags'])) {
                 $articlePost->tags()->attach($data['tags']);
            }
        });
    }

    public function update($id, $data, $image = null)
    {
        return DB::transaction(function () use ($id, $data, $image) {
            $articlePost = $this->articlePost->findOrFail($id);

            if ($image) {
                $this->deleteImage($articlePost->image_path);

                $data['image_path'] = $this->saveImageAsWebp($image, 'articles');
            }

            $articlePost->update([
                'title' => $data['title'],
                'content' => $data['content'],
                'image_path' => $data['image_path'] ?? $articlePost->image_path,
                'image_alt_text' => $data['image_alt_text'] ?? $articlePost->image_alt_text,
                'seo_url' => $data['seo_url'],
                'meta_title' => $data['meta_title'],
                'meta_description' => $data['meta_description'],
                'meta_keywords' => $data['meta_keywords']
            ]);

            if (!empty($data['tags'])) {
                $articlePost->tags()->sync($data['tags']);
            } else {
                $articlePost->tags()->detach();
            }
        });
    }

    public function destroy($id)
    {
        return DB::transaction(function () use ($id) {
            $articlePost = $this->articlePost->findOrFail($id);
            $this->deleteImage($articlePost->image_path);
            $articlePost->delete();
        });
    }
}
