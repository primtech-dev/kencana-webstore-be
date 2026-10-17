<?php

namespace Tests\Feature;

use App\Support\Qty;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\StoreFixtures;
use Tests\TestCase;

class DecimalStockReceiptTest extends TestCase
{
    use RefreshDatabase, StoreFixtures;

    private const PERMS = ['stock_receipts.view', 'stock_receipts.create', 'stock_receipts.update', 'stock_receipts.delete'];

    private function payload($branch, $variant, $qty, string $status = 'received'): array
    {
        return [
            'branch_id' => $branch->id,
            'status' => $status,
            'items' => [['variant_id' => $variant->id, 'quantity_received' => $qty]],
        ];
    }

    public function test_decimal_receipt_adds_stock_with_two_digits(): void
    {
        $b = $this->makeBranch();
        $v = $this->makeVariant('KG-1', 'kg');
        $admin = $this->makeAdmin(null, self::PERMS);

        $this->actingAs($admin)->post(route('stock_receipts.store'), $this->payload($b, $v, '1.25'))->assertSessionHasNoErrors();
        $this->actingAs($admin)->post(route('stock_receipts.store'), $this->payload($b, $v, '2.50'))->assertSessionHasNoErrors();

        $row = $this->inventoryRow($b, $v);
        $this->assertSame('3.75', Qty::norm($row->on_hand));
        $this->assertSame('3.75', Qty::norm($row->available));
        $this->assertDatabaseHas('stock_movements', ['variant_id' => $v->id, 'branch_id' => $b->id, 'change' => '1.25', 'resulting_on_hand' => '1.25', 'reason' => 'receipt']);
    }

    public function test_more_than_two_decimals_or_zero_is_rejected(): void
    {
        $b = $this->makeBranch();
        $v = $this->makeVariant();
        $admin = $this->makeAdmin(null, self::PERMS);

        $this->actingAs($admin)->post(route('stock_receipts.store'), $this->payload($b, $v, '1.255'))->assertSessionHasErrors('items.0.quantity_received');
        $this->actingAs($admin)->post(route('stock_receipts.store'), $this->payload($b, $v, '0'))->assertSessionHasErrors('items.0.quantity_received');
        $this->assertNull($this->inventoryRow($b, $v));
    }

    public function test_update_applies_decimal_difference_and_delete_reverses(): void
    {
        $b = $this->makeBranch();
        $v = $this->makeVariant('KG-2', 'kg');
        $admin = $this->makeAdmin(null, self::PERMS);

        $this->actingAs($admin)->post(route('stock_receipts.store'), $this->payload($b, $v, '5.10'));
        $receipt = \App\Models\StockReceipt::firstOrFail();

        $this->actingAs($admin)->put(route('stock_receipts.update', $receipt->id), $this->payload($b, $v, '7.35'))->assertSessionHasNoErrors();
        $this->assertSame('7.35', Qty::norm($this->inventoryRow($b, $v)->on_hand));

        $this->actingAs($admin)->put(route('stock_receipts.update', $receipt->id), $this->payload($b, $v, '2.05'))->assertSessionHasNoErrors();
        $this->assertSame('2.05', Qty::norm($this->inventoryRow($b, $v)->on_hand));

        $this->actingAs($admin)->delete(route('stock_receipts.destroy', $receipt->id))->assertSessionHasNoErrors();
        $this->assertSame('0.00', Qty::norm($this->inventoryRow($b, $v)->on_hand));
    }

    public function test_moving_received_receipt_to_other_branch_moves_stock(): void
    {
        $a = $this->makeBranch('A');
        $b = $this->makeBranch('B');
        $v = $this->makeVariant();
        $admin = $this->makeAdmin(null, self::PERMS);

        $this->actingAs($admin)->post(route('stock_receipts.store'), $this->payload($a, $v, '4.00'));
        $receipt = \App\Models\StockReceipt::firstOrFail();

        $this->actingAs($admin)->put(route('stock_receipts.update', $receipt->id), $this->payload($b, $v, '4.00'))->assertSessionHasNoErrors();

        $this->assertSame('0.00', Qty::norm($this->inventoryRow($a, $v)->on_hand));
        $this->assertSame('4.00', Qty::norm($this->inventoryRow($b, $v)->on_hand));
    }

    public function test_delete_is_blocked_when_stock_already_consumed(): void
    {
        $b = $this->makeBranch();
        $v = $this->makeVariant();
        $admin = $this->makeAdmin(null, self::PERMS);

        $this->actingAs($admin)->post(route('stock_receipts.store'), $this->payload($b, $v, '3.00'));
        $receipt = \App\Models\StockReceipt::firstOrFail();
        $this->setInventory($b, $v, 1);

        $this->actingAs($admin)->delete(route('stock_receipts.destroy', $receipt->id))->assertSessionHas('error');
        $this->assertNull(\App\Models\StockReceipt::find($receipt->id)?->deleted_at);
        $this->assertSame('1.00', Qty::norm($this->inventoryRow($b, $v)->on_hand));
    }

    public function test_branch_admin_cannot_use_or_see_other_branch_receipts(): void
    {
        $a = $this->makeBranch('A');
        $b = $this->makeBranch('B');
        $v = $this->makeVariant();
        $pusat = $this->makeAdmin(null, self::PERMS, 'pusat');
        $admin = $this->makeAdmin($a, self::PERMS, 'cabang');

        $this->actingAs($pusat)->post(route('stock_receipts.store'), $this->payload($b, $v, '1.00'));
        $receiptB = \App\Models\StockReceipt::firstOrFail();

        $this->actingAs($admin)->get(route('stock_receipts.show', $receiptB->id))->assertNotFound();
        $this->actingAs($admin)->delete(route('stock_receipts.destroy', $receiptB->id))->assertNotFound();
        $this->actingAs($admin)->post(route('stock_receipts.store'), $this->payload($b, $v, '9.00'))->assertForbidden();
        $this->assertSame('1.00', Qty::norm($this->inventoryRow($b, $v)->on_hand));
    }
}
