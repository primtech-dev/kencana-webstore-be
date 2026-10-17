<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\StockImportLog;
use App\Support\Qty;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\Support\StoreFixtures;
use Tests\TestCase;

class OdooStockImportTest extends TestCase
{
    use RefreshDatabase, StoreFixtures;

    private const PERMS = ['stock_imports.create'];
    private const HEADER = ['Location', 'Warehouse', 'Product', 'Product Category', 'Available Quantity', 'Unit of Measure'];

    /** @param list<array{0:string,1:string,2:string,3:mixed}> $rows [location, warehouse, product, qty] */
    private function odoo(array $rows): UploadedFile
    {
        $sheet = new Spreadsheet();
        $out = [self::HEADER];
        foreach ($rows as [$loc, $wh, $prod, $qty]) {
            $out[] = [$loc, $wh, $prod, 'TOOLS / OTHERS', $qty, 'Units'];
        }
        $sheet->getActiveSheet()->fromArray($out, null, 'A1', true);

        $path = tempnam(sys_get_temp_dir(), 'odoo') . '.xlsx';
        (new Xlsx($sheet))->save($path);

        return new UploadedFile($path, 'Quants (stock.quant).xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true);
    }

    private function preview($user, UploadedFile $file, bool $zeroMissing = true)
    {
        return $this->actingAs($user)->post(route('stock_imports.preview'), ['excel' => $file, 'zero_missing' => $zeroMissing ? 1 : 0]);
    }

    private function confirm($user, $response)
    {
        return $this->actingAs($user)->post(route('stock_imports.process'), ['token' => $response->viewData('token')]);
    }

    private function on($branch, $variant): ?string
    {
        $r = $this->inventoryRow($branch, $variant);

        return $r ? Qty::norm($r->on_hand) : null;
    }

    public function test_sets_stock_per_branch_only_from_stock_locations(): void
    {
        $gresik = $this->makeBranch('02');
        $gresik->update(['name' => 'Kencana Store GRESIK_KEBOMAS']);
        $v1 = $this->makeVariant('100103010003');
        $v2 = $this->makeVariant('500301010009');
        $v3 = $this->makeVariant('200104030056');
        $this->setInventory($gresik, $v1, 99);
        $admin = $this->makeAdmin(null, self::PERMS);

        $preview = $this->preview($admin, $this->odoo([
            ['GD002/Stock', 'Kencana Store GRESIK_KEBOMAS', '[100103010003] ATAP UPVC', 2],
            ['GD002/Stock', 'Kencana Store GRESIK_KEBOMAS', '[500301010009] BAUT DYNABOLT', 35.57],
            ['GD002/Scrap', 'Kencana Store GRESIK_KEBOMAS', '[100103010003] ATAP UPVC', 50],   // bukan /Stock
            ['GD002/Tinting', 'Kencana Store GRESIK_KEBOMAS', '[200104030056] COLORANT', 7],     // bukan /Stock
        ]))->assertOk();

        $this->assertSame(2, $preview->viewData('summary')['ok']);
        $this->assertSame(2, $preview->viewData('summary')['non_stock']);
        $this->assertSame([], $preview->viewData('fatalErrors'));
        $this->assertSame('99.00', $this->on($gresik, $v1));   // preview tidak mengubah apa pun

        $this->confirm($admin, $preview)->assertSessionHas('success');

        $this->assertSame('2.00', $this->on($gresik, $v1));
        $this->assertSame('35.57', $this->on($gresik, $v2));
        $this->assertNull($this->inventoryRow($gresik, $v3));
        $this->assertDatabaseHas('stock_movements', ['variant_id' => $v1->id, 'reason' => 'import_adjustment', 'change' => '-97.00', 'reference_type' => 'stock_import']);

        $log = StockImportLog::firstOrFail();
        $this->assertSame('odoo', $log->source);
        $this->assertSame(2, $log->rows_changed);
        $this->assertSame($admin->id, $log->user_id);
    }

    public function test_skips_unknown_warehouse_unknown_sku_and_rows_without_code(): void
    {
        $b = $this->makeBranch('02');
        $b->update(['name' => 'Kencana Store GRESIK_KEBOMAS']);
        $v = $this->makeVariant('100103010003');
        $admin = $this->makeAdmin(null, self::PERMS);

        $preview = $this->preview($admin, $this->odoo([
            ['GD002/Stock', 'Kencana Store GRESIK_KEBOMAS', '[100103010003] ATAP', 4],
            ['GD099/Stock', 'Kencana Store GRESIK_MENGANTI', '[100103010003] ATAP', 9],           // gudang tak dikenal
            ['GD002/Stock', 'Kencana Store GRESIK_KEBOMAS', '[999999999999] PRODUK ASING', 3],     // SKU tak ada
            ['GD002/Stock', 'Kencana Store GRESIK_KEBOMAS', 'NASI KOTAK', 60],                     // tanpa kode
        ]))->assertOk();

        $this->assertSame([], $preview->viewData('fatalErrors'));
        $this->assertSame(1, $preview->viewData('summary')['ok']);
        $this->assertSame(3, $preview->viewData('summary')['skipped']);
        $titles = collect($preview->viewData('warnings'))->pluck('title')->implode('|');
        $this->assertStringContainsString('Gudang tidak dikenal', $titles);
        $this->assertStringContainsString('Kode produk tidak ditemukan', $titles);
        $this->assertStringContainsString('tanpa kode', $titles);

        $this->confirm($admin, $preview)->assertSessionHas('success');
        $this->assertSame('4.00', $this->on($b, $v));
    }

    public function test_negative_becomes_zero_and_duplicates_in_stock_are_summed(): void
    {
        $b = $this->makeBranch('02');
        $b->update(['name' => 'Kencana Store GRESIK_KEBOMAS']);
        $neg = $this->makeVariant('200104030056');
        $dup = $this->makeVariant('100103010003');
        $this->setInventory($b, $neg, 5);
        $admin = $this->makeAdmin(null, self::PERMS);

        $preview = $this->preview($admin, $this->odoo([
            ['GD002/Stock', 'Kencana Store GRESIK_KEBOMAS', '[200104030056] COLORANT', -0.03],
            ['GD002/Stock', 'Kencana Store GRESIK_KEBOMAS', '[100103010003] ATAP (lot A)', 3],
            ['GD002/Stock', 'Kencana Store GRESIK_KEBOMAS', '[100103010003] ATAP (lot B)', 4.5],
        ]));
        $this->assertSame(1, $preview->viewData('summary')['duplicates']);

        $this->confirm($admin, $preview)->assertSessionHas('success');
        $this->assertSame('0.00', $this->on($b, $neg));
        $this->assertSame('7.50', $this->on($b, $dup));
    }

    public function test_missing_products_are_zeroed_only_for_branches_in_file_and_only_when_checked(): void
    {
        $a = $this->makeBranch('02');
        $a->update(['name' => 'Kencana Store GRESIK_KEBOMAS']);
        $b = $this->makeBranch('05');
        $b->update(['name' => 'Kencana Store MALANG_BURING']);
        $inFile = $this->makeVariant('111111111111');
        $missing = $this->makeVariant('222222222222');
        $this->setInventory($a, $inFile, 1);
        $this->setInventory($a, $missing, 8);
        $this->setInventory($b, $missing, 6);          // cabang B tidak ada di file
        $admin = $this->makeAdmin(null, self::PERMS);
        $file = fn () => $this->odoo([['GD002/Stock', 'Kencana Store GRESIK_KEBOMAS', '[111111111111] A', 5]]);

        // tanpa centang: dibiarkan
        $p = $this->preview($admin, $file(), zeroMissing: false);
        $this->assertSame(0, $p->viewData('summary')['zeroed']);
        $this->confirm($admin, $p);
        $this->assertSame('8.00', $this->on($a, $missing));

        // dengan centang: dinolkan hanya di cabang A
        $p = $this->preview($admin, $file(), zeroMissing: true);
        $this->assertSame(1, $p->viewData('summary')['zeroed']);
        $this->confirm($admin, $p)->assertSessionHas('success');

        $this->assertSame('5.00', $this->on($a, $inFile));
        $this->assertSame('0.00', $this->on($a, $missing));
        $this->assertSame('6.00', $this->on($b, $missing));
        $this->assertDatabaseHas('stock_movements', ['variant_id' => $missing->id, 'branch_id' => $a->id, 'change' => '-8.00']);
    }

    public function test_stock_below_reserved_is_allowed_and_available_is_clamped(): void
    {
        $b = $this->makeBranch('02');
        $b->update(['name' => 'Kencana Store GRESIK_KEBOMAS']);
        $v = $this->makeVariant('100103010003');
        $this->setInventory($b, $v, 10, 6);
        $admin = $this->makeAdmin(null, self::PERMS);

        $preview = $this->preview($admin, $this->odoo([['GD002/Stock', 'Kencana Store GRESIK_KEBOMAS', '[100103010003] ATAP', 2]]));
        $this->assertSame([], $preview->viewData('fatalErrors'));
        $this->assertNotEmpty(collect($preview->viewData('warnings'))->firstWhere('title', 'Stok di bawah stok reserved pesanan (available jadi 0)'));

        $this->confirm($admin, $preview)->assertSessionHas('success');
        $row = $this->inventoryRow($b, $v);
        $this->assertSame('2.00', Qty::norm($row->on_hand));
        $this->assertSame('6.00', Qty::norm($row->reserved));
        $this->assertSame('0.00', Qty::norm($row->available));
    }

    public function test_falls_back_to_product_sku_with_single_variant(): void
    {
        $b = $this->makeBranch('02');
        $b->update(['name' => 'Kencana Store GRESIK_KEBOMAS']);
        $variant = $this->makeVariant('VAR-X');
        Product::whereKey($variant->product_id)->update(['sku' => '555555555555']);
        $admin = $this->makeAdmin(null, self::PERMS);

        $preview = $this->preview($admin, $this->odoo([['GD002/Stock', 'Kencana Store GRESIK_KEBOMAS', '[555555555555] PRODUK', 12]]));
        $this->assertSame(1, $preview->viewData('summary')['ok']);
        $this->confirm($admin, $preview);
        $this->assertSame('12.00', $this->on($b, $variant));
    }

    public function test_nothing_matching_is_blocked_and_never_zeroes_stock(): void
    {
        $b = $this->makeBranch('02');
        $b->update(['name' => 'Kencana Store GRESIK_KEBOMAS']);
        $v = $this->makeVariant('100103010003');
        $this->setInventory($b, $v, 9);
        $admin = $this->makeAdmin(null, self::PERMS);

        $preview = $this->preview($admin, $this->odoo([['GD002/Stock', 'Kencana Store GRESIK_KEBOMAS', '[999999999999] ASING', 3]]), zeroMissing: true);
        $this->assertNotEmpty($preview->viewData('fatalErrors'));

        $this->confirm($admin, $preview)->assertSessionHas('error');
        $this->assertSame('9.00', $this->on($b, $v));
    }

    public function test_branch_user_only_affects_own_branch(): void
    {
        $a = $this->makeBranch('02');
        $a->update(['name' => 'Kencana Store GRESIK_KEBOMAS']);
        $b = $this->makeBranch('05');
        $b->update(['name' => 'Kencana Store MALANG_BURING']);
        $v = $this->makeVariant('100103010003');
        $this->setInventory($b, $v, 3);
        $admin = $this->makeAdmin($a, self::PERMS);

        $preview = $this->preview($admin, $this->odoo([
            ['GD002/Stock', 'Kencana Store GRESIK_KEBOMAS', '[100103010003] ATAP', 4],
            ['GD005/Stock', 'Kencana Store MALANG_BURING', '[100103010003] ATAP', 100],
        ]));
        $this->assertSame(1, $preview->viewData('summary')['ok']);
        $this->assertNotEmpty(collect($preview->viewData('warnings'))->firstWhere('title', 'Gudang di luar hak akses Anda (baris dilewati)'));

        $this->confirm($admin, $preview)->assertSessionHas('success');
        $this->assertSame('4.00', $this->on($a, $v));
        $this->assertSame('3.00', $this->on($b, $v));
    }

    public function test_import_form_lists_recent_imports(): void
    {
        $admin = $this->makeAdmin(null, self::PERMS);
        StockImportLog::create(['batch_id' => (string) \Illuminate\Support\Str::uuid(), 'user_id' => $admin->id, 'source' => 'odoo', 'file_name' => 'x.xlsx', 'rows_changed' => 1234, 'created_at' => now()]);

        $this->actingAs($admin)->get(route('stock_imports.form'))->assertOk()->assertSee('1.234')->assertSee('Odoo');
    }

    public function test_history_list_and_detail_are_scoped_by_branch(): void
    {
        $a = $this->makeBranch('02');
        $a->update(['name' => 'Kencana Store GRESIK_KEBOMAS']);
        $b = $this->makeBranch('05');
        $b->update(['name' => 'Kencana Store MALANG_BURING']);
        $v = $this->makeVariant('100103010003');
        $perms = ['stock_imports.create', 'inventory.view'];
        $pusat = $this->makeAdmin(null, $perms, 'pusat');
        $adminA = $this->makeAdmin($a, $perms, 'cabang_a');
        $adminB = $this->makeAdmin($b, $perms, 'cabang_b');

        // import 1: dua cabang oleh pusat; import 2: hanya cabang B oleh admin B
        $this->confirm($pusat, $this->preview($pusat, $this->odoo([
            ['GD002/Stock', 'Kencana Store GRESIK_KEBOMAS', '[100103010003] ATAP', 4],
            ['GD005/Stock', 'Kencana Store MALANG_BURING', '[100103010003] ATAP', 6],
        ])))->assertSessionHas('success');
        $this->confirm($adminB, $this->preview($adminB, $this->odoo([
            ['GD005/Stock', 'Kencana Store MALANG_BURING', '[100103010003] ATAP', 9],
        ])))->assertSessionHas('success');
        $this->assertSame(2, StockImportLog::count());

        $first = StockImportLog::orderBy('id')->first();
        $second = StockImportLog::orderBy('id')->skip(1)->first();
        $cols = ['DT_RowIndex', 'created_at', 'user', 'source', 'file_name', 'branch_list', 'rows_changed', 'rows_zeroed', 'rows_skipped', 'action'];
        $list = fn ($u, string $q = '') => $this->actingAs($u)->ajax()->get(route('stock_imports.history', $this->dtParams($cols, $q, ['order' => [['column' => 1, 'dir' => 'desc']]])))->assertOk();

        $this->assertSame(2, $list($pusat)->json('recordsFiltered'));
        $this->assertSame(1, $list($adminA)->json('recordsFiltered'));          // hanya import yang menyentuh Gresik
        $this->assertSame(2, $list($adminB)->json('recordsFiltered'));
        // pencarian dengan request DataTables sungguhan: satu/multi-kata tidak error, hasil sesuai
        $this->assertSame(2, $list($pusat, 'quants')->json('recordsFiltered'));
        $this->assertSame(2, $list($pusat, 'quants xlsx')->json('recordsFiltered'));
        $this->assertSame(0, $list($pusat, 'tidak ada')->json('recordsFiltered'));
        $this->assertNull($list($pusat, 'quants xlsx')->json('error'));

        $row = collect($list($pusat)->json('data'))->first();
        $this->assertStringContainsString('Odoo', $row['source']);
        $this->assertStringContainsString(route('stock_imports.show', $second->batch_id), $row['action']);

        // detail
        $this->flushHeaders();
        $this->actingAs($pusat)->get(route('stock_imports.show', $first->batch_id))->assertOk()->assertSee('Quants (stock.quant).xlsx');
        $this->actingAs($adminA)->get(route('stock_imports.show', $first->batch_id))->assertOk();
        $this->actingAs($adminA)->get(route('stock_imports.show', $second->batch_id))->assertNotFound();   // import cabang lain
        $this->actingAs($pusat)->get('/stock-imports/history/bukan-uuid')->assertNotFound();

        // perubahan stok per batch (riwayat stok difilter batch_id) dan dibatasi cabang
        $mv = fn ($u) => $this->actingAs($u)->ajax()->get(route('inventory.movements', ['batch_id' => $first->batch_id]))->assertOk()->json('recordsFiltered');
        $this->assertSame(2, $mv($pusat));
        $this->assertSame(1, $mv($adminA));
    }
}
