<?php

namespace Tests\Support;

use App\Models\Branch;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

trait StoreFixtures
{
    protected function makeBranch(string $code = 'B1', bool $active = true): Branch
    {
        return Branch::create(['code' => $code, 'name' => 'Cabang ' . $code, 'is_active' => $active]);
    }

    protected function makeVariant(string $sku = 'SKU-1', string $unit = 'pcs'): ProductVariant
    {
        $u = Unit::firstOrCreate(['name' => $unit]);
        $product = Product::create(['name' => 'Produk ' . $sku, 'sku' => 'P-' . $sku, 'unit_id' => $u->id]);

        return ProductVariant::create([
            'product_id' => $product->id,
            'sku' => $sku,
            'variant_name' => 'Varian ' . $sku,
            'price' => 10000,
            'unit_id' => $u->id,
        ]);
    }

    protected function setInventory(Branch $branch, ProductVariant $variant, string|int|float $onHand, string|int|float $reserved = 0): void
    {
        DB::table('inventory')->updateOrInsert(
            ['variant_id' => $variant->id, 'branch_id' => $branch->id],
            [
                'on_hand' => $onHand, 'reserved' => $reserved,
                'available' => max(0, $onHand - $reserved),
                'created_at' => now(), 'updated_at' => now(),
            ]
        );
    }

    protected function inventoryRow(Branch $branch, ProductVariant $variant): ?object
    {
        return DB::table('inventory')->where('variant_id', $variant->id)->where('branch_id', $branch->id)->first();
    }

    protected function makeCustomer(): Customer
    {
        return Customer::create(['public_id' => (string) Str::uuid(), 'full_name' => 'Budi Santoso', 'password_hash' => 'x']);
    }

    /** @param list<array{0: ProductVariant, 1: string|int|float}> $lines */
    protected function makeOrder(Branch $branch, array $lines, string $status = 'pending'): Order
    {
        $order = Order::create([
            'customer_id' => $this->makeCustomer()->id,
            'branch_id' => $branch->id,
            'status' => $status,
            'subtotal_before_discount' => 10000,
            'discount_total' => 0,
            'subtotal_after_discount' => 10000,
            'total_amount' => 10000,
            'payment_status' => 'pending',
        ]);

        foreach ($lines as [$variant, $qty]) {
            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $variant->product_id,
                'variant_id' => $variant->id,
                'sku' => $variant->sku,
                'product_name' => 'Produk ' . $variant->sku,
                'unit_price' => 10000,
                'line_quantity' => $qty,
                'line_total_before_discount' => 10000,
                'line_discount' => 0,
                'line_total_after_discount' => 10000,
                'line_total' => 10000,
            ]);
        }

        return $order;
    }

    /** User dengan role berisi $permissions. $branch null = pusat. */
    protected function makeAdmin(?Branch $branch, array $permissions, string $roleName = 'test_role'): User
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach ($permissions as $p) {
            Permission::firstOrCreate(['name' => $p, 'guard_name' => 'web']);
        }
        $role = Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
        $role->syncPermissions($permissions);

        $user = User::factory()->create(['branch_id' => $branch?->id, 'is_active' => true]);
        $user->assignRole($role);

        return $user;
    }

    protected function makeSuperAdmin(): User
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        Role::firstOrCreate(['name' => 'superadmin', 'guard_name' => 'web']);
        $user = User::factory()->create(['is_active' => true, 'is_superadmin' => true]);
        $user->assignRole('superadmin');

        return $user;
    }

    protected function ajax(): static
    {
        return $this->withHeaders(['X-Requested-With' => 'XMLHttpRequest', 'Accept' => 'application/json']);
    }
}
