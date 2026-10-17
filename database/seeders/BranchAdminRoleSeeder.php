<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Role admin cabang: operasional order & stok di cabangnya sendiri.
 * Pembatasan data per cabang dilakukan oleh aplikasi (User::branch_id), bukan oleh role.
 */
class BranchAdminRoleSeeder extends Seeder
{
    public const PERMISSIONS = [
        'orders.view', 'orders.manage', 'orders.cancel', 'orders.ship', 'orders.delete',
        'stock_receipts.view', 'stock_receipts.create', 'stock_receipts.update', 'stock_receipts.delete',
        'stock_imports.create',
        'customers.view',
        'products.view',
        'branches.view', 'branches.update',
        'users.view', 'users.create', 'users.update',
    ];

    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (self::PERMISSIONS as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }

        $role = Role::firstOrCreate(['name' => 'branch_admin', 'guard_name' => 'web']);
        $role->syncPermissions(self::PERMISSIONS);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
