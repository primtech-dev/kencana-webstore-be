<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasRoles, HasFactory, Notifiable;

    protected $fillable = [
        'name','email','password','phone','address','branch_id','is_superadmin','is_active','meta'
    ];

    public function branch()
    {
        return $this->belongsTo(\App\Models\Branch::class);
    }

    public function isSuperAdmin(): bool
    {
        return (bool)$this->is_superadmin || $this->hasRole('superadmin');
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected $casts = [
        'is_superadmin' => 'boolean',
        'is_active' => 'boolean',
        'meta' => 'array',
        'email_verified_at' => 'datetime',
        'last_login_at' => 'datetime',
    ];

    /**
     * Pusat = superadmin atau user tanpa branch_id (akses semua cabang).
     * Cabang = user dengan branch_id terisi (hanya data cabangnya).
     */
    public function hasAllBranchScope(): bool
    {
        return $this->isSuperAdmin() || $this->branch_id === null;
    }

    public function scopedBranchId(): ?int
    {
        return $this->hasAllBranchScope() ? null : (int) $this->branch_id;
    }

    // helper check: apakah user boleh akses resource cabang $branchId
    public function canAccessBranch(?int $branchId): bool
    {
        if ($this->hasAllBranchScope()) return true;
        if (is_null($branchId)) return false;
        return (int) $this->branch_id === (int) $branchId;
    }

    /**
     * Role yang boleh di-assign user ini ke user lain:
     * superadmin -> semua; lainnya -> hanya role yang izinnya tidak melebihi izin miliknya
     * dan bukan role superadmin (anti eskalasi hak).
     *
     * @return list<string>
     */
    public function assignableRoleNames(): array
    {
        if ($this->isSuperAdmin()) {
            return Role::query()->pluck('name')->all();
        }

        $mine = $this->getAllPermissions()->pluck('name');

        return Role::query()
            ->with('permissions:id,name')
            ->where('name', '!=', 'superadmin')
            ->get()
            ->filter(fn ($role) => $role->permissions->pluck('name')->diff($mine)->isEmpty())
            ->pluck('name')
            ->values()
            ->all();
    }

    /**
     * User yang boleh dikelola $actor: user cabang hanya mengelola user cabangnya,
     * dan hanya superadmin yang boleh menyentuh akun superadmin.
     */
    public function scopeManageableBy($query, User $actor)
    {
        if (! $actor->hasAllBranchScope()) {
            $query->where('users.branch_id', (int) $actor->branch_id);
        }

        if (! $actor->isSuperAdmin()) {
            $query->where('users.is_superadmin', false)
                ->whereDoesntHave('roles', fn ($r) => $r->where('name', 'superadmin'));
        }

        return $query;
    }

    /** Jumlah superadmin aktif (untuk mencegah menghapus/menonaktifkan yang terakhir). */
    public static function activeSuperAdminCount(): int
    {
        return static::query()
            ->where('is_active', true)
            ->where(fn ($q) => $q->where('is_superadmin', true)->orWhereHas('roles', fn ($r) => $r->where('name', 'superadmin')))
            ->count();
    }
}
