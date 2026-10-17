<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Menu Stok & Riwayat Stok memakai permission inventory.view. Diberikan ke superadmin dan
 * role yang sudah bekerja dengan stok/order (stock_receipts.view, orders.view, stock_imports.create).
 */
return new class extends Migration
{
    public function up(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $perm = Permission::firstOrCreate(['name' => 'inventory.view', 'guard_name' => 'web']);

        $roles = Role::where('name', 'superadmin')->get();
        foreach (['stock_receipts.view', 'orders.view', 'stock_imports.create'] as $source) {
            $p = Permission::where('name', $source)->where('guard_name', 'web')->first();
            if ($p) {
                $roles = $roles->merge(Role::permission($p)->get());
            }
        }

        foreach ($roles->unique('id') as $role) {
            $role->givePermissionTo($perm);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
    }
};
