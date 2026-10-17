<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class StockImportPermissionSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $perm = Permission::firstOrCreate(['name' => 'stock_imports.create', 'guard_name' => 'web']);

        Role::firstOrCreate(['name' => 'superadmin', 'guard_name' => 'web'])->givePermissionTo($perm);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
