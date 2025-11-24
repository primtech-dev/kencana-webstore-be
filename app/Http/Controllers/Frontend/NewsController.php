<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Interfaces\ContentManagement\ArticleInterface;
use Illuminate\Http\Request;

class NewsController extends Controller
{
    public function __construct(private readonly ArticleInterface $article) {}

    public function index(Request $request)
    {
        $search = $request->get('search');

        $perPage = 6;

        $news = $this->article->getArticlePaginated($perPage);

         $news = $search
             ? $this->article->searchArticles($search, $perPage)
             : $this->article->getArticlePaginated($perPage);

        return view('landing.news.index', compact('news', 'search'));
    }

    public function show($slug)
    {
        $article = $this->article->getArticleBySlug($slug);

        if (!$article) {
            return view('errors.404-public');
        }

        $relatedNews = $this->article->getLatestArticles(4)
            ->where('id', '!=', $article->id)
            ->take(3);

        return view('landing.news.show', compact('article', 'relatedNews'));
    }

    public function showTag($tagSlug)
    {
        $articles = $this->article->getArticleByTag($tagSlug, 3);

        if (!$articles) {
            return view('errors.404-public');
        }

        $tagName = $tagSlug;

        return view('landing.news.show-tag', compact('articles', 'tagSlug', 'tagName'));
    }
}
