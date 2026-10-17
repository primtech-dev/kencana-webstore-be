<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // ==== Permissions & Roles ====
        $this->call([
            PermissionSeeder::class,
            RoleSeeder::class,
            RolePermissionSeeder::class,
            RolePermissionSyncSeeder::class,
        ]);

        // ==== Permission Modules ====
        $this->call([
            BranchesPermissionSeeder::class,
            CategoryPermissionSeeder::class,
            SubCategoryPermissionSeeder::class,
            CustomerPermissionSeeder::class,
            MetaKeywordPermissionSeeder::class,
            OrdersPermissionSeeder::class,
            PaymentMethodSeeder::class,
            PermissionProductSeeder::class,
            StockPermissionSeeder::class,
            UnitPermissionSeeder::class,
            ReviewPermissionSeeder::class,
            HomeBannerPermissionSeeder::class,
            StockImportPermissionSeeder::class,
            BranchAdminRoleSeeder::class,
        ]);

        // ==== Master Data ====
        $this->call([
            BranchMasterSeeder::class,
            UserSeeder::class,
            BranchAccountSeeder::class,
            CustomerSeeder::class,
        ]);

        // ==== Dummy Orders (opsional) ====
        $this->call([
            OrderDummySeeder::class,
            OrderDummySeeder2::class,
        ]);
    }
}
