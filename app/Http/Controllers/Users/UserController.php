<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserRequest;
use App\Models\Branch;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax() || $request->wantsJson()) {
            try {
                $query = User::manageableBy($request->user())
                    ->select(['users.id', 'users.name', 'users.email', 'users.branch_id', 'users.is_superadmin', 'users.is_active', 'users.created_at'])
                    ->with(['branch', 'roles']);

                return datatables()->eloquent($query)
                    ->addIndexColumn()
                    ->filter(function ($q) use ($request) {
                        $keyword = trim((string) $request->input('search.value'));
                        if ($keyword === '') {
                            return;
                        }
                        $like = '%' . mb_strtolower($keyword) . '%';
                        $q->where(function ($w) use ($like) {
                            $w->whereRaw('LOWER(users.name) LIKE ?', [$like])
                                ->orWhereRaw('LOWER(users.email) LIKE ?', [$like])
                                ->orWhereRaw('LOWER(users.phone) LIKE ?', [$like]);
                        });
                    })
                    ->addColumn('branch', fn (User $u) => $u->branch ? e($u->branch->name) : 'Pusat')
                    ->addColumn('roles', function (User $u) {
                        $names = $u->roles->pluck('name')->map(fn ($r) => e($r))->toArray();
                        if (empty($names)) {
                            return '-';
                        }

                        return collect($names)->map(fn ($n) => "<span class=\"badge bg-info me-1 mb-1\">{$n}</span>")->implode(' ');
                    })
                    ->addColumn('is_superadmin', fn (User $u) => $u->is_superadmin ? '<span class="badge bg-warning">Superadmin</span>' : '-')
                    ->addColumn('is_active', fn (User $u) => $u->is_active
                        ? '<span class="badge bg-success">Aktif</span>'
                        : '<span class="badge bg-secondary">Non-aktif</span>')
                    ->addColumn('created_at', fn ($u) => $u->created_at ? $u->created_at->format('d M Y H:i') : '-')
                    ->addColumn('action', fn ($u) => view('users._column_action', ['u' => $u])->render())
                    ->rawColumns(['roles', 'is_superadmin', 'is_active', 'action'])
                    ->toJson();
            } catch (\Throwable $e) {
                Log::error('UserController@index error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);

                return response()->json(['error' => 'Server error, cek logs.'], 500);
            }
        }

        return view('users.index');
    }

    public function create(Request $request)
    {
        return view('users.create', [
            'user' => new User(),
            'branches' => $this->selectableBranches($request->user()),
            'roles' => $this->selectableRoles($request->user()),
        ]);
    }

    public function store(UserRequest $request)
    {
        $actor = $request->user();
        $data = $request->validated();

        $data['branch_id'] = $this->resolveBranchId($actor, $data['branch_id'] ?? null);
        $data['is_superadmin'] = $actor->isSuperAdmin() && $request->boolean('is_superadmin');
        $data['password'] = Hash::make($data['password']);
        $roles = $data['roles'] ?? [];
        unset($data['roles']);

        try {
            DB::transaction(function () use ($data, $roles) {
                $user = User::create($data);
                $user->syncRoles($roles);
            });

            return redirect()->route('users.index')->with('success', 'User berhasil ditambahkan');
        } catch (\Throwable $th) {
            Log::error('User store failed', ['error' => $th->getMessage()]);

            return redirect()->back()->withInput()->with('error', 'Gagal menyimpan user. Silakan coba lagi.');
        }
    }

    public function edit(Request $request, int $id)
    {
        $user = User::manageableBy($request->user())->findOrFail($id);

        return view('users.edit', [
            'user' => $user,
            'branches' => $this->selectableBranches($request->user(), $user->branch_id),
            'roles' => $this->selectableRoles($request->user(), $user),
        ]);
    }

    public function update(UserRequest $request, int $id)
    {
        $actor = $request->user();
        $user = User::manageableBy($actor)->findOrFail($id);
        $isSelf = $actor->id === $user->id;

        $data = $request->validated();
        $data['branch_id'] = $this->resolveBranchId($actor, $data['branch_id'] ?? null);

        // Hanya superadmin yang boleh mengubah flag superadmin; tidak boleh mengubah diri sendiri
        if ($actor->isSuperAdmin() && ! $isSelf) {
            $data['is_superadmin'] = $request->boolean('is_superadmin');
        } else {
            unset($data['is_superadmin']);
        }

        // Tidak boleh menonaktifkan diri sendiri / mengubah hak & cabang sendiri
        if ($isSelf) {
            unset($data['is_active']);
            if (! $actor->hasAllBranchScope()) {
                unset($data['branch_id']);
            }
        }

        if (! empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $newRoles = $data['roles'] ?? [];
        unset($data['roles']);

        // Susun role akhir: pertahankan role yang tidak boleh diubah actor, ganti sisanya
        $finalRoles = $user->roles->pluck('name')->all();
        if (! $isSelf) {
            $assignable = $actor->assignableRoleNames();
            $keep = array_values(array_filter($finalRoles, fn ($r) => ! in_array($r, $assignable, true)));
            $finalRoles = array_values(array_unique(array_merge($keep, $newRoles)));
        }

        // Jangan sampai superadmin aktif terakhir kehilangan status / dinonaktifkan
        if ($user->isSuperAdmin() && User::activeSuperAdminCount() <= 1) {
            $stillSuper = ($data['is_superadmin'] ?? $user->is_superadmin) || in_array('superadmin', $finalRoles, true);
            $stillActive = $data['is_active'] ?? $user->is_active;
            if (! $stillSuper || ! $stillActive) {
                return redirect()->back()->withInput()->with('error', 'Tidak dapat menonaktifkan / mencabut hak superadmin terakhir.');
            }
        }

        try {
            DB::transaction(function () use ($user, $data, $finalRoles, $isSelf) {
                $user->update($data);

                if (! $isSelf) {
                    $user->syncRoles($finalRoles);
                }
            });

            return redirect()->route('users.index')->with('success', 'User berhasil diperbarui');
        } catch (\Throwable $th) {
            Log::error('User update failed', ['id' => $id, 'error' => $th->getMessage()]);

            return redirect()->back()->withInput()->with('error', 'Gagal memperbarui user. Silakan coba lagi.');
        }
    }

    public function destroy(Request $request, int $id)
    {
        $actor = $request->user();
        $user = User::manageableBy($actor)->findOrFail($id);

        if ($actor->id === $user->id) {
            return redirect()->back()->with('error', 'Anda tidak dapat menghapus akun yang sedang digunakan.');
        }

        if ($user->isSuperAdmin() && User::activeSuperAdminCount() <= 1) {
            return redirect()->back()->with('error', 'Tidak dapat menghapus superadmin terakhir.');
        }

        try {
            $user->delete();

            return redirect()->route('users.index')->with('success', 'User berhasil dihapus');
        } catch (\Throwable $th) {
            Log::error('User destroy failed', ['id' => $id, 'error' => $th->getMessage()]);

            return redirect()->back()->with('error', 'Gagal menghapus user. Silakan coba lagi.');
        }
    }

    public function show(Request $request, int $id)
    {
        $user = User::manageableBy($request->user())->with('branch', 'roles')->findOrFail($id);

        return view('users.show', compact('user'));
    }

    // ------------------------------------------------------------------ helpers

    /** User cabang selalu dipaksa ke cabangnya; pusat bebas memilih (null = pusat). */
    private function resolveBranchId(User $actor, $requested): ?int
    {
        if (! $actor->hasAllBranchScope()) {
            return (int) $actor->branch_id;
        }

        return $requested !== null && $requested !== '' ? (int) $requested : null;
    }

    private function selectableBranches(User $actor, ?int $includeId = null)
    {
        return Branch::accessibleBy($actor)
            ->where(fn ($q) => $q->where('is_active', true)->when($includeId, fn ($x) => $x->orWhere('id', $includeId)))
            ->orderBy('name')
            ->get();
    }

    private function selectableRoles(User $actor, ?User $target = null)
    {
        $names = $actor->assignableRoleNames();

        return Role::whereIn('name', $names)->orderBy('name')->get();
    }
}
