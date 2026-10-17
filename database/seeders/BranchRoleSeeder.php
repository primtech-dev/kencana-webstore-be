<?php

namespace Database\Seeders;

use Database\Seeders\Concerns\ResyncsPgsqlSequences;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Hak akses role operasional cabang + admin pusat. Bersifat menambah (givePermissionTo),
 * tidak menghapus izin yang sudah diatur manual di role tersebut.
 * Pembatasan data per cabang dilakukan aplikasi (users.branch_id), bukan oleh role.
 */
class BranchRoleSeeder extends Seeder
{
    use ResyncsPgsqlSequences;

    private const INVENTORY = [
        'products.view', 'categories.view', 'sub_categories.view', 'units.view', 'branches.view',
        'stock_receipts.view', 'stock_receipts.create', 'stock_receipts.update', 'stock_receipts.delete',
        'stock_imports.create',
        'inventory.view', 'inventory.adjust', 'inventory.receive',
    ];

    private const SALES = [
        'products.view', 'branches.view', 'inventory.view',
        'orders.view', 'orders.manage', 'orders.cancel', 'orders.ship',
        'customers.view', 'reviews.view', 'shipments.manage',
    ];

    private const MANAGER_EXTRA = [
        'orders.delete', 'reviews.manage',
        'branches.update',
        'users.view', 'users.create', 'users.update',
    ];

    public function run(): void
    {
        $this->resyncPgsqlSequences();

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $sets = [
            'admin_inventory' => self::INVENTORY,
            'admin_penjualan' => self::SALES,
            'manager_cabang' => array_values(array_unique(array_merge(self::INVENTORY, self::SALES, self::MANAGER_EXTRA))),
        ];

        foreach ($sets as $roleName => $permissions) {
            $role = Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
            foreach ($permissions as $name) {
                Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
            }
            $role->givePermissionTo($permissions);
        }

        // Admin pusat: semua izin operasional, kecuali pengelolaan role & permission (khusus superadmin)
        $pusat = Role::firstOrCreate(['name' => 'admin_pusat', 'guard_name' => 'web']);
        $pusat->givePermissionTo(
            Permission::query()
                ->where('guard_name', 'web')
                ->where('name', 'not like', 'roles.%')
                ->where('name', 'not like', 'permissions.%')
                ->pluck('name')
                ->all()
        );

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
