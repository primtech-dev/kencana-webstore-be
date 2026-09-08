<?php

namespace App\Http\Controllers;

use App\Exports\SubCategoryImportTemplateExport;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use App\Support\ImageUploader;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class SubCategoryController extends Controller
{
    private const VALIDATION_MESSAGES = [
        'name.required' => 'Nama sub kategori tidak boleh kosong',
        'slug.unique'   => 'Slug sudah digunakan, gunakan nama lain atau tambahkan suffix',
        'parent_id.required' => 'Kategori induk wajib dipilih',
        'parent_id.exists' => 'Kategori induk tidak ditemukan',
    ];

    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = Category::select(['id','name','slug','parent_id','position','is_active','created_at','thumbnail'])
                ->whereNotNull('parent_id')
                ->with('parent');

            $searchValue = $request->input('search.value');
            if (!empty($searchValue)) {
                $query->where(function ($q) use ($searchValue) {
                    $q->where('name', 'ilike', "%{$searchValue}%")
                        ->orWhere('slug', 'ilike', "%{$searchValue}%");
                });
            }

            return datatables()->eloquent($query)
                ->addIndexColumn()
                ->addColumn('parent', function (Category $c) {
                    return $c->parent ? e($c->parent->name) : '-';
                })
                ->addColumn('is_active', function (Category $c) {
                    return $c->is_active ? 'Aktif' : 'Non-aktif';
                })
                ->addColumn('created_at', function ($c) {
                    return $c->created_at ? $c->created_at->format('d M Y H:i') : '-';
                })
                ->addColumn('thumbnail', function (Category $c) {
                    return $c->thumbnail_url
                        ? '<img src="'.$c->thumbnail_url.'" width="40" class="rounded">'
                        : '-';
                })
                ->addColumn('action', function ($c) {
                    return view('sub-categories._column_action', ['c' => $c])->render();
                })
                ->rawColumns(['thumbnail','action'])
                ->toJson();
        }

        return view('sub-categories.index');
    }

    public function create()
    {
        $parents = Category::whereNull('parent_id')->orderBy('position')->get();
        return view('sub-categories.create', ['subCategory' => new Category(), 'parents' => $parents]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:categories,slug',
            'parent_id' => ['required', 'integer', 'exists:categories,id', function ($attribute, $value, $fail) {
                $parent = Category::find($value);
                if ($parent && !is_null($parent->parent_id)) {
                    $fail('Kategori induk harus berupa kategori utama (bukan sub kategori lain).');
                }
            }],
            'position' => 'nullable|integer',
            'is_active' => 'sometimes|boolean',
            'thumbnail' => 'nullable|image|max:3000',
        ], self::VALIDATION_MESSAGES);

        try {
            if ($request->hasFile('thumbnail')) {
                $validated['thumbnail'] = ImageUploader::uploadWebp(
                    $request->file('thumbnail'),
                    'categories/thumbnails',
                    600,
                    80
                );
            }

            Category::create($validated);

            return redirect()->route('sub_categories.index')->with('success', 'Sub kategori berhasil ditambahkan');
        } catch (\Throwable $th) {
            return redirect()->back()->withInput()->with('error', $th->getMessage());
        }
    }

    public function edit(int $id)
    {
        $subCategory = Category::whereNotNull('parent_id')->find($id);
        if (!$subCategory) return abort(404);
        $parents = Category::whereNull('parent_id')->orderBy('position')->get();
        return view('sub-categories.edit', ['subCategory' => $subCategory, 'parents' => $parents]);
    }

    public function update(Request $request, int $id)
    {
        $subCategory = Category::whereNotNull('parent_id')->findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => ['nullable','string','max:255', Rule::unique('categories','slug')->ignore($subCategory->id)],
            'parent_id' => ['required', 'integer', 'exists:categories,id', function ($attribute, $value, $fail) use ($subCategory) {
                if ($value == $subCategory->id) {
                    $fail('Kategori induk tidak boleh sama dengan sub kategori itu sendiri.');
                    return;
                }
                $parent = Category::find($value);
                if ($parent && !is_null($parent->parent_id)) {
                    $fail('Kategori induk harus berupa kategori utama (bukan sub kategori lain).');
                }
            }],
            'position' => 'nullable|integer',
            'is_active' => 'sometimes|boolean',
            'thumbnail' => 'nullable|image|max:3000',
        ], self::VALIDATION_MESSAGES);

        try {
            if ($request->hasFile('thumbnail')) {
                if ($subCategory->thumbnail) {
                    Storage::disk('public')->delete($subCategory->thumbnail);
                }

                $validated['thumbnail'] = ImageUploader::uploadWebp(
                    $request->file('thumbnail'),
                    'categories/thumbnails',
                    600,
                    80
                );
            }

            $subCategory->update($validated);
            return redirect()->route('sub_categories.index')->with('success', 'Sub kategori berhasil diperbarui');
        } catch (\Throwable $th) {
            return redirect()->back()->withInput()->with('error', $th->getMessage());
        }
    }

    public function destroy(int $id)
    {
        $subCategory = Category::whereNotNull('parent_id')->findOrFail($id);
        try {
            $subCategory->delete();
            return redirect()->route('sub_categories.index')->with('success', 'Sub kategori berhasil dihapus');
        } catch (\Throwable $th) {
            return redirect()->back()->with('error', $th->getMessage());
        }
    }

    public function importForm()
    {
        return view('sub-categories.import');
    }

    public function downloadImportTemplate()
    {
        return Excel::download(new SubCategoryImportTemplateExport(), 'sub_kategori_import_template.xlsx');
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
        $parentIndex = array_search('parent_category', $header, true);
        if ($nameIndex === false || $parentIndex === false) {
            return redirect()->back()->with('error', "Kolom 'name' dan 'parent_category' harus ada di file Excel.");
        }

        $created = 0;
        $existing = 0;
        $skipped = [];

        foreach ($rows as $i => $row) {
            if (count(array_filter($row)) === 0) continue;
            $rowNum = $i + 2;

            $name = trim((string) ($row[$nameIndex] ?? ''));
            $parentName = trim((string) ($row[$parentIndex] ?? ''));

            if ($name === '' || $parentName === '') {
                $skipped[] = "Baris {$rowNum}: kolom 'name' atau 'parent_category' kosong";
                continue;
            }

            $parent = Category::whereNull('parent_id')
                ->whereRaw('LOWER(name) = ?', [strtolower($parentName)])
                ->first();

            if (!$parent) {
                $skipped[] = "Baris {$rowNum}: kategori induk '{$parentName}' tidak ditemukan";
                continue;
            }

            $subCategory = Category::withTrashed()
                ->whereNotNull('parent_id')
                ->whereRaw('LOWER(name) = ?', [strtolower($name)])
                ->first();

            if ($subCategory) {
                if ($subCategory->deleted_at) {
                    $subCategory->restore();
                }
                $existing++;
                continue;
            }

            $baseSlug = Str::slug($name);
            $slug = $baseSlug;
            $suffix = 1;
            while (Category::where('slug', $slug)->exists()) {
                $slug = $baseSlug . '-' . $suffix++;
            }

            Category::create([
                'name' => $name,
                'slug' => $slug,
                'parent_id' => $parent->id,
                'is_active' => true,
            ]);
            $created++;
        }

        $message = "Import selesai: {$created} sub kategori dibuat, {$existing} sudah ada sebelumnya.";
        if (!empty($skipped)) {
            $detail = implode('; ', array_slice($skipped, 0, 10));
            if (count($skipped) > 10) {
                $detail .= '; dan ' . (count($skipped) - 10) . ' baris lainnya';
            }
            $message .= ' ' . count($skipped) . ' baris dilewati: ' . $detail;
        }

        return redirect()->route('sub_categories.index')->with($skipped ? 'warning' : 'success', $message);
    }
}
