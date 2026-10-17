<?php

namespace Tests\Feature;

use App\Support\Qty;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\Support\StoreFixtures;
use Tests\TestCase;

class StockImportTest extends TestCase
{
    use RefreshDatabase, StoreFixtures;

    private const PERMS = ['stock_imports.create'];

    private function xlsx(array $rows, array $header = ['branch_code', 'variant_sku', 'new_stock', 'mode', 'note']): UploadedFile
    {
        $sheet = new Spreadsheet();
        $ws = $sheet->getActiveSheet();
        $ws->fromArray(array_merge([$header], $rows), null, 'A1', true);

        $path = tempnam(sys_get_temp_dir(), 'stk') . '.xlsx';
        (new Xlsx($sheet))->save($path);

        return new UploadedFile($path, 'stok.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true);
    }

    private function preview($user, UploadedFile $file)
    {
        return $this->actingAs($user)->post(route('stock_imports.preview'), ['excel' => $file]);
    }

    private function confirm($user, $response)
    {
        return $this->actingAs($user)->post(route('stock_imports.process'), ['token' => $response->viewData('token')]);
    }

    public function test_set_add_subtract_with_decimals_and_movements(): void
    {
        $b = $this->makeBranch('JKT');
        $v1 = $this->makeVariant('S1', 'kg');
        $v2 = $this->makeVariant('S2', 'kg');
        $v3 = $this->makeVariant('S3');
        $this->setInventory($b, $v2, '10.00');
        $this->setInventory($b, $v3, '5.00');
        $admin = $this->makeAdmin(null, self::PERMS);

        $preview = $this->preview($admin, $this->xlsx([
            ['JKT', 'S1', 12.25, 'set', 'opname'],
            ['jkt', 'S2', '2,5', 'add', ''],     // koma desimal + kode huruf kecil
            ['JKT', 'S3', 1.75, 'subtract', ''],
        ]))->assertOk();

        $this->assertSame(3, $preview->viewData('summary')['ok']);
        $this->assertSame([], $preview->viewData('fatalErrors'));
        // preview tidak mengubah data
        $this->assertNull($this->inventoryRow($b, $v1));

        $this->confirm($admin, $preview)->assertSessionHas('success');

        $this->assertSame('12.25', Qty::norm($this->inventoryRow($b, $v1)->on_hand));
        $this->assertSame('12.50', Qty::norm($this->inventoryRow($b, $v2)->on_hand));
        $this->assertSame('3.25', Qty::norm($this->inventoryRow($b, $v3)->on_hand));
        $this->assertSame('3.25', Qty::norm($this->inventoryRow($b, $v3)->available));

        $this->assertDatabaseHas('stock_movements', ['variant_id' => $v1->id, 'reason' => 'import_adjustment', 'change' => '12.25', 'resulting_on_hand' => '12.25', 'reference_type' => 'stock_import', 'performed_by' => $admin->id]);
        $this->assertDatabaseHas('stock_movements', ['variant_id' => $v3->id, 'change' => '-1.75']);
        $this->assertSame(1, \App\Models\StockMovement::distinct()->count('reference_id'));   // satu batch
    }

    public function test_any_error_blocks_the_whole_import(): void
    {
        $b = $this->makeBranch('JKT');
        $v = $this->makeVariant('S1');
        $this->setInventory($b, $v, 5, 2);
        $admin = $this->makeAdmin(null, self::PERMS);

        $preview = $this->preview($admin, $this->xlsx([
            ['JKT', 'S1', 9, 'set', ''],
            ['JKT', 'NOPE', 1, 'set', ''],      // SKU tidak ada
            ['XXX', 'S1', 1, 'set', ''],        // cabang tidak ada
            ['JKT', 'S1', 1.234, 'set', ''],    // 3 desimal
            ['JKT', 'S1', 1, 'wipe', ''],       // mode salah
        ]))->assertOk();

        $this->assertSame(4, $preview->viewData('summary')['error']);
        $this->assertNotEmpty($preview->viewData('fatalErrors'));

        $this->confirm($admin, $preview)->assertSessionHas('error');
        $this->assertSame('5.00', Qty::norm($this->inventoryRow($b, $v)->on_hand));
        $this->assertSame(0, \App\Models\StockMovement::count());
    }

    public function test_negative_result_and_below_reserved_are_rejected(): void
    {
        $b = $this->makeBranch('JKT');
        $v = $this->makeVariant('S1');
        $this->setInventory($b, $v, 5, 3);
        $admin = $this->makeAdmin(null, self::PERMS);

        $neg = $this->preview($admin, $this->xlsx([['JKT', 'S1', 6, 'subtract', '']]));
        $this->assertSame(1, $neg->viewData('summary')['error']);

        $reserved = $this->preview($admin, $this->xlsx([['JKT', 'S1', 2, 'set', '']]));   // 2 < reserved 3
        $this->assertSame(1, $reserved->viewData('summary')['error']);
    }

    public function test_duplicates_unchanged_and_missing_columns(): void
    {
        $b = $this->makeBranch('JKT');
        $v = $this->makeVariant('S1');
        $this->setInventory($b, $v, 5);
        $admin = $this->makeAdmin(null, self::PERMS);

        $dup = $this->preview($admin, $this->xlsx([['JKT', 'S1', 6, 'set', ''], ['JKT', 'S1', 7, 'set', '']]));
        $this->assertSame(1, $dup->viewData('summary')['error']);

        $same = $this->preview($admin, $this->xlsx([['JKT', 'S1', 5, 'set', '']]));
        $this->assertSame(1, $same->viewData('summary')['unchanged']);
        $this->assertNotEmpty($same->viewData('fatalErrors'));   // tidak ada perubahan

        $noCol = $this->preview($admin, $this->xlsx([['S1']], ['variant_sku']));
        $this->assertNotEmpty($noCol->viewData('fatalErrors'));
    }

    public function test_branch_admin_is_restricted_to_own_branch(): void
    {
        $a = $this->makeBranch('AAA');
        $b = $this->makeBranch('BBB');
        $v = $this->makeVariant('S1');
        $admin = $this->makeAdmin($a, self::PERMS);

        $other = $this->preview($admin, $this->xlsx([['BBB', 'S1', 5, 'set', '']]));
        $this->assertSame(1, $other->viewData('summary')['error']);

        // branch_code kosong -> otomatis cabang sendiri
        $own = $this->preview($admin, $this->xlsx([['', 'S1', 5, 'set', '']]));
        $this->assertSame(1, $own->viewData('summary')['ok']);
        $this->confirm($admin, $own)->assertSessionHas('success');
        $this->assertSame('5.00', Qty::norm($this->inventoryRow($a, $v)->on_hand));
        $this->assertNull($this->inventoryRow($b, $v));
    }

    public function test_token_cannot_be_used_by_another_user_and_requires_permission(): void
    {
        $b = $this->makeBranch('JKT');
        $this->makeVariant('S1');
        $admin = $this->makeAdmin(null, self::PERMS, 'imp');
        $intruder = $this->makeAdmin(null, self::PERMS, 'imp2');
        $nobody = $this->makeAdmin(null, ['orders.view'], 'noimp');

        $preview = $this->preview($admin, $this->xlsx([['JKT', 'S1', 5, 'set', '']]));
        $this->confirm($intruder, $preview)->assertSessionHas('error');

        $this->actingAs($nobody)->get(route('stock_imports.form'))->assertForbidden();
        $this->actingAs($admin)->get(route('stock_imports.form'))->assertOk();
    }

    public function test_template_and_snapshot_export_download(): void
    {
        $b = $this->makeBranch('JKT');
        $v = $this->makeVariant('S1', 'kg');
        $this->setInventory($b, $v, '3.50');
        $admin = $this->makeAdmin(null, self::PERMS);

        $this->actingAs($admin)->get(route('stock_imports.template'))->assertOk();
        $this->actingAs($admin)->get(route('stock_imports.export'))->assertOk();
    }
}
