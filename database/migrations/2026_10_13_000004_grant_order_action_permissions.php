<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Aksi order kini dicek per-aksi (orders.cancel / orders.ship / orders.delete).
 * Agar perilaku role yang sudah ada tidak berubah, semua role yang memegang
 * orders.manage otomatis diberi permission aksi tersebut.
 */
return new class extends Migration
{
    private array $perms = ['orders.cancel', 'orders.ship', 'orders.delete'];

    public function up(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach ($this->perms as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }

        $manage = Permission::where('name', 'orders.manage')->where('guard_name', 'web')->first();
        if ($manage) {
            foreach (Role::permission($manage)->get() as $role) {
                $role->givePermissionTo($this->perms);
            }
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        // permission dibiarkan (aman); tidak ada yang perlu dikembalikan
    }
};
