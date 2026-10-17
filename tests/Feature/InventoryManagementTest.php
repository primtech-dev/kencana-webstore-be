<?php

namespace Tests\Feature;

use App\Models\StockMovement;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\Support\StoreFixtures;
use Tests\TestCase;

class InventoryManagementTest extends TestCase
{
    use RefreshDatabase, StoreFixtures;

    private const PERMS = ['inventory.view', 'orders.view', 'stock_receipts.view'];

    private function movement($branch, $variant, $change, $after, string $reason, ?string $refType = null, ?string $refId = null, ?int $by = null, array $meta = []): StockMovement
    {
        return StockMovement::create([
            'variant_id' => $variant->id, 'branch_id' => $branch->id, 'change' => $change, 'resulting_on_hand' => $after,
            'reason' => $reason, 'reference_type' => $refType, 'reference_id' => $refId, 'performed_by' => $by,
            'metadata' => $meta ?: null, 'created_at' => now(),
        ]);
    }

    private function stockRows($response): array
    {
        return collect($response->json('data'))->map(fn ($r) => $r['branch_name'] . ':' . $r['sku'])->sort()->values()->all();
    }

    public function test_permission_is_required(): void
    {
        $nobody = $this->makeAdmin(null, ['orders.view']);

        $this->actingAs($nobody)->get(route('inventory.index'))->assertForbidden();
        $this->actingAs($nobody)->get(route('inventory.movements'))->assertForbidden();
    }

    public function test_pusat_sees_all_branches_and_can_filter_branch_and_status(): void
    {
        $a = $this->makeBranch('A');
        $b = $this->makeBranch('B');
        $v1 = $this->makeVariant('SKU-1');
        $v2 = $this->makeVariant('SKU-2');
        $this->setInventory($a, $v1, '10.50', 2);
        $this->setInventory($b, $v1, 0);
        $this->setInventory($b, $v2, 5, 5);
        $pusat = $this->makeAdmin(null, self::PERMS);

        $all = $this->actingAs($pusat)->ajax()->get(route('inventory.index'))->assertOk();
        $this->assertSame(['Cabang A:SKU-1', 'Cabang B:SKU-1', 'Cabang B:SKU-2'], $this->stockRows($all));
        $row = collect($all->json('data'))->firstWhere('sku', 'SKU-1');
        $this->assertTrue(in_array('10,50', array_column($all->json('data'), 'on_hand'), true));

        $byBranch = $this->actingAs($pusat)->ajax()->get(route('inventory.index', ['branch_id' => $a->id]));
        $this->assertSame(['Cabang A:SKU-1'], $this->stockRows($byBranch));

        $habis = $this->actingAs($pusat)->ajax()->get(route('inventory.index', ['status' => 'habis']));
        $this->assertSame(['Cabang B:SKU-1'], $this->stockRows($habis));

        $tersedia = $this->actingAs($pusat)->ajax()->get(route('inventory.index', ['status' => 'ada']));
        $this->assertSame(['Cabang A:SKU-1'], $this->stockRows($tersedia));

        $reserved = $this->actingAs($pusat)->ajax()->get(route('inventory.index', ['status' => 'reserved']));
        $this->assertSame(['Cabang A:SKU-1', 'Cabang B:SKU-2'], $this->stockRows($reserved));

        $search = $this->actingAs($pusat)->ajax()->get(route('inventory.index', ['search' => ['value' => 'sku-2']]));
        $this->assertSame(['Cabang B:SKU-2'], $this->stockRows($search));

        // request DataTables sungguhan: daftar kolom + pencarian multi-kata (regresi error "i.branch_name does not exist")
        $cols = ['DT_RowIndex', 'branch_name', 'sku', 'product', 'unit_name', 'on_hand', 'reserved', 'available', 'updated_at', 'action'];
        $dt = fn (string $q, array $extra = []) => $this->actingAs($pusat)->ajax()->get(route('inventory.index', $this->dtParams($cols, $q, $extra)))->assertOk();

        $this->assertSame(['Cabang B:SKU-2'], $this->stockRows($dt('produk sku-2')));
        $this->assertSame(['Cabang A:SKU-1', 'Cabang B:SKU-1'], $this->stockRows($dt('varian sku-1')));
        $this->assertSame([], $this->stockRows($dt('sku-1 tidak-ada')));
        $this->assertNull($dt('sku')->json('error'));
        $this->assertSame(['Cabang B:SKU-1'], $this->stockRows($dt('sku-1', ['branch_id' => $b->id])));
    }

    public function test_branch_user_only_sees_own_branch_even_if_requesting_another(): void
    {
        $a = $this->makeBranch('A');
        $b = $this->makeBranch('B');
        $v = $this->makeVariant('SKU-1');
        $this->setInventory($a, $v, 3);
        $this->setInventory($b, $v, 9);
        $admin = $this->makeAdmin($a, self::PERMS);

        $res = $this->actingAs($admin)->ajax()->get(route('inventory.index', ['branch_id' => $b->id]))->assertOk();
        $this->assertSame(['Cabang A:SKU-1'], $this->stockRows($res));
    }

    public function test_movements_history_is_scoped_filterable_and_formatted(): void
    {
        $a = $this->makeBranch('A');
        $b = $this->makeBranch('B');
        $v1 = $this->makeVariant('SKU-1');
        $v2 = $this->makeVariant('SKU-2');
        $pusat = $this->makeAdmin(null, self::PERMS, 'pusat');
        $adminA = $this->makeAdmin($a, self::PERMS, 'cabang');

        $order = $this->makeOrder($a, [[$v1, 2]]);
        $this->movement($a, $v1, '10.50', '10.50', 'receipt', null, null, $pusat->id, ['note' => 'Stock received']);
        $this->movement($a, $v1, '-2.00', '8.50', 'sale_shipment', 'order', $order->public_id, $adminA->id);
        $this->movement($b, $v1, '5.00', '5.00', 'import_adjustment', 'stock_import', (string) Str::uuid(), $pusat->id, ['file' => 'Quants.xlsx']);
        $this->movement($b, $v2, '1.00', '1.00', 'receipt');

        $rows = fn ($res) => collect($res->json('data'));

        // pusat: semua
        $all = $this->actingAs($pusat)->ajax()->get(route('inventory.movements'))->assertOk();
        $this->assertSame(4, $all->json('recordsTotal'));

        // pusat: pilih cabang & varian
        $onlyB = $this->actingAs($pusat)->ajax()->get(route('inventory.movements', ['branch_id' => $b->id, 'variant_id' => $v1->id]));
        $this->assertSame(1, $onlyB->json('recordsFiltered'));
        $this->assertStringContainsString('Quants.xlsx', $rows($onlyB)->first()['note']);
        $this->assertStringContainsString('Import stok', $rows($onlyB)->first()['reason']);

        // jenis perubahan
        $ship = $this->actingAs($pusat)->ajax()->get(route('inventory.movements', ['reason' => 'shipment']));
        $this->assertSame(1, $ship->json('recordsFiltered'));
        $first = $rows($ship)->first();
        $this->assertStringContainsString('-2</span>', $first['change']);
        $this->assertStringContainsString('text-danger', $first['change']);
        $this->assertStringContainsString($order->order_no, $first['reference']);
        $this->assertSame($adminA->name, $first['performer']);

        // penambahan tampil +
        $recv = $this->actingAs($pusat)->ajax()->get(route('inventory.movements', ['reason' => 'receipt', 'branch_id' => $a->id]));
        $this->assertStringContainsString('+10,50', $rows($recv)->first()['change']);

        // admin cabang: hanya cabangnya, abaikan branch_id cabang lain
        $own = $this->actingAs($adminA)->ajax()->get(route('inventory.movements', ['branch_id' => $b->id]));
        $this->assertSame(2, $own->json('recordsFiltered'));
        $this->assertSame(['Cabang A'], $rows($own)->pluck('branch_name')->unique()->values()->all());

        // pencarian nomor order & tanggal
        $byOrder = $this->actingAs($pusat)->ajax()->get(route('inventory.movements', ['search' => ['value' => strtolower($order->order_no)]]));
        $this->assertSame(1, $byOrder->json('recordsFiltered'));

        // request DataTables sungguhan (daftar kolom + multi-kata)
        $cols = ['created_at', 'branch_name', 'product', 'change', 'resulting_on_hand', 'reason', 'reference', 'performer', 'note'];
        $multi = $this->actingAs($pusat)->ajax()->get(route('inventory.movements', $this->dtParams($cols, 'produk sku-1')))->assertOk();
        $this->assertNull($multi->json('error'));
        $this->assertSame(3, $multi->json('recordsFiltered'));
        $future = $this->actingAs($pusat)->ajax()->get(route('inventory.movements', ['date_from' => now()->addDay()->toDateString()]));
        $this->assertSame(0, $future->json('recordsFiltered'));
    }

    public function test_variant_page_shows_per_branch_stock_for_pusat_and_only_own_for_branch_admin(): void
    {
        $a = $this->makeBranch('A');
        $b = $this->makeBranch('B');
        $v = $this->makeVariant('SKU-1', 'kg');
        $this->setInventory($a, $v, '4.25', 1);
        $this->setInventory($b, $v, 6);
        $pusat = $this->makeAdmin(null, self::PERMS, 'pusat');
        $adminA = $this->makeAdmin($a, self::PERMS, 'cabang');

        $this->actingAs($pusat)->get(route('inventory.variant', [$v->id, 'branch_id' => $b->id]))
            ->assertOk()->assertSee('Cabang A')->assertSee('Cabang B')->assertSee('4,25')->assertSee('Total semua cabang');

        $this->actingAs($adminA)->get(route('inventory.variant', $v->id))
            ->assertOk()->assertSee('Cabang A')->assertDontSee('Cabang B')->assertDontSee('Total semua cabang');

        $this->actingAs($pusat)->get(route('inventory.variant', 99999))->assertNotFound();
    }

    public function test_pages_render(): void
    {
        $a = $this->makeBranch('A');
        $pusat = $this->makeAdmin(null, self::PERMS);

        $this->actingAs($pusat)->get(route('inventory.index'))->assertOk()->assertSee('Stok Saat Ini');
        $this->actingAs($pusat)->get(route('inventory.movements'))->assertOk()->assertSee('Riwayat Stok');
    }
}
