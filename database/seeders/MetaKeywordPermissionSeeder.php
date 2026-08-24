<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class MetaKeywordPermissionSeeder extends Seeder
{
    public function run()
    {
        $perms = [
            'meta_keywords.view',
            'meta_keywords.create',
            'meta_keywords.update',
            'meta_keywords.delete'
        ];

        foreach ($perms as $p) {
            Permission::firstOrCreate(['name' => $p]);
        }

        $admin = Role::firstOrCreate(['name' => 'superadmin']);
        $admin->givePermissionTo($perms);
    }
}
