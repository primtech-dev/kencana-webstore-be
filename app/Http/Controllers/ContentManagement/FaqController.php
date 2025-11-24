<?php

namespace App\Http\Controllers\ContentManagement;

use App\Http\Controllers\Controller;
use App\Interfaces\ContentManagement\FaqInterface;
use Illuminate\Http\Request;

class FaqController extends Controller
{
    private const VALIDATION_MESSAGES = [
        'question.required' => 'Pertanyaan tidak boleh kosong',
        'answer.required' => 'Jawaban tidak boleh kosong'
    ];

    public function __construct(private FaqInterface $faq) {}
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $faqs = $this->faq->get();

            return datatables()->of($faqs)
                ->addIndexColumn()
                ->addColumn('created_at', function ($faq) {
                    return $faq->created_at->format('d M Y H:i');
                })
                ->addColumn('action', function ($faq) {
                    return view('content-management.faqs.column.action', compact('faq'))->render();
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        return view('content-management.faqs.index');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'question' => 'required|max:255',
            'answer' => 'required'
        ], self::VALIDATION_MESSAGES);

        try {
            $this->faq->store($validated);
            return redirect()->back()->with('success', 'FAQ berhasil ditambahkan');
        } catch (\Throwable $th) {
            return redirect()->back()->with('error', $th->getMessage());
        }
    }

    public function update(Request $request, int $id)
    {
        $validated = $request->validate([
            'question' => 'required|max:255',
            'answer' => 'required'
        ], self::VALIDATION_MESSAGES);

        try {
            $this->faq->update($id, $validated);
            return redirect()->back()->with('success', 'FAQ berhasil diubah');
        } catch (\Throwable $th) {
            return redirect()->back()->with('error', $th->getMessage());
        }
    }

    public function destroy(int $id)
    {
        try {
            $this->faq->destroy($id);
            return redirect()->back()->with('success', 'FAQ berhasil dihapus');
        } catch (\Throwable $th) {
            return redirect()->back()->with('error', $th->getMessage());
        }
    }
}
