<?php

namespace App\Http\Controllers\ContentManagement;

use App\Http\Controllers\Controller;
use App\Interfaces\ContentManagement\ArticleInterface;
use App\Interfaces\ContentManagement\TagInterface;
use Illuminate\Http\Request;

class ArticleController extends Controller
{
    private const VALIDATION_MESSAGES = [
        'title.required' => 'Judul tidak boleh kosong',
        'content.required' => 'Konten tidak boleh kosong',
        'image.required' => 'Gambar tidak boleh kosong',
        'image.mimes' => 'Format gambar tidak sesuai',
        'image_alt_text.required' => 'Alt text gambar tidak boleh kosong',
        'seo_url.required' => 'URL SEO tidak boleh kosong',
        'seo_url.unique' => 'URL SEO sudah digunakan',
        'meta_title.required' => 'Meta title tidak boleh kosong',
        'meta_title.unique' => 'Meta title sudah digunakan',
        'meta_description.required' => 'Meta description tidak boleh kosong',
        'meta_keywords.required' => 'Meta keywords tidak boleh kosong',
        'tags.required' => 'Tag tidak boleh kosong'
    ];

    public function __construct(private ArticleInterface $article, private TagInterface $tag) {}

    public function index(Request $request)
    {
        if ($request->ajax()) {
            $articles = $this->article->get();

            return datatables()->of($articles)
                ->addIndexColumn()
                ->addColumn('created_at', function ($article) {
                    return $article->created_at->format('d M Y H:i');
                })
                ->addColumn('action', function ($article) {
                    return view('content-management.articles.column.action', compact('article'))->render();
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        return view('content-management.articles.index');
    }

    public function create()
    {
        $tags = $this->tag->get();
        return view('content-management.articles.create', compact('tags'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|max:255',
            'content' => 'required',
            'image' => 'required|mimes:jpg,jpeg,png,webp|max:2048',
            'image_alt_text' => 'required|max:255',
            'seo_url' => 'required|unique:article_posts,seo_url|max:255',
            'meta_title' => 'required|unique:article_posts,meta_title|max:255',
            'meta_description' => 'required',
            'meta_keywords' => 'required',
            'tags' => 'required|array'
        ], self::VALIDATION_MESSAGES);

        try {
            $this->article->store($validated, $request->file('image'));
            return redirect()->route('articles.index')->with('success', 'Artikel berhasil ditambahkan');
        } catch (\Throwable $th) {
            return redirect()->back()->with('error', $th->getMessage());
        }
    }

    public function edit(int $id)
    {
        $article = $this->article->getById($id);

        if (!$article) {
            return view('errors.404');
        }

        $tags = $this->tag->get();
        return view('content-management.articles.edit', compact('article', 'tags'));
    }

    public function update(Request $request, int $id)
    {
        $validated = $request->validate([
            'title' => 'required|max:255',
            'content' => 'required',
            'image' => 'nullable|mimes:jpg,jpeg,png,webp|max:2048',
            'image_alt_text' => 'required|max:255',
            'seo_url' => "required|max:255|unique:article_posts,seo_url,{$id}",
            'meta_title' => "required|max:255|unique:article_posts,meta_title,{$id}",
            'meta_description' => 'required',
            'meta_keywords' => 'required',
            'tags' => 'required|array'
        ], self::VALIDATION_MESSAGES);

        try {
            $this->article->update($id, $validated, $request->file('image'));

            return redirect()->route('articles.index')->with('success', 'Artikel berhasil diperbarui');
        } catch (\Throwable $th) {
            return redirect()->back()->with('error', $th->getMessage());
        }
    }

    public function destroy(int $id)
    {
        try {
            $this->article->destroy($id);
            return redirect()->route('articles.index')->with('success', 'Artikel berhasil dihapus');
        } catch (\Throwable $th) {
            return redirect()->back()->with('error', $th->getMessage());
        }
    }
}
