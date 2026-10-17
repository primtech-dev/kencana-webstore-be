<?php

namespace App\Http\Controllers;

use App\Http\Requests\BranchRequest;
use App\Models\Branch;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BranchController extends Controller
{
    public function index(Request $request)
    {
        // jika request AJAX (DataTables)
        if ($request->ajax()) {
            $query = Branch::accessibleBy($request->user())
                ->select(['id', 'code', 'name', 'city', 'province', 'phone', 'is_active', 'created_at'])
                ->withCount(['users', 'orders']);

            return datatables()->eloquent($query)
                ->addIndexColumn()
                ->filter(function ($q) use ($request) {
                    $search = trim((string) $request->input('search.value'));
                    if ($search === '') {
                        return;
                    }
                    $like = '%' . mb_strtolower($search) . '%';
                    $q->where(function ($w) use ($like) {
                        $w->whereRaw('LOWER(name) LIKE ?', [$like])
                            ->orWhereRaw('LOWER(code) LIKE ?', [$like])
                            ->orWhereRaw('LOWER(city) LIKE ?', [$like]);
                    });
                })
                ->addColumn('is_active', fn ($b) => $b->is_active ? 'Aktif' : 'Non-aktif')
                ->addColumn('created_at', fn ($b) => $b->created_at ? $b->created_at->format('d M Y H:i') : '-')
                ->addColumn('action', fn ($b) => view('branches._column_action', ['b' => $b])->render())
                ->rawColumns(['action'])
                ->toJson();
        }

        return view('branches.index');
    }

    public function create(Request $request)
    {
        $this->requireAllBranchScope($request);

        return view('branches.create', ['branch' => new Branch(), 'admins' => collect()]);
    }

    public function store(BranchRequest $request)
    {
        $this->requireAllBranchScope($request);

        $data = $request->validated();
        // manager belum bisa dipilih sebelum cabang punya user
        unset($data['manager_user_id']);

        try {
            Branch::create($data);

            return redirect()->route('branches.index')->with('success', 'Cabang berhasil ditambahkan');
        } catch (\Throwable $th) {
            Log::error('Branch store failed', ['error' => $th->getMessage()]);

            return redirect()->back()->withInput()->with('error', 'Gagal menyimpan cabang. Silakan coba lagi.');
        }
    }

    public function edit(Request $request, int $id)
    {
        $branch = $this->findAccessible($request->user(), $id);
        $admins = $this->managerCandidates($branch);

        return view('branches.edit', compact('branch', 'admins'));
    }

    public function update(BranchRequest $request, int $id)
    {
        $user = $request->user();
        $branch = $this->findAccessible($user, $id);

        $data = $request->validated();

        // Admin cabang tidak boleh mengubah kode & status aktif cabangnya
        if (! $user->hasAllBranchScope()) {
            unset($data['code'], $data['is_active']);
        }

        // Tidak boleh menonaktifkan cabang aktif terakhir
        if (array_key_exists('is_active', $data) && ! $data['is_active'] && $branch->is_active) {
            $otherActive = Branch::active()->where('id', '!=', $branch->id)->exists();
            if (! $otherActive) {
                return redirect()->back()->withInput()->with('error', 'Tidak dapat menonaktifkan cabang aktif terakhir.');
            }
        }

        try {
            $branch->update($data);

            return redirect()->route('branches.index')->with('success', 'Cabang berhasil diperbarui');
        } catch (\Throwable $th) {
            Log::error('Branch update failed', ['id' => $id, 'error' => $th->getMessage()]);

            return redirect()->back()->withInput()->with('error', 'Gagal memperbarui cabang. Silakan coba lagi.');
        }
    }

    public function destroy(Request $request, int $id)
    {
        $this->requireAllBranchScope($request);

        $branch = Branch::findOrFail($id);

        $blockers = [];
        if ($branch->users()->exists()) {
            $blockers[] = 'masih memiliki user';
        }
        if ($branch->orders()->exists()) {
            $blockers[] = 'memiliki riwayat order';
        }
        if ($branch->stockReceipts()->exists()) {
            $blockers[] = 'memiliki penerimaan stok';
        }
        if ($branch->inventories()->where('on_hand', '>', 0)->exists()) {
            $blockers[] = 'masih memiliki stok';
        }
        if ($blockers) {
            return redirect()->back()->with(
                'error',
                'Cabang tidak dapat dihapus karena ' . implode(', ', $blockers) . '. Nonaktifkan cabang sebagai gantinya.'
            );
        }

        if ($branch->is_active && ! Branch::active()->where('id', '!=', $branch->id)->exists()) {
            return redirect()->back()->with('error', 'Tidak dapat menghapus cabang aktif terakhir.');
        }

        try {
            DB::transaction(function () use ($branch) {
                $branch->inventories()->delete();
                $branch->delete(); // soft delete
            });

            return redirect()->route('branches.index')->with('success', 'Cabang berhasil dihapus');
        } catch (\Throwable $th) {
            Log::error('Branch destroy failed', ['id' => $id, 'error' => $th->getMessage()]);

            return redirect()->back()->with('error', 'Gagal menghapus cabang. Silakan coba lagi.');
        }
    }

    public function show(Request $request, int $id)
    {
        $branch = $this->findAccessible($request->user(), $id);
        $branch->load('manager')->loadCount(['users', 'orders', 'stockReceipts']);
        $users = $branch->users()->orderBy('name')->get(['id', 'name', 'email', 'is_active']);

        return view('branches.show', compact('branch', 'users'));
    }

    private function findAccessible(User $user, int $id): Branch
    {
        return Branch::accessibleBy($user)->findOrFail($id);
    }

    /** User aktif yang boleh dijadikan manager cabang tsb. */
    private function managerCandidates(Branch $branch)
    {
        return User::where('is_active', true)
            ->where(fn ($q) => $q->where('branch_id', $branch->id)->orWhere('id', $branch->manager_user_id))
            ->orderBy('name')
            ->get(['id', 'name', 'email']);
    }

    private function requireAllBranchScope(Request $request): void
    {
        abort_unless($request->user()->hasAllBranchScope(), 403, 'Hanya pusat yang dapat melakukan aksi ini.');
    }
}
