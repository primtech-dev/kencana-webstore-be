<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Permission untuk fitur Import Update Stok. Diberikan ke superadmin dan role
 * yang sudah boleh membuat penerimaan stok (stock_receipts.create).
 */
return new class extends Migration
{
    public function up(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $perm = Permission::firstOrCreate(['name' => 'stock_imports.create', 'guard_name' => 'web']);

        $roles = Role::where('name', 'superadmin')->get();
        $source = Permission::where('name', 'stock_receipts.create')->where('guard_name', 'web')->first();
        if ($source) {
            $roles = $roles->merge(Role::permission($source)->get())->unique('id');
        }
        foreach ($roles as $role) {
            $role->givePermissionTo($perm);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
    }
};
