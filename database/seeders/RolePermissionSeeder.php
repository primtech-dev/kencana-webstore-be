<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Pastikan cache permission dibersihkan sebelum membuat
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            // Orders
            'orders.view', 'orders.manage', 'orders.cancel', 'orders.ship', 'orders.delete',
            // Inventory
            'inventory.view', 'inventory.adjust', 'inventory.receive',
            // Products
            'products.view', 'products.create', 'products.update', 'products.delete',
            // Shipments & returns
            'shipments.manage', 'returns.manage',
            // Customers
            'customers.view',
            // Branches (manajemen cabang)
            'branches.view', 'branches.create', 'branches.update', 'branches.delete',
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm]);
        }

        // Roles — gunakan nama konsisten
        $super = Role::firstOrCreate(['name' => 'superadmin']);
        $manager = Role::firstOrCreate(['name' => 'manager_cabang']);
        $sales = Role::firstOrCreate(['name' => 'admin_penjualan']);
        $inv = Role::firstOrCreate(['name' => 'admin_inventory']);

        // Assign permissions
        $super->syncPermissions(Permission::all()); // superadmin dapat semua
        $inv->syncPermissions(['inventory.view','inventory.adjust','inventory.receive','products.view']);
        $sales->syncPermissions(['orders.view','orders.manage','orders.cancel','orders.ship','orders.delete','shipments.manage']);
        $manager->syncPermissions(['orders.view','inventory.view','shipments.manage','customers.view']);

        // Pastikan cache di-clear lagi setelah perubahan
        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }
}
