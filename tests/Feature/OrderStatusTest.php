<?php

namespace Tests\Feature;

use App\Support\Qty;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\StoreFixtures;
use Tests\TestCase;

class OrderStatusTest extends TestCase
{
    use RefreshDatabase, StoreFixtures;

    private const PERMS = ['orders.view', 'orders.manage', 'orders.cancel', 'orders.ship', 'orders.delete'];

    private function setStatus($admin, $order, string $status, ?string $notes = null)
    {
        return $this->actingAs($admin)->post(route('admin.orders.updateStatus', $order->id), ['status' => $status, 'notes' => $notes]);
    }

    private function on($branch, $variant): array
    {
        $r = $this->inventoryRow($branch, $variant);

        return [Qty::norm($r->on_hand), Qty::norm($r->reserved), Qty::norm($r->available)];
    }

    public function test_full_lifecycle_moves_decimal_stock_correctly(): void
    {
        $b = $this->makeBranch();
        $v = $this->makeVariant('KG-1', 'kg');
        $this->setInventory($b, $v, '10.50', '2.25');
        $order = $this->makeOrder($b, [[$v, '2.25']]);
        $admin = $this->makeAdmin($b, self::PERMS);

        $this->setStatus($admin, $order, 'processing')->assertSessionHas('success');
        $this->assertSame(['10.50', '2.25', '8.25'], $this->on($b, $v));

        $this->setStatus($admin, $order, 'shipped')->assertSessionHas('success');
        $this->assertSame(['8.25', '0.00', '8.25'], $this->on($b, $v));

        $this->setStatus($admin, $order, 'return')->assertSessionHas('success');
        $this->assertSame(['10.50', '0.00', '10.50'], $this->on($b, $v));

        $this->assertDatabaseHas('stock_movements', ['reason' => 'sale_shipment', 'change' => '-2.25']);
        $this->assertDatabaseHas('stock_movements', ['reason' => 'order_return', 'change' => '2.25']);
        $this->assertSame(3, $order->statusHistories()->count());
    }

    public function test_shipping_twice_does_not_deduct_twice(): void
    {
        $b = $this->makeBranch();
        $v = $this->makeVariant();
        $this->setInventory($b, $v, 10, 3);
        $order = $this->makeOrder($b, [[$v, 3]], 'processing');
        $admin = $this->makeAdmin($b, self::PERMS);

        $this->setStatus($admin, $order, 'shipped')->assertSessionHas('success');
        $this->setStatus($admin, $order, 'shipped')->assertSessionHas('error');

        $this->assertSame(['7.00', '0.00', '7.00'], $this->on($b, $v));
        $this->assertSame(1, \App\Models\StockMovement::where('reason', 'sale_shipment')->count());
    }

    public function test_invalid_transitions_are_rejected(): void
    {
        $b = $this->makeBranch();
        $v = $this->makeVariant();
        $this->setInventory($b, $v, 10, 2);
        $admin = $this->makeAdmin($b, self::PERMS);

        $pending = $this->makeOrder($b, [[$v, 2]], 'pending');
        $this->setStatus($admin, $pending, 'complete')->assertSessionHas('error');   // lompat
        $this->setStatus($admin, $pending, 'shipped')->assertSessionHas('error');    // belum diproses

        $shipped = $this->makeOrder($b, [[$v, 1]], 'shipped');
        $this->setStatus($admin, $shipped, 'cancelled')->assertSessionHas('error');  // sudah dikirim

        $this->assertSame(['10.00', '2.00', '8.00'], $this->on($b, $v));
        $this->assertSame('pending', $pending->fresh()->status);
    }

    public function test_cancel_releases_reservation_and_is_idempotent(): void
    {
        $b = $this->makeBranch();
        $v = $this->makeVariant();
        $this->setInventory($b, $v, 10, 4);
        $order = $this->makeOrder($b, [[$v, 4]], 'paid');
        $admin = $this->makeAdmin($b, self::PERMS);

        $this->setStatus($admin, $order, 'cancelled')->assertSessionHas('success');
        $this->setStatus($admin, $order, 'cancelled')->assertSessionHas('error');

        $this->assertSame(['10.00', '0.00', '10.00'], $this->on($b, $v));
        $this->assertNotNull($order->fresh()->cancelled_at);
    }

    public function test_ship_fails_atomically_when_stock_is_insufficient(): void
    {
        $b = $this->makeBranch();
        $v1 = $this->makeVariant('A');
        $v2 = $this->makeVariant('B');
        $this->setInventory($b, $v1, 10, 2);
        $this->setInventory($b, $v2, 1, 3);
        $order = $this->makeOrder($b, [[$v1, 2], [$v2, 3]], 'processing');
        $admin = $this->makeAdmin($b, self::PERMS);

        $this->setStatus($admin, $order, 'shipped')->assertSessionHas('error');

        $this->assertSame('processing', $order->fresh()->status);
        $this->assertSame(['10.00', '2.00', '8.00'], $this->on($b, $v1));   // rollback v1
        $this->assertSame(0, \App\Models\StockMovement::count());
    }

    public function test_paid_status_updates_payment_and_note_only_update_keeps_status(): void
    {
        $b = $this->makeBranch();
        $v = $this->makeVariant();
        $this->setInventory($b, $v, 5, 1);
        $order = $this->makeOrder($b, [[$v, 1]]);
        $admin = $this->makeAdmin($b, self::PERMS);

        $this->setStatus($admin, $order, 'paid');
        $this->assertSame('paid', $order->fresh()->payment_status);

        $this->setStatus($admin, $order, 'paid', 'Cek bukti transfer')->assertSessionHas('success');
        $this->assertStringContainsString('Cek bukti transfer', $order->fresh()->notes);
        $this->assertSame(1, $order->statusHistories()->count());
    }

    public function test_per_action_permissions_and_delete_rules(): void
    {
        $b = $this->makeBranch();
        $v = $this->makeVariant();
        $this->setInventory($b, $v, 5, 1);
        $limited = $this->makeAdmin($b, ['orders.view', 'orders.manage']);   // tanpa cancel/ship/delete
        $full = $this->makeAdmin($b, self::PERMS, 'full');

        $order = $this->makeOrder($b, [[$v, 1]], 'pending');
        $this->setStatus($limited, $order, 'cancelled')->assertSessionHas('error');
        $this->assertSame('pending', $order->fresh()->status);

        $this->actingAs($limited)->delete(route('admin.orders.destroy', $order->id))->assertSessionHas('error');
        $this->assertNotNull(\App\Models\Order::find($order->id));

        // hapus pending -> reservasi dilepas
        $this->actingAs($full)->delete(route('admin.orders.destroy', $order->id))->assertSessionHas('success');
        $this->assertNull(\App\Models\Order::find($order->id));
        $this->assertSame(['5.00', '0.00', '5.00'], $this->on($b, $v));

        // order yang sedang diproses tidak boleh dihapus
        $processing = $this->makeOrder($b, [[$v, 1]], 'processing');
        $this->actingAs($full)->delete(route('admin.orders.destroy', $processing->id))->assertSessionHas('error');
        $this->assertNotNull(\App\Models\Order::find($processing->id));
    }

    public function test_show_and_print_render(): void
    {
        $b = $this->makeBranch();
        $v = $this->makeVariant('KG-9', 'kg');
        $order = $this->makeOrder($b, [[$v, '1.5']]);
        $admin = $this->makeAdmin($b, self::PERMS);

        $this->actingAs($admin)->get(route('admin.orders.show', $order->id))->assertOk()->assertSee('1,5');
        $this->actingAs($admin)->get(route('admin.orders.print', $order->id))->assertOk()->assertSee($order->order_no);
        $this->actingAs($admin)->get(route('admin.orders.index'))->assertOk();
        $this->actingAs($admin)->get('/admin/orders/abc')->assertNotFound();
    }
}
