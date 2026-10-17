<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Password dari env SEED_ADMIN_PASSWORD. Default 'password' hanya di luar production;
        // di production tanpa env, password acak dibuat dan ditampilkan sekali.
        $password = env('SEED_ADMIN_PASSWORD');
        if (! $password) {
            $password = app()->environment('production') ? Str::random(20) : 'password';
            if (app()->environment('production')) {
                $this->command?->warn("Password superadmin@mail.com (catat, hanya tampil sekali): {$password}");
            }
        }

        // Create super admin user
        $superAdmin = User::firstOrCreate(
            ['email' => 'superadmin@mail.com'],
            [
                'name' => 'Admin',
                'password' => Hash::make($password),
                'is_superadmin' => true,
            ]
        );
        if (! $superAdmin->is_superadmin) {
            $superAdmin->forceFill(['is_superadmin' => true])->save();
        }

        $role = Role::firstOrCreate(['name' => 'superadmin']);
        $superAdmin->syncRoles([$role->name]);

        // \Spatie\Permission\PermissionRegistrar::forgetCachedPermissions();
        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
