<?php

namespace App\Http\Controllers;

use App\Exports\MetaKeywordsExport;
use App\Models\MetaKeyword;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;

class MetaKeywordController extends Controller
{
    private const VALIDATION_MESSAGES = [
        'name.required' => 'Nama meta keyword tidak boleh kosong',
        'slug.unique'   => 'Slug sudah digunakan, gunakan nama lain atau tambahkan suffix',
    ];

    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = MetaKeyword::select(['id','name','slug','is_active','created_at']);

            $searchValue = $request->input('search.value');
            if (!empty($searchValue)) {
                $query->where(function ($q) use ($searchValue) {
                    $q->where('name', 'ilike', "%{$searchValue}%")
                        ->orWhere('slug', 'ilike', "%{$searchValue}%");
                });
            }

            return datatables()->eloquent($query)
                ->addIndexColumn()
                ->addColumn('is_active', function (MetaKeyword $k) {
                    return $k->is_active ? 'Aktif' : 'Non-aktif';
                })
                ->addColumn('created_at', function ($k) {
                    return $k->created_at ? $k->created_at->format('d M Y H:i') : '-';
                })
                ->addColumn('action', function ($k) {
                    return view('meta-keywords._column_action', ['k' => $k])->render();
                })
                ->rawColumns(['action'])
                ->toJson();
        }

        return view('meta-keywords.index');
    }

    public function create()
    {
        return view('meta-keywords.create', ['metaKeyword' => new MetaKeyword()]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:meta_keywords,slug',
            'is_active' => 'sometimes|boolean',
        ], self::VALIDATION_MESSAGES);

        try {
            MetaKeyword::create($validated);
            return redirect()->route('meta_keywords.index')->with('success', 'Meta keyword berhasil ditambahkan');
        } catch (\Throwable $th) {
            return redirect()->back()->withInput()->with('error', $th->getMessage());
        }
    }

    public function edit(int $id)
    {
        $metaKeyword = MetaKeyword::findOrFail($id);
        return view('meta-keywords.edit', compact('metaKeyword'));
    }

    public function update(Request $request, int $id)
    {
        $metaKeyword = MetaKeyword::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => ['nullable','string','max:255', Rule::unique('meta_keywords','slug')->ignore($metaKeyword->id)],
            'is_active' => 'sometimes|boolean',
        ], self::VALIDATION_MESSAGES);

        try {
            $metaKeyword->update($validated);
            return redirect()->route('meta_keywords.index')->with('success', 'Meta keyword berhasil diperbarui');
        } catch (\Throwable $th) {
            return redirect()->back()->withInput()->with('error', $th->getMessage());
        }
    }

    public function destroy(int $id)
    {
        $metaKeyword = MetaKeyword::findOrFail($id);
        try {
            $metaKeyword->delete();
            return redirect()->route('meta_keywords.index')->with('success', 'Meta keyword berhasil dihapus');
        } catch (\Throwable $th) {
            return redirect()->back()->with('error', $th->getMessage());
        }
    }

    /**
     * Select2 AJAX search source used from the product create/edit form.
     */
    public function search(Request $request)
    {
        $q = trim((string) $request->input('q', ''));

        $query = MetaKeyword::where('is_active', true);
        if ($q !== '') {
            $query->where('name', 'ilike', "%{$q}%");
        }

        $results = $query->orderBy('name')->limit(20)->get(['id','name'])
            ->map(fn(MetaKeyword $k) => ['id' => $k->id, 'text' => $k->name]);

        return response()->json(['results' => $results]);
    }

    public function export()
    {
        return Excel::download(new MetaKeywordsExport(), 'meta_keywords.xlsx');
    }

    public function importForm()
    {
        return view('meta-keywords.import');
    }

    public function import(Request $request)
    {
        $request->validate([
            'excel' => 'required|file|mimes:xlsx,xls',
        ]);

        $path = $request->file('excel')->getRealPath();
        $rows = Excel::toArray([], $path)[0] ?? [];
        $header = array_map(fn($h) => strtolower(trim((string) $h)), array_shift($rows) ?? []);

        $nameIndex = array_search('name', $header, true);
        if ($nameIndex === false) {
            return redirect()->back()->with('error', "Kolom 'name' tidak ditemukan di file Excel.");
        }

        $created = 0;
        $existing = 0;

        foreach ($rows as $row) {
            if (count(array_filter($row)) === 0) continue;

            $name = trim((string) ($row[$nameIndex] ?? ''));
            if ($name === '') continue;

            $metaKeyword = MetaKeyword::withTrashed()
                ->whereRaw('LOWER(name) = ?', [strtolower($name)])
                ->first();

            if ($metaKeyword) {
                if ($metaKeyword->deleted_at) {
                    $metaKeyword->restore();
                }
                $existing++;
                continue;
            }

            $baseSlug = Str::slug($name);
            $slug = $baseSlug;
            $i = 1;
            while (MetaKeyword::where('slug', $slug)->exists()) {
                $slug = $baseSlug . '-' . $i++;
            }

            MetaKeyword::create([
                'name' => $name,
                'slug' => $slug,
                'is_active' => true,
            ]);
            $created++;
        }

        return redirect()->route('meta_keywords.index')
            ->with('success', "Import selesai: {$created} meta keyword dibuat, {$existing} sudah ada sebelumnya.");
    }
}
