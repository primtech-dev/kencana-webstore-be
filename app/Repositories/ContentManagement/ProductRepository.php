<?php

namespace App\Repositories\ContentManagement;

use App\Interfaces\ContentManagement\ProductInterface;
use App\Models\Product;
use App\Traits\HandlesImages;
use Illuminate\Support\Facades\DB;

class ProductRepository implements ProductInterface
{
    use HandlesImages;

    public function __construct(private Product $product) {}

    public function get()
    {
        return $this->product->get();
    }

    public function getActive()
    {
        return $this->product->where('is_active', true)->get();
    }

    public function getBySlug($slug)
    {
        return $this->product->where('slug', $slug)->first();
    }

    public function getExceptSlug($slug)
    {
        return $this->product->where('slug', '!=', $slug)->get();
    }

    public function getById($id)
    {
        return $this->product->findOrFail($id);
    }

    public function store($data, $image)
    {
        return DB::transaction(function () use ($data, $image) {

            $imagePath = null;
            if ($image) {
                $imagePath = $this->saveImageAsWebp($image, 'products');
            }

            return $this->product->create([
                'name' => $data['name'],
                'slug' => $data['slug'],
                'content' => $data['content'],
                'terms_and_condition' => $data['terms_and_condition'],
                'image_path' => $imagePath,
                'alt_text' => $data['alt_text'] ?? null,
                'is_active' => $data['is_active'] ?? true,
            ]);
        });
    }

    public function update($id, $data, $image = null)
    {
        return DB::transaction(function () use ($id, $data, $image) {
            $product = $this->product->findOrFail($id);

            // Handle image change
            if ($image) {
                $this->deleteImage($product->image_path);
                $data['image_path'] = $this->saveImageAsWebp($image, 'products');
            }

            $product->update([
                'name' => $data['name'],
                'slug' => $data['slug'],
                'content' => $data['content'],
                'terms_and_condition' => $data['terms_and_condition'],
                'image_path' => $data['image_path'] ?? $product->image_path,
                'alt_text' => $data['alt_text'] ?? $product->alt_text,
                'is_active' => $data['is_active'] ?? $product->is_active,
            ]);

            return $product;
        });
    }

    public function destroy($id)
    {
        return DB::transaction(function () use ($id) {
            $product = $this->product->findOrFail($id);

            $this->deleteImage($product->image_path);

            return $product->delete();
        });
    }
}
