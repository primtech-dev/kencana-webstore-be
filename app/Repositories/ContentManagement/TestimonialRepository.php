<?php

namespace App\Repositories\ContentManagement;

use App\Interfaces\ContentManagement\TestimonialInterface;
use App\Models\Testimonial;
use App\Traits\HandlesImages;
use Illuminate\Support\Facades\DB;

class TestimonialRepository implements TestimonialInterface
{
    use HandlesImages;

    public function __construct(private Testimonial $testimonial) {}

    public function get()
    {
        return $this->testimonial->get();
    }

    public function getById($id)
    {
        return $this->testimonial->find($id);
    }

    public function store($data, $image)
    {
        return DB::transaction(function () use ($data, $image) {
            $imagePath = $this->saveImageAsWebp($image, 'testimonials');

            $this->testimonial->create([
                'name' => $data['name'],
                'image_path' => $imagePath,
                'job' => $data['job'],
                'rating' => $data['rating'],
                'comment' => $data['comment']
            ]);
        });
    }

    public function update($id, $data, $image = null)
    {
        return DB::transaction(function () use ($id, $data, $image) {
            $testimonial = $this->testimonial->findOrFail($id);

            if ($image) {
                $this->deleteImage($testimonial->image_path);

                $data['image_path'] = $this->saveImageAsWebp($image, 'testimonials');
            }

            $testimonial->update([
                'name' => $data['name'],
                'image_path' => $data['image_path'] ?? $testimonial->image_path,
                'job' => $data['job'],
                'rating' => $data['rating'],
                'comment' => $data['comment']
            ]);
        });
    }

    public function destroy($id)
    {
        return DB::transaction(function () use ($id) {
            $testimonial = $this->testimonial->findOrFail($id);
            $this->deleteImage($testimonial->image_path);
            $testimonial->delete();
        });
    }
}
