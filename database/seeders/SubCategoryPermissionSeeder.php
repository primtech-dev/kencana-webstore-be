<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class SubCategoryPermissionSeeder extends Seeder
{
    public function run()
    {
        $perms = [
            'sub_categories.view',
            'sub_categories.create',
            'sub_categories.update',
            'sub_categories.delete'
        ];

        foreach ($perms as $p) {
            Permission::firstOrCreate(['name' => $p]);
        }

        // contoh assign ke role admin
        $admin = Role::firstOrCreate(['name' => 'superadmin']);
        $admin->givePermissionTo($perms);
    }
}
