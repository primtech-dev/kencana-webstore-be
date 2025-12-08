<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\ProductVariant;
use App\Models\ProductImage;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Gate;

class ProductController extends Controller
{
    private const VALIDATION_MESSAGES = [
        'name.required' => 'Nama produk tidak boleh kosong',
        'variants.*.variant_name.required' => 'Nama varian dibutuhkan',
        // add more as needed
    ];

    public function index(Request $request)
    {
        if ($request->ajax()) {
            // eager load unit (ambil kolom penting saja)
            $query = Product::select(['id','sku','name','is_active','created_at','unit_id'])
                ->withCount('variants')
                ->with(['unit' => function($q){
                    $q->select('id','code','name');
                }]);

            $searchValue = $request->input('search.value');
            if (!empty($searchValue)) {
                $query->where(function ($q) use ($searchValue) {
                    $q->where('name', 'ilike', "%{$searchValue}%")
                        ->orWhere('sku', 'ilike', "%{$searchValue}%");
                });
            }

            return datatables()->eloquent($query)
                ->addIndexColumn()
                ->addColumn('name', fn(Product $p) => e($p->name))
                // deliver unit as formatted string so client simple
                ->addColumn('unit', function (Product $p) {
                    if ($p->unit) {
                        $code = $p->unit->code ? e($p->unit->code).' • ' : '';
                        return $code . e($p->unit->name);
                    }
                    return '-';
                })
                ->addColumn('sku', fn(Product $p) => $p->sku ? e($p->sku) : '-')
                ->addColumn('variants_count', fn($p) => $p->variants_count)
                ->addColumn('is_active', fn(Product $p) => $p->is_active ? 'Aktif' : 'Non-aktif')
                ->addColumn('created_at', fn($p) => $p->created_at ? $p->created_at->format('d M Y H:i') : '-')
                ->addColumn('action', function (Product $p) {
                    return view('products._column_action', ['p'=>$p])->render();
                })
                ->rawColumns(['action'])
                ->toJson();
        }

        return view('products.index');
    }

    public function create()
    {
        $categories = Category::whereNull('parent_id')->orderBy('position')->get();
        $units = Unit::orderBy('name')->get();
        return view('products.create', ['product' => new Product(), 'categories' => $categories, 'units' => $units]);
    }

    public function store(Request $request)
    {
        // validate (this also normalizes 'attributes' inside)
        $this->validateRequestProduct($request);

        // prepare validated (includes normalized attributes)
        $validated = $this->prepareValidated($request);

        return $validated;

        DB::beginTransaction();
        try {
            // 1) create product
            $product = Product::create([
                'sku' => $validated['sku'] ?? null,
                'name' => $validated['name'],
                'short_description' => $validated['short_description'] ?? null,
                'description' => $validated['description'] ?? null,
                'attributes' => $validated['attributes'] ?? null,
                'weight_gram' => $validated['weight_gram'] ?? null,
                'is_active' => $request->has('is_active') ? (bool) ($validated['is_active'] ?? true) : true,
                'unit_id' => $validated['unit_id'] ?? null
            ]);

            // 2) categories pivot
            if (!empty($validated['categories']) && is_array($validated['categories'])) {
                $product->categories()->sync($validated['categories']);
            }

            // 3) create variants and record mapping (form index => variant id)
            $variantIndexToId = [];
            if (!empty($validated['variants']) && is_array($validated['variants'])) {
                foreach ($validated['variants'] as $formIndex => $v) {
                    // skip if fully empty
                    $hasMeaning = (!empty($v['variant_name']) || !empty($v['sku']) || !empty($v['price']));
                    if (!$hasMeaning) continue;

                    $variant = ProductVariant::create([
                        'product_id' => $product->id,
                        'sku' => $v['sku'] ?? null,
                        'variant_name' => $v['variant_name'] ?? '',
                        'price' => isset($v['price']) ? (int)$v['price'] : null,
                        'length' => $v['length'] ?? null,
                        'width' => $v['width'] ?? null,
                        'height' => $v['height'] ?? null,
                        'is_active' => isset($v['is_active']) ? (bool)$v['is_active'] : true,
                        'is_sellable' => isset($v['is_sellable']) ? (bool)$v['is_sellable'] : true,
                        'unit_id' => $validated['unit_id'] ?? null
                    ]);

                    // store mapping by original form index key (may be "0","1" or "new_0" etc.)
                    $variantIndexToId[(string)$formIndex] = $variant->id;
                }
            }

            // 4) save global product images (product_images[])
            if ($request->hasFile('product_images')) {

                $first = true;

                foreach ($request->file('product_images') as $file) {
                    if (!$file || !$file->isValid()) continue;

                    $path = $file->store("products/{$product->id}", 'public');

                    // jika image ini akan menjadi main, unset main image sebelumnya untuk product-level (variant_id = NULL)
                    if ($first) {
                        \App\Models\ProductImage::where('product_id', $product->id)
                            ->whereNull('variant_id')
                            ->update(['is_main' => false]);
                    }

                    \App\Models\ProductImage::create([
                        'product_id' => $product->id,
                        'variant_id' => null,
                        'url' => $path,
                        'position' => 0,
                        'is_main' => $first ? true : false,
                    ]);

                    $first = false; // setelah gambar pertama
                }
            }

            $variantsFiles = $request->file('variants') ?? [];
            $validatedVariants = $validated['variants'] ?? $request->input('variants', []);

            foreach ($variantsFiles as $vIndex => $filesForVariant) {
                $formIndex = (string)$vIndex;
                $variantId = null;

                // Determine variant id (same strategy as before)
                if (isset($variantIndexToId[$formIndex]) && $variantIndexToId[$formIndex]) {
                    $variantId = $variantIndexToId[$formIndex];
                }

                if (!$variantId && isset($validatedVariants[$formIndex]) && is_array($validatedVariants[$formIndex]) && !empty($validatedVariants[$formIndex]['id'])) {
                    $variantId = (int)$validatedVariants[$formIndex]['id'];
                }

                if (!$variantId) {
                    $maybeNum = preg_replace('/^[^\d]*/', '', $formIndex);
                    if ($maybeNum !== '') {
                        $maybeNum = (string)$maybeNum;
                        if (isset($variantIndexToId[$maybeNum])) $variantId = $variantIndexToId[$maybeNum];
                        if (!$variantId && isset($validatedVariants[$maybeNum]) && !empty($validatedVariants[$maybeNum]['id'])) {
                            $variantId = (int)$validatedVariants[$maybeNum]['id'];
                        }
                    }
                }

                if (!$variantId) {
                    $vInput = $request->input("variants.{$formIndex}", $request->input("variants.{$maybeNum}", []));
                    if (is_array($vInput) && !empty($vInput['sku'])) {
                        $found = $product->variants()->where('sku', $vInput['sku'])->first();
                        if ($found) $variantId = $found->id;
                    }
                }

                if (!$variantId) {
                    $firstVariant = $product->variants()->first();
                    if ($firstVariant) $variantId = $firstVariant->id;
                }

                if (!$variantId) {
                    \Log::warning("store: cannot determine variant id for files index {$formIndex} (product {$product->id})");
                    continue;
                }

                // --- NORMALIZE filesForVariant: support multiple shapes ---
                // Cases we expect:
                // 1) filesForVariant is an UploadedFile => wrap to array
                // 2) filesForVariant is an array of UploadedFile => use as-is
                // 3) filesForVariant is an associative array like ['images' => [UploadedFile,...]] => use that inner array
                if ($filesForVariant instanceof \Illuminate\Http\UploadedFile) {
                    $files = [$filesForVariant];
                } elseif (is_array($filesForVariant) && isset($filesForVariant['images']) && is_array($filesForVariant['images'])) {
                    $files = $filesForVariant['images'];
                } elseif (is_array($filesForVariant)) {
                    // may be numeric-indexed array of UploadedFile OR associative array where first value is list
                    // try to detect uploaded files inside
                    $firstVal = reset($filesForVariant);
                    if ($firstVal instanceof \Illuminate\Http\UploadedFile) {
                        $files = array_values($filesForVariant);
                    } elseif (is_array($firstVal) && isset($firstVal[0]) && $firstVal[0] instanceof \Illuminate\Http\UploadedFile) {
                        // nested one-level deeper
                        $files = $firstVal;
                    } else {
                        // unknown shape -> skip
                        $files = [];
                    }
                } else {
                    $files = [];
                }

                if (empty($files)) {
                    \Log::debug("store: no files resolved for variant index {$formIndex}");
                    continue;
                }

                // Save files
                $firstImage = true;

                foreach ($files as $file) {
                    if (!$file || !$file->isValid()) {
                        \Log::warning("store: invalid variant file for variant id {$variantId}");
                        continue;
                    }

                    $path = $file->store("products/{$product->id}/variants/{$variantId}", 'public');

                    // jika ini akan jadi main -> unset main sebelumnya untuk pasangan product+variant
                    if ($firstImage) {
                        \App\Models\ProductImage::where('product_id', $product->id)
                            ->where('variant_id', $variantId)
                            ->update(['is_main' => false]);
                    }

                    \App\Models\ProductImage::create([
                        'product_id' => $product->id,
                        'variant_id' => $variantId,
                        'url' => $path,
                        'position' => 0,
                        'is_main' => $firstImage ? true : false,
                    ]);

                    $firstImage = false;
                }
            }

            DB::commit();
            return redirect()->route('products.index')->with('success', 'Produk berhasil dibuat');
        } catch (\Throwable $th) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', $th->getMessage());
        }
    }


    public function edit(int $id)
    {
        $product = Product::with(['variants.images','images','categories'])->findOrFail($id);
        $categories = Category::orderBy('name')->get();
        $units = Unit::orderBy('name')->get();
        return view('products.create', compact('product','categories','units'));
    }

    public function update(Request $request, int $id)
    {
        $product = Product::with(['variants', 'images'])->findOrFail($id);

        // validate (normalize attributes inside)
        $this->validateRequestProduct($request, $product);

        $validated = $this->prepareValidated($request);

//        return $validated['unit_id'];

        DB::beginTransaction();
        try {
            // 1) update product
            $product->update([
                'sku' => $validated['sku'] ?? null,
                'name' => $validated['name'],
                'short_description' => $validated['short_description'] ?? null,
                'description' => $validated['description'] ?? null,
                'attributes' => $validated['attributes'] ?? null,
                'weight_gram' => $validated['weight_gram'] ?? null,
                'is_active' => $request->has('is_active') ? (bool) ($validated['is_active'] ?? false) : false,
                'unit_id' => $validated['unit_id'] ?? null
            ]);

            // 2) sync categories
            $product->categories()->sync($validated['categories'] ?? []);

            // 3) update existing variants and create new ones; collect incoming ids
            $incomingVariants = $validated['variants'] ?? [];
            $incomingIds = [];
            $variantIndexToId = [];

            foreach ($incomingVariants as $formIndex => $v) {
                // update existing
                if (!empty($v['id'])) {
                    $variant = ProductVariant::find($v['id']);
                    if ($variant && $variant->product_id == $product->id) {
                        $variant->update([
                            'sku' => $v['sku'] ?? $variant->sku,
                            'variant_name' => $v['variant_name'] ?? $variant->variant_name,
                            'price' => isset($v['price']) ? (int)$v['price'] : $variant->price,
                            'length' => $v['length'] ?? $variant->length,
                            'width' => $v['width'] ?? $variant->width,
                            'height' => $v['height'] ?? $variant->height,
                            'is_active' => isset($v['is_active']) ? (bool)$v['is_active'] : $variant->is_active,
                            'is_sellable' => isset($v['is_sellable']) ? (bool)$v['is_sellable'] : $variant->is_sellable,
                            'unit_id' => $validated['unit_id'] ?? null
                        ]);
                        $incomingIds[] = $variant->id;
                        $variantIndexToId[(string)$formIndex] = $variant->id;
                    }
                } else {
                    // create new variant if meaningful
                    $hasMeaning = (!empty($v['variant_name']) || !empty($v['sku']) || !empty($v['price']));
                    if (!$hasMeaning) continue;
                    $variant = ProductVariant::create([
                        'product_id' => $product->id,
                        'sku' => $v['sku'] ?? null,
                        'variant_name' => $v['variant_name'] ?? '',
                        'price' => isset($v['price']) ? (int)$v['price'] : null,
                        'length' => $v['length'] ?? null,
                        'width' => $v['width'] ?? null,
                        'height' => $v['height'] ?? null,
                        'is_active' => isset($v['is_active']) ? (bool)$v['is_active'] : true,
                        'is_sellable' => isset($v['is_sellable']) ? (bool)$v['is_sellable'] : true,
                        'unit_id' => $validated['unit_id'] ?? null
                    ]);
                    $incomingIds[] = $variant->id;
                    $variantIndexToId[(string)$formIndex] = $variant->id;
                }
            }

            // 4) delete variants removed in the form (optional or soft-delete)
            $toDelete = $product->variants()->whereNotIn('id', $incomingIds)->get();
        foreach ($toDelete as $d) {
                $d->delete();
            }

            // 5) save global product images (product_images[])
            if ($request->hasFile('product_images')) {
                $first = true;
                foreach ($request->file('product_images') as $file) {
                    if (!$file || !$file->isValid()) continue;
                    $path = $file->store("products/{$product->id}", 'public');

                    // jika gambar pertama dari request ingin jadi main -> unset main sebelumnya
                    if ($first) {
                        \App\Models\ProductImage::where('product_id', $product->id)
                            ->whereNull('variant_id')
                            ->update(['is_main' => false]);
                    }

                    \App\Models\ProductImage::create([
                        'product_id' => $product->id,
                        'variant_id' => null,
                        'url' => $path,
                        'position' => 0,
                        'is_main' => $first ? true : false,
                    ]);

                    $first = false;
                }
            }

            // ===== Robust variant images handler (update) =====
            $variantsFiles = $request->file('variants') ?? [];
            $validatedVariants = $incomingVariants; // incomingVariants sudah di-prepare

            foreach ($variantsFiles as $vIndex => $filesForVariant) {
                $formIndex = (string)$vIndex;
                $variantId = $variantIndexToId[$formIndex] ?? null;

                if (!$variantId && isset($validatedVariants[$formIndex]) && is_array($validatedVariants[$formIndex]) && !empty($validatedVariants[$formIndex]['id'])) {
                    $variantId = (int)$validatedVariants[$formIndex]['id'];
                }

                if (!$variantId) {
                    $maybeNum = preg_replace('/^[^\d]*/', '', $formIndex);
                    if ($maybeNum !== '') {
                        $maybeNum = (string)$maybeNum;
                        if (isset($variantIndexToId[$maybeNum])) $variantId = $variantIndexToId[$maybeNum];
                        if (!$variantId && isset($validatedVariants[$maybeNum]) && !empty($validatedVariants[$maybeNum]['id'])) {
                            $variantId = (int)$validatedVariants[$maybeNum]['id'];
                        }
                    }
                }

                if (!$variantId) {
                    $vInput = $request->input("variants.{$formIndex}", $request->input("variants.{$maybeNum}", []));
                    if (is_array($vInput) && !empty($vInput['sku'])) {
                        $found = $product->variants()->where('sku', $vInput['sku'])->first();
                        if ($found) $variantId = $found->id;
                    }
                }

                if (!$variantId) {
                    $firstVariant = $product->variants()->first();
                    if ($firstVariant) $variantId = $firstVariant->id;
                }

                if (!$variantId) {
                    \Log::warning("update: cannot determine variant id for files index {$formIndex} (product {$product->id})");
                    continue;
                }

                // Normalize filesForVariant shapes same as store()
                if ($filesForVariant instanceof \Illuminate\Http\UploadedFile) {
                    $files = [$filesForVariant];
                } elseif (is_array($filesForVariant) && isset($filesForVariant['images']) && is_array($filesForVariant['images'])) {
                    $files = $filesForVariant['images'];
                } elseif (is_array($filesForVariant)) {
                    $firstVal = reset($filesForVariant);
                    if ($firstVal instanceof \Illuminate\Http\UploadedFile) {
                        $files = array_values($filesForVariant);
                    } elseif (is_array($firstVal) && isset($firstVal[0]) && $firstVal[0] instanceof \Illuminate\Http\UploadedFile) {
                        $files = $firstVal;
                    } else {
                        $files = [];
                    }
                } else {
                    $files = [];
                }

                if (empty($files)) {
                    \Log::debug("update: no files resolved for variant index {$formIndex}");
                    continue;
                }

                // Save files
                foreach ($files as $file) {
                    if (!$file || !$file->isValid()) {
                        \Log::warning("update: invalid variant file for variant id {$variantId}");
                        continue;
                    }
                    $path = $file->store("products/{$product->id}/variants/{$variantId}", 'public');

                    // jika file pertama untuk varian ini dari batch upload -> unset main sebelumnya
                    if ($firstImage ?? true) {
                        \App\Models\ProductImage::where('product_id', $product->id)
                            ->where('variant_id', $variantId)
                            ->update(['is_main' => false]);
                    }

                    \App\Models\ProductImage::create([
                        'product_id' => $product->id,
                        'variant_id' => $variantId,
                        'url' => $path,
                        'position' => 0,
                        'is_main' => ($firstImage ?? true) ? true : false,
                    ]);

                    // flip marker for next iterations
                    $firstImage = false;
                }
            }

            DB::commit();
            return redirect()->route('products.index')->with('success', 'Produk berhasil diperbarui');
        } catch (\Throwable $th) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', $th->getMessage());
        }
    }

    public function destroy(int $id)
    {
        $product = Product::findOrFail($id);
        try {
            $product->delete();
            return redirect()->route('products.index')->with('success','Produk berhasil dihapus');
        } catch (\Throwable $th) {
            return redirect()->back()->with('error', $th->getMessage());
        }
    }

    protected function validateRequestProduct(Request $request, Product $product = null)
    {
        // Normalize attributes: if JSON string -> decode to array
        $attrInput = $request->input('attributes');
        if (is_string($attrInput) && $attrInput !== '') {
            $decoded = json_decode($attrInput, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $request->merge(['attributes' => $decoded]);
            } else {
                $request->merge(['attributes' => null]);
            }
        } elseif (!is_array($attrInput) && $attrInput !== null) {
            $request->merge(['attributes' => null]);
        }

        // SKU uniqueness rule
        $uniqueSkuRule = ['nullable', 'string', 'max:255'];
        if ($product && $product->id) {
            $uniqueSkuRule[] = Rule::unique('products', 'sku')->ignore($product->id);
        } else {
            $uniqueSkuRule[] = Rule::unique('products', 'sku');
        }

        $rules = [
            'sku' => $uniqueSkuRule,
            'name' => 'required|string|max:255',
            'short_description' => 'nullable|string',
            'description' => 'nullable|string',
            'attributes' => 'nullable|array',
            'weight_gram' => 'nullable|integer',
            'is_active' => 'sometimes|boolean',
            'categories' => 'nullable|array',
            'categories.*' => 'integer|exists:categories,id',
            'unit_id' => 'nullable',
            'variants' => 'nullable|array',
            'variants.*.id' => 'nullable|integer|exists:product_variants,id',
            'variants.*.variant_name' => 'sometimes|required|string|max:255',
            'variants.*.sku' => 'nullable|string|max:255',
            'variants.*.price' => 'nullable|integer|min:0',
            'variants.*.length' => 'nullable|numeric',
            'variants.*.width' => 'nullable|numeric',
            'variants.*.height' => 'nullable|numeric',
            'variants.*.is_active' => 'sometimes|boolean',
            'variants.*.is_sellable' => 'sometimes|boolean',
        ];

        $request->validate($rules, self::VALIDATION_MESSAGES);

        // Optional: validate product_images and variant images per-file if present
        if ($request->hasFile('product_images')) {
            // example: $this->validate($request, ['product_images.*' => 'image|mimes:jpeg,png,jpg,gif|max:5120']);
        }
        if ($request->hasFile('variants')) {
            // iterate and optionally validate each file
            foreach ($request->file('variants') as $vFiles) {
                if (is_array($vFiles)) {
                    foreach ($vFiles as $f) {
                        // example check:
                        // if (!$f->isValid()) abort(422, 'One of variant images invalid.');
                    }
                }
            }
        }
    }

    protected function prepareValidated(Request $request): array
    {
        $validated = $request->only([
            'sku','name','short_description','description','weight_gram','is_active','categories','variants', 'unit_id'
        ]);

        // normalize attributes
        $attributesInput = $request->input('attributes');
        if (is_string($attributesInput)) {
            $decoded = json_decode($attributesInput, true);
            $validated['attributes'] = is_array($decoded) ? $decoded : null;
        } elseif (is_array($attributesInput)) {
            $validated['attributes'] = $attributesInput;
        } else {
            $validated['attributes'] = null;
        }

        return $validated;
    }

    // Ajax upload image (for edit/create via async)
    public function uploadImage(Request $request, $id)
    {
        $product = Product::findOrFail($id);
        $request->validate([
            'image' => 'required|image|max:4096',
            'variant_id' => 'nullable|integer|exists:product_variants,id',
            'is_main' => 'sometimes|boolean',
            'position' => 'nullable|integer',
        ]);

        $file = $request->file('image');
        $path = $file->store('products/'.$product->id, 'public');

        $img = ProductImage::create([
            'product_id' => $product->id,
            'variant_id' => $request->input('variant_id'),
            'url' => $path,
            'position' => $request->input('position') ?? 0,
            'is_main' => $request->boolean('is_main'),
        ]);

        return response()->json(['success'=>true, 'image' => $img]);
    }

    public function deleteImage($id)
    {
        $img = ProductImage::findOrFail($id);
        // delete file from disk
        if ($img->url && Storage::disk('public')->exists($img->url)) {
            Storage::disk('public')->delete($img->url);
        }
        $img->delete();
        return response()->json(['success'=>true]);
    }

    public function show(int $id)
    {
        $product = Product::with(['variants.images','images','categories'])->findOrFail($id);
        return view('products.show', compact('product'));
    }

    public function reorderImages(Request $request, int $id)
    {
        $product = Product::with('images')->findOrFail($id);

        // permission check (optional)
        if ($request->user() && !$request->user()->can('products.update')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $data = $request->validate([
            'order' => 'required|array',
            'order.*' => ['required','integer','exists:product_images,id'],
        ]);

        $order = $data['order'];

        DB::beginTransaction();
        try {
            // Optional: ensure submitted images all belong to product
            $imagesCount = $product->images()->whereIn('id', $order)->count();
            if ($imagesCount !== count($order)) {
                return response()->json(['success' => false, 'message' => 'Some images do not belong to this product'], 422);
            }

            // Set positions according to array order (0-based or 1-based as you prefer)
            foreach ($order as $pos => $imageId) {
                \App\Models\ProductImage::where('id', $imageId)->update(['position' => (int)$pos]);
            }

            DB::commit();
            return response()->json(['success' => true]);
        } catch (\Throwable $e) {
            DB::rollBack();
            \Log::error('reorderImages error: '.$e->getMessage());
            return response()->json(['success' => false, 'message' => 'Server error'], 500);
        }
    }

    /**
     * Set a given image as "main" for its product.
     * URL: POST /products/images/{id}/set-main
     * Body (optional): product_id
     */
    public function setMainImage(Request $request, int $id)
    {
        $img = \App\Models\ProductImage::findOrFail($id);

        // permission check
        if ($request->user() && !$request->user()->can('products.update')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        DB::beginTransaction();
        try {
            // Unset current main images for same product
            \App\Models\ProductImage::where('product_id', $img->product_id)->where('is_main', true)->update(['is_main' => false]);

            // Set this as main
            $img->is_main = true;
            $img->position = 0; // optional: put main at position 0
            $img->save();

            DB::commit();
            return response()->json(['success' => true]);
        } catch (\Throwable $e) {
            DB::rollBack();
            \Log::error('setMainImage error: '.$e->getMessage());
            return response()->json(['success' => false, 'message' => 'Server error'], 500);
        }
    }
}
