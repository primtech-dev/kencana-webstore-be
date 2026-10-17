<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class OrdersPermissionSeeder extends Seeder
{
    public function run()
    {
        $permissions = [
            'orders.view',      // melihat daftar & detail order
            'orders.manage',    // ubah status, delete, aksi manajemen order
            'orders.cancel',    // membatalkan order
            'orders.ship',      // menandai order dikirim (mengurangi stok)
            'orders.delete',    // menghapus order
            'orders.refund',    // refund
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm]);
        }

        // Role yang sudah memegang orders.manage otomatis mendapat izin aksi
        $manage = Permission::where('name', 'orders.manage')->first();
        if ($manage) {
            foreach (\Spatie\Permission\Models\Role::permission($manage)->get() as $role) {
                $role->givePermissionTo(['orders.cancel', 'orders.ship', 'orders.delete']);
            }
        }

        $this->command->info('Order permissions seeded.');
    }
}
