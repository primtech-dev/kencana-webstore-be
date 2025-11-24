<?php

namespace App\Http\Controllers\ContentManagement;

use App\Http\Controllers\Controller;
use App\Interfaces\ContentManagement\TagInterface;
use Illuminate\Http\Request;

class TagController extends Controller
{
    private const VALIDATION_MESSAGES = [
        'name.required' => 'Nama tag tidak boleh kosong'
    ];

    public function __construct(private TagInterface $tag) {}

    public function index(Request $request)
    {
        if ($request->ajax()) {
            $tags = $this->tag->get();

            return datatables()->of($tags)
                ->addIndexColumn()
                ->addColumn('created_at', function ($tag) {
                    return $tag->created_at->format('d M Y H:i');
                })
                ->addColumn('action', function ($tag) {
                    return view('content-management.tags.column.action', compact('tag'))->render();
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        return view('content-management.tags.index');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|max:255'
        ], self::VALIDATION_MESSAGES);

        try {
            $this->tag->store($validated);
            return redirect()->back()->with('success', 'Tag berhasil ditambahkan');
        } catch (\Throwable $th) {
            return redirect()->back()->with('error', $th->getMessage());
        }
    }

    public function update(Request $request, int $id)
    {
        $validated = $request->validate([
            'name' => 'required|max:255'
        ], self::VALIDATION_MESSAGES);

        try {
            $this->tag->update($id, $validated);
            return redirect()->back()->with('success', 'Tag berhasil diubah');
        } catch (\Throwable $th) {
            return redirect()->back()->with('error', $th->getMessage());
        }
    }

    public function destroy(int $id)
    {
        try {
            $this->tag->destroy($id);
            return redirect()->back()->with('success', 'Tag berhasil dihapus');
        } catch (\Throwable $th) {
            return redirect()->back()->with('error', $th->getMessage());
        }
    }
}
