<?php

namespace App\Http\Controllers\ContentManagement;

use App\Http\Controllers\Controller;
use App\Interfaces\ContentManagement\TestimonialInterface;
use Illuminate\Http\Request;

class TestimonialController extends Controller
{
    private const VALIDATION_MESSAGES = [
        'name.required' => 'Nama tidak boleh kosong',
        'image.required' => 'Gambar tidak boleh kosong',
        'image.mimes' => 'Format gambar tidak sesuai',
        'job.required' => 'Pekerjaan tidak boleh kosong',
        'rating.required' => 'Rating tidak boleh kosong',
        'rating.numeric' => 'Rating harus berupa angka',
        'rating.between' => 'Rating harus antara 1 sampai 5',
        'comment.required' => 'Komentar tidak boleh kosong'
    ];

    public function __construct(private TestimonialInterface $testimonial) {}

    public function index(Request $request)
    {
        if ($request->ajax()) {
            $testimonials = $this->testimonial->get();

            return datatables()->of($testimonials)
                ->addIndexColumn()
                ->addColumn('created_at', function ($testimonial) {
                    return $testimonial->created_at->format('d M Y H:i');
                })
                ->addColumn('action', function ($testimonial) {
                    return view('content-management.testimonials.column.action', compact('testimonial'))->render();
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        return view('content-management.testimonials.index');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|max:255',
            'image' => 'required|mimes:jpg,jpeg,png,webp|max:2048',
            'job' => 'required|max:50',
            'rating' => 'required|numeric|between:1,5',
            'comment' => 'required'
        ], self::VALIDATION_MESSAGES);

        try {
            $this->testimonial->store($validated, $request->file('image'));
            return redirect()->back()->with('success', 'Testimoni berhasil ditambahkan');
        } catch (\Throwable $th) {
            return redirect()->back()->with('error', $th->getMessage());
        }
    }

    public function update(Request $request, int $id)
    {
        $validated = $request->validate([
            'name' => 'required|max:255',
            'image' => 'nullable|mimes:jpg,jpeg,png,webp|max:2048',
            'job' => 'required|max:50',
            'rating' => 'required|numeric|between:1,5',
            'comment' => 'required'
        ], self::VALIDATION_MESSAGES);

        try {
            $this->testimonial->update($id, $validated, $request->file('image'));
            return redirect()->back()->with('success', 'Testimoni berhasil diubah');
        } catch (\Throwable $th) {
            return redirect()->back()->with('error', $th->getMessage());
        }
    }

    public function destroy(int $id)
    {
        try {
            $this->testimonial->destroy($id);
            return redirect()->back()->with('success', 'Testimoni berhasil dihapus');
        } catch (\Throwable $th) {
            return redirect()->back()->with('error', $th->getMessage());
        }
    }
}
