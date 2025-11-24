<?php

namespace App\Interfaces\ContentManagement;

interface ArticleInterface
{
    public function get();
    public function getArticleLatest($limit = 3);
    public function getArticlePaginated($limit = 6);
    public function getArticleByTag(string $slugTag, $limit = 6);
    public function getArticleBySlug(string $slug);
    public function getLatestArticles(int $limit = 5);
    public function searchArticles(string $query, int $perPage = 6);
    public function getById($id);
    public function store($data, $image);
    public function update($id, $data);
    public function destroy($id);
}
