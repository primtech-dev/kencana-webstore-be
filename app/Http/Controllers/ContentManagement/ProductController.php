<?php

namespace App\Http\Controllers\ContentManagement;

use App\Http\Controllers\Controller;
use App\Interfaces\ContentManagement\ProductInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    private const VALIDATION_MESSAGES = [
        'name.required' => 'Nama produk tidak boleh kosong',
        'slug.required' => 'Slug tidak boleh kosong',
        'content.required' => 'Konten tidak boleh kosong',
        'terms_and_condition.required' => 'Syarat dan ketentuan tidak boleh kosong',
        'image.required' => 'Gambar tidak boleh kosong',
        'image.mimes' => 'Format gambar tidak sesuai',
        'alt_text.required' => 'Alt text gambar tidak boleh kosong',
    ];

    public function __construct(private ProductInterface $product) {}

    public function index(Request $request)
    {
        if ($request->ajax()) {
            $products = $this->product->get();

            return datatables()->of($products)
                ->addIndexColumn()
                ->addColumn('created_at', function ($product) {
                    return $product->created_at->format('d M Y H:i');
                })
                ->addColumn('is_active', function ($product) {
                    return $product->is_active ? 'Active' : 'Inactive';
                })
                ->addColumn('action', function ($product) {
                    return view('content-management.products.column.action', compact('product'))->render();
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        return view('content-management.products.index');
    }

    public function create()
    {
        return view('content-management.products.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'                  => 'required|max:255',
            'content'               => 'required',
            'terms_and_condition'   => 'required',
            'image'                 => 'required|mimes:jpg,jpeg,png,webp|max:2048',
            'alt_text'              => 'nullable|max:255',
            'is_active'             => 'nullable|boolean',
        ], self::VALIDATION_MESSAGES);

        $validated['slug'] = Str::slug($validated['name']);

        try {
            $this->product->store($validated, $request->file('image'));

            return redirect()
                ->route('products.index')
                ->with('success', 'Produk berhasil ditambahkan');
        } catch (\Throwable $th) {
            return redirect()->back()->with('error', $th->getMessage());
        }
    }


    public function edit(int $id)
    {
        $product = $this->product->getById($id);

        if (!$product) {
            return view('errors.404');
        }

        return view('content-management.products.edit', compact('product'));
    }

    public function update(Request $request, int $id)
    {
        $validated = $request->validate([
            'name'                  => 'required|max:255',
            'content'               => 'required',
            'terms_and_condition'   => 'required',
            'image'                 => 'nullable|mimes:jpg,jpeg,png,webp|max:2048',
            'alt_text'              => 'nullable|max:255',
            'is_active'             => 'nullable|boolean',
        ], self::VALIDATION_MESSAGES);

        $validated['slug'] = Str::slug($validated['name']);

        try {
            $this->product->update($id, $validated, $request->file('image'));

            return redirect()
                ->route('products.index')
                ->with('success', 'Produk berhasil diperbarui');
        } catch (\Throwable $th) {
            return redirect()->back()->with('error', $th->getMessage());
        }
    }

    public function destroy(int $id)
    {
        try {
            $this->product->destroy($id);

            return redirect()
                ->route('products.index')
                ->with('success', 'Produk berhasil dihapus');
        } catch (\Throwable $th) {
            return redirect()->back()->with('error', $th->getMessage());
        }
    }
}
