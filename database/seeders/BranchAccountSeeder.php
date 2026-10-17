<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\User;
use Database\Seeders\Concerns\ResyncsPgsqlSequences;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Akun per cabang (admin_inventory, admin_penjualan, manager_cabang) + 1 admin pusat.
 *
 * Format email: {peran}.{cabang}@kencanastore.co.id, contoh
 *   inventory.gresik_kebomas@kencanastore.co.id
 *   penjualan.gresik_kebomas@kencanastore.co.id
 *   manager.gresik_kebomas@kencanastore.co.id
 *   adminpusat@kencanastore.co.id
 *
 * Password default (hanya dipakai saat akun DIBUAT; akun yang sudah ada tidak diubah
 * passwordnya): env SEED_ACCOUNT_PASSWORD, default "kencanastore".
 * Wajib dijalankan setelah BranchMasterSeeder.
 */
class BranchAccountSeeder extends Seeder
{
    use ResyncsPgsqlSequences;

    public const DOMAIN = 'kencanastore.co.id';

    /** role => [awalan email, label nama] */
    public const ROLES = [
        'admin_inventory' => ['inventory', 'Admin Inventory'],
        'admin_penjualan' => ['penjualan', 'Admin Penjualan'],
        'manager_cabang' => ['manager', 'Manager Cabang'],
    ];

    public function run(): void
    {
        $this->resyncPgsqlSequences();
        $this->call(BranchRoleSeeder::class);

        $password = Hash::make(env('SEED_ACCOUNT_PASSWORD', 'kencanastore'));
        $master = require database_path('data/branches_master.php');
        $rows = [];

        foreach ($master as $row) {
            $branch = Branch::where('code', $row['code'])->first();
            if (! $branch) {
                throw new \RuntimeException("Cabang kode {$row['code']} belum ada. Jalankan BranchMasterSeeder terlebih dahulu.");
            }

            $slug = self::slug($row['name']);
            $label = Str::of($slug)->replace('_', ' ')->title();

            foreach (self::ROLES as $role => [$prefix, $roleLabel]) {
                $rows[] = $this->upsert(
                    "{$prefix}.{$slug}@" . self::DOMAIN,
                    "{$roleLabel} {$label}",
                    $role,
                    $branch->id,
                    $password
                );
            }
        }

        $rows[] = $this->upsert('adminpusat@' . self::DOMAIN, 'Admin Pusat', 'admin_pusat', null, $password);

        $this->command?->table(['Status', 'Email', 'Role', 'Cabang'], $rows);
    }

    /** "Kencana Store GRESIK_KEBOMAS" -> "gresik_kebomas" */
    public static function slug(string $branchName): string
    {
        $name = preg_replace('/^kencana store\s+/i', '', $branchName);

        return trim(preg_replace('/[^a-z0-9]+/', '_', mb_strtolower($name)), '_');
    }

    private function upsert(string $email, string $name, string $role, ?int $branchId, string $passwordHash): array
    {
        $user = User::where('email', $email)->first();
        $status = $user ? 'ADA (diperbarui)' : 'DIBUAT';

        if (! $user) {
            $user = new User(['email' => $email, 'password' => $passwordHash]);
        }

        $user->fill(['name' => $name, 'branch_id' => $branchId, 'is_active' => true, 'is_superadmin' => false])->save();
        $user->syncRoles([$role]);

        return [$status, $email, $role, $branchId ?? 'Pusat'];
    }
}
