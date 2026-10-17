<?php

namespace App\Http\Controllers;

use App\Exceptions\InventoryException;
use App\Exports\StockSnapshotExport;
use App\Exports\StockUpdateImportTemplateExport;
use App\Models\Branch;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\StockImportLog;
use App\Models\User;
use App\Services\InventoryService;
use App\Support\Qty;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;

/**
 * Import update stok (Excel): form -> preview -> konfirmasi. Dua format, dikenali otomatis dari header:
 *
 *  1. Export Odoo "Quants (stock.quant)": Location, Warehouse, Product, Product Category,
 *     Available Quantity, Unit of Measure. Stok backoffice disamakan dengan Odoo.
 *  2. Template: branch_code, variant_sku, new_stock, mode (set|add|subtract), note.
 *
 * Seluruh file bersifat all-or-nothing: baris yang valid diterapkan dalam satu transaksi.
 */
class StockImportController extends Controller
{
    private const MAX_ROWS = 5000;
    private const MAX_ROWS_ODOO = 30000;
    private const MODES = ['set', 'add', 'subtract'];
    private const REQUIRED_COLUMNS = ['variant_sku', 'new_stock'];
    private const SESSION_KEY = 'stock_import';
    private const PREVIEW_LIMIT = 500;
    private const WARNING_ITEMS = 30;

    public function __construct(private InventoryService $inventory)
    {
    }

    public function form()
    {
        return view('stock-imports.import', [
            'logs' => StockImportLog::with('user:id,name')->latest('id')->limit(10)->get(),
        ]);
    }

    /** Daftar seluruh riwayat import stok. */
    public function history(Request $request)
    {
        $user = $request->user();

        if ($request->ajax()) {
            $filters = $request->validate([
                'source' => ['nullable', Rule::in(['odoo', 'template'])],
                'date_from' => ['nullable', 'date'],
                'date_to' => ['nullable', 'date'],
            ]);

            $q = $this->visibleLogs($user)->with('user:id,name')->select('stock_import_logs.*');

            if (! empty($filters['source'])) {
                $q->where('source', $filters['source']);
            }
            if (! empty($filters['date_from'])) {
                $q->where('created_at', '>=', $filters['date_from'] . ' 00:00:00');
            }
            if (! empty($filters['date_to'])) {
                $q->where('created_at', '<=', $filters['date_to'] . ' 23:59:59');
            }

            return datatables()->eloquent($q)
                ->addIndexColumn()
                ->filter(function ($query) use ($request) {
                    // setiap kata harus cocok di nama file, batch, atau nama pengguna
                    $words = preg_split('/\s+/', mb_strtolower(trim((string) $request->input('search.value'))), -1, PREG_SPLIT_NO_EMPTY);
                    foreach ($words as $word) {
                        $like = '%' . $word . '%';
                        $query->where(fn ($w) => $w->whereRaw('LOWER(file_name) LIKE ?', [$like])
                            ->orWhereRaw('CAST(batch_id AS TEXT) LIKE ?', [$like])
                            ->orWhereHas('user', fn ($u) => $u->whereRaw('LOWER(name) LIKE ?', [$like])));
                    }
                })
                ->editColumn('created_at', fn ($l) => $l->created_at?->format('d M Y H:i') ?? '-')
                ->addColumn('user', fn ($l) => $l->user->name ?? '-')
                ->editColumn('source', fn ($l) => '<span class="badge bg-' . ($l->source === 'odoo' ? 'primary' : 'secondary') . '">' . ($l->source === 'odoo' ? 'Odoo' : 'Template') . '</span>')
                ->addColumn('branch_list', function ($l) {
                    $codes = $l->branches ?? [];

                    return count($codes) > 4 ? count($codes) . ' cabang' : (implode(', ', $codes) ?: '-');
                })
                ->editColumn('rows_changed', fn ($l) => number_format($l->rows_changed, 0, ',', '.'))
                ->editColumn('rows_zeroed', fn ($l) => number_format($l->rows_zeroed, 0, ',', '.'))
                ->editColumn('rows_skipped', fn ($l) => number_format($l->rows_skipped, 0, ',', '.'))
                ->addColumn('action', fn ($l) => '<a class="btn btn-sm btn-outline-primary" href="' . route('stock_imports.show', $l->batch_id) . '">Detail</a>')
                ->orderColumn('created_at', 'created_at $1, id $1')
                ->rawColumns(['source', 'action'])
                ->toJson();
        }

        return view('stock-imports.history');
    }

    /** Detail satu import: ringkasan + perubahan stok yang dihasilkan. */
    public function show(Request $request, string $batch)
    {
        abort_unless(Str::isUuid($batch), 404);

        $user = $request->user();
        $log = $this->visibleLogs($user)->with('user:id,name')->where('batch_id', $batch)->firstOrFail();

        return view('stock-imports.show', [
            'log' => $log,
            'showBranch' => $user->hasAllBranchScope(),
            'branches' => Branch::accessibleBy($user)->orderBy('name')->get(['id', 'code', 'name', 'is_active']),
            'reasonGroups' => collect(InventoryController::REASON_GROUPS)->map(fn ($g) => $g['label'])->all(),
        ]);
    }

    /** Admin pusat melihat semua import; admin cabang hanya import yang menyentuh cabangnya. */
    private function visibleLogs(User $user)
    {
        $q = StockImportLog::query();

        if (! $user->hasAllBranchScope()) {
            $code = $user->branch?->code;
            $q->whereRaw('branches::jsonb @> ?::jsonb', [json_encode([(string) $code])]);
        }

        return $q;
    }

    public function downloadTemplate(Request $request)
    {
        $user = $request->user();
        $code = $user->hasAllBranchScope()
            ? (Branch::active()->orderBy('code')->value('code') ?? 'KNC-JKT1')
            : ($user->branch?->code ?? 'KNC-JKT1');

        return Excel::download(new StockUpdateImportTemplateExport($code), 'template-import-update-stok.xlsx');
    }

    public function exportCurrent(Request $request)
    {
        return Excel::download(new StockSnapshotExport($request->user()), 'stok-saat-ini-' . now()->format('Ymd-His') . '.xlsx');
    }

    public function preview(Request $request)
    {
        $request->validate([
            'excel' => 'required|file|mimes:xlsx,xls|max:20480',
            'zero_missing' => 'nullable|boolean',
        ], [
            'excel.required' => 'File Excel wajib diunggah',
            'excel.mimes' => 'File harus berformat .xlsx atau .xls',
            'excel.max' => 'Ukuran file maksimal 20 MB',
        ]);

        @set_time_limit(300);

        $file = $request->file('excel');
        $path = $file->store('tmp/stock-import', 'local');
        $zeroMissing = $request->boolean('zero_missing');

        $result = $this->analyze(Storage::disk('local')->path($path), $request->user(), $zeroMissing);

        $token = (string) Str::uuid();
        $request->session()->put(self::SESSION_KEY . '.' . $token, [
            'path' => $path,
            'user_id' => $request->user()->id,
            'name' => $file->getClientOriginalName(),
            'zero_missing' => $zeroMissing,
        ]);

        $view = $result['format'] === 'odoo' ? 'stock-imports.import-preview-odoo' : 'stock-imports.import-preview';

        return view($view, [
            'rows' => $result['rows'],
            'fatalErrors' => $result['fatal'],
            'summary' => $result['summary'],
            'warnings' => $result['warnings'],
            'branches' => $result['branches'],
            'token' => $token,
            'fileName' => $file->getClientOriginalName(),
            'zeroMissing' => $zeroMissing,
            'previewLimit' => self::PREVIEW_LIMIT,
        ]);
    }

    public function process(Request $request)
    {
        $request->validate(['token' => 'required|uuid']);
        $token = $request->input('token');
        $key = self::SESSION_KEY . '.' . $token;

        $meta = $request->session()->get($key);
        if (! $meta || (int) $meta['user_id'] !== (int) $request->user()->id
            || ! Storage::disk('local')->exists($meta['path'])) {
            return redirect()->route('stock_imports.form')->with('error', 'Sesi import kedaluwarsa. Unggah ulang file Anda.');
        }

        @set_time_limit(300);

        $user = $request->user();

        // Validasi ulang (jangan percaya tombol "disabled" di halaman preview)
        $result = $this->analyze(Storage::disk('local')->path($meta['path']), $user, (bool) ($meta['zero_missing'] ?? false));
        if ($result['fatal']) {
            return redirect()->route('stock_imports.form')
                ->with('error', 'Import diblokir: ' . implode(' ', array_slice($result['fatal'], 0, 3)));
        }

        $odoo = $result['format'] === 'odoo';
        $changes = array_values(array_filter($result['rows'], fn ($r) => $r['status'] === 'OK'));
        usort($changes, fn ($a, $b) => [$a['variant_id'], $a['branch_id']] <=> [$b['variant_id'], $b['branch_id']]);

        $batchId = (string) Str::uuid();

        try {
            DB::transaction(function () use ($changes, $batchId, $user, $meta, $odoo, $result) {
                foreach ($changes as $r) {
                    $metadata = [
                        'source' => $odoo ? 'odoo' : 'template',
                        'file' => $meta['name'],
                        'row' => $r['row'],
                        'mode' => $r['mode'],
                        'input' => $r['input'],
                        'note' => $r['note'],
                    ];
                    if (! empty($r['missing'])) {
                        $metadata['missing_in_file'] = true;
                    }

                    if ($r['mode'] === 'set') {
                        $this->inventory->setStock(
                            $r['branch_id'], $r['variant_id'], $r['target'], 'import_adjustment', 'stock_import',
                            $batchId, $user->id, $metadata, allowBelowReserved: $odoo
                        );
                    } else {
                        $this->inventory->applyDelta(
                            $r['branch_id'], $r['variant_id'], $r['delta'], 'import_adjustment', 'stock_import',
                            $batchId, $user->id, $metadata
                        );
                    }
                }

                $summary = $result['summary'];
                StockImportLog::create([
                    'batch_id' => $batchId,
                    'user_id' => $user->id,
                    'source' => $odoo ? 'odoo' : 'template',
                    'file_name' => $meta['name'],
                    'rows_total' => $summary['total'],
                    'rows_changed' => $summary['ok'] + ($summary['zeroed'] ?? 0),
                    'rows_zeroed' => $summary['zeroed'] ?? 0,
                    'rows_skipped' => $summary['skipped'] ?? 0,
                    'branches' => array_values(array_map(fn ($b) => $b['code'], $result['branches'])),
                    'created_at' => now(),
                ]);
            });
        } catch (InventoryException $e) {
            return redirect()->route('stock_imports.form')->with('error', 'Import dibatalkan: ' . $e->getMessage());
        } catch (\Throwable $th) {
            Log::error('Stock import failed', ['error' => $th->getMessage(), 'batch' => $batchId]);

            return redirect()->route('stock_imports.form')->with('error', 'Import gagal diproses. Tidak ada stok yang berubah.');
        }

        Storage::disk('local')->delete($meta['path']);
        $request->session()->forget($key);

        $summary = $result['summary'];
        $message = $odoo
            ? sprintf(
                'Sinkron stok dari Odoo berhasil: %d baris diperbarui, %d dinolkan (tidak ada di file), %d tanpa perubahan, %d dilewati.',
                $summary['ok'], $summary['zeroed'], $summary['unchanged'], $summary['skipped']
            )
            : sprintf('Import stok berhasil: %d baris diperbarui, %d baris tanpa perubahan.', $summary['ok'], $summary['unchanged']);

        return redirect()->route('stock_imports.form')->with('success', $message . ' Batch: ' . $batchId);
    }

    // ------------------------------------------------------------------ analisis file

    /**
     * Baca & validasi file. Tidak mengubah data.
     *
     * @return array{format: string, rows: list<array>, fatal: list<string>, summary: array, warnings: list<array>, branches: array}
     */
    private function analyze(string $fullPath, User $user, bool $zeroMissing = false): array
    {
        try {
            $sheet = Excel::toArray([], $fullPath)[0] ?? [];
        } catch (\Throwable $e) {
            Log::warning('Stock import: unreadable file', ['error' => $e->getMessage()]);

            return $this->failure('generic', 'File Excel tidak dapat dibaca.');
        }

        if (count($sheet) < 2) {
            return $this->failure('generic', 'File kosong: tidak ada baris data.');
        }

        $header = array_map(fn ($h) => Str::lower(trim((string) $h)), array_shift($sheet));

        if (in_array('warehouse', $header, true) && in_array('product', $header, true) && in_array('available quantity', $header, true)) {
            return $this->analyzeOdoo($sheet, $header, $user, $zeroMissing);
        }

        return $this->analyzeTemplate($sheet, $header, $user);
    }

    private function failure(string $format, string ...$fatal): array
    {
        return ['format' => $format, 'rows' => [], 'fatal' => array_values($fatal), 'summary' => $this->summary([]), 'warnings' => [], 'branches' => []];
    }

    // ------------------------------------------------------------------ format Odoo

    private function analyzeOdoo(array $sheet, array $header, User $user, bool $zeroMissing): array
    {
        $idx = fn (string $name) => ($i = array_search($name, $header, true)) === false ? null : $i;
        $iLoc = $idx('location');
        $iWh = $idx('warehouse');
        $iProd = $idx('product');
        $iQty = $idx('available quantity');
        $iUom = $idx('unit of measure');

        if ($iLoc === null) {
            return $this->failure('odoo', "Kolom 'Location' tidak ditemukan pada header.");
        }

        $cell = fn (array $cells, ?int $i) => $i === null ? null : ($cells[$i] ?? null);
        $text = fn ($v) => $v === null ? '' : trim((string) $v);

        $skip = [
            'non_stock' => 0, 'no_code' => [], 'unknown_wh' => [], 'denied' => [], 'unmatched' => [],
            'negative' => [], 'rounded' => 0, 'duplicates' => 0, 'bad_qty' => [], 'below_reserved' => [],
        ];

        // ---- pass 1: normalisasi baris
        $entries = [];
        $fileRows = 0;
        foreach ($sheet as $i => $cells) {
            if (count(array_filter($cells, fn ($c) => $c !== null && trim((string) $c) !== '')) === 0) {
                continue;
            }
            $fileRows++;
            $rowNo = $i + 2;

            if ($fileRows > self::MAX_ROWS_ODOO) {
                return $this->failure('odoo', 'Maksimal ' . self::MAX_ROWS_ODOO . ' baris per file.');
            }

            // Hanya lokasi ".../Stock" yang dihitung (Tinting, Scrap, Inventaris bukan stok jual)
            $location = $text($cell($cells, $iLoc));
            if (! preg_match('~/stock$~i', $location)) {
                $skip['non_stock']++;
                continue;
            }

            $product = $text($cell($cells, $iProd));
            if (! preg_match('/^\[([^\]]+)\]\s*(.*)$/u', $product, $m)) {
                $skip['no_code'][] = $product === '' ? "baris {$rowNo}" : $product;
                continue;
            }
            $code = trim($m[1]);

            $qtyRaw = $cell($cells, $iQty);
            $qtyStr = str_replace(',', '.', $text($qtyRaw));
            if (! is_numeric($qtyStr)) {
                $skip['bad_qty'][] = "baris {$rowNo}: '{$text($qtyRaw)}'";
                continue;
            }
            $f = (float) $qtyStr;
            if (abs($f * 100 - round($f * 100)) > 1e-6) {
                $skip['rounded']++;
            }
            $cents = (int) round($f * 100);
            if ($cents < 0) {
                $skip['negative'][] = "{$code} ({$qtyStr})";
                $cents = 0;
            }

            $entries[] = [
                'row' => $rowNo, 'warehouse' => $text($cell($cells, $iWh)), 'code' => $code,
                'name' => trim($m[2]), 'cents' => $cents, 'uom' => $text($cell($cells, $iUom)),
            ];
        }

        if ($fileRows === 0) {
            return $this->failure('odoo', 'Tidak ada baris data.');
        }

        // ---- gudang -> cabang
        $branches = Branch::whereNull('deleted_at')->get()->keyBy(fn ($b) => Str::lower(trim($b->name)));
        $resolved = [];
        foreach ($entries as $e) {
            $branch = $branches->get(Str::lower($e['warehouse']));
            if (! $branch || ! $branch->is_active) {
                $label = $e['warehouse'] === '' ? '(kosong)' : $e['warehouse'] . ($branch ? ' (cabang nonaktif)' : '');
                $skip['unknown_wh'][$label] = ($skip['unknown_wh'][$label] ?? 0) + 1;
                continue;
            }
            if (! $user->canAccessBranch((int) $branch->id)) {
                $skip['denied'][$branch->name] = ($skip['denied'][$branch->name] ?? 0) + 1;
                continue;
            }
            $e['branch'] = $branch;
            $resolved[] = $e;
        }

        // ---- kode -> varian (SKU varian; fallback SKU produk yang hanya punya satu varian)
        $codes = array_values(array_unique(array_column($resolved, 'code')));
        $variants = ProductVariant::withTrashed()->with('product:id,name')->whereIn('sku', $codes)->get()->keyBy('sku');

        $missingCodes = array_values(array_diff($codes, $variants->keys()->all()));
        $byProduct = [];
        if ($missingCodes) {
            foreach (Product::whereIn('sku', $missingCodes)->with(['variants' => fn ($q) => $q->with('product:id,name')])->get() as $p) {
                if ($p->variants->count() === 1) {
                    $byProduct[$p->sku] = $p->variants->first();
                }
            }
        }

        // ---- agregasi per (cabang, varian): beberapa quant dijumlahkan
        $agg = [];
        foreach ($resolved as $e) {
            $variant = $variants->get($e['code']) ?? ($byProduct[$e['code']] ?? null);
            if (! $variant) {
                $skip['unmatched'][] = $e['code'] . ' ' . $e['name'];
                continue;
            }
            if ($variant->trashed()) {
                $skip['unmatched'][] = $e['code'] . ' (varian sudah dihapus)';
                continue;
            }

            $key = $e['branch']->id . ':' . $variant->id;
            if (isset($agg[$key])) {
                $agg[$key]['cents'] += $e['cents'];
                $agg[$key]['rows'][] = $e['row'];
                $skip['duplicates']++;
            } else {
                $agg[$key] = $e + ['variant' => $variant, 'rows' => [$e['row']]];
            }
        }

        if (! $agg) {
            return ['format' => 'odoo', 'rows' => [], 'fatal' => [
                'Tidak ada baris yang cocok dengan produk/cabang di backoffice. Periksa apakah kode produk Odoo sama dengan SKU varian dan nama Warehouse sama dengan nama cabang.',
            ], 'summary' => $this->summary([], $fileRows, $skip), 'warnings' => $this->odooWarnings($skip), 'branches' => []];
        }

        // ---- stok sekarang
        $branchIds = collect($agg)->pluck('branch.id')->unique()->values();
        $inventory = DB::table('inventory')->whereNull('deleted_at')->whereIn('branch_id', $branchIds)->get()
            ->keyBy(fn ($i) => $i->branch_id . ':' . $i->variant_id);

        $rows = [];
        $branchStats = [];
        $bump = function (Branch $b, string $field) use (&$branchStats) {
            $branchStats[$b->id] ??= ['code' => $b->code, 'name' => $b->name, 'matched' => 0, 'ok' => 0, 'unchanged' => 0, 'zeroed' => 0];
            $branchStats[$b->id][$field]++;
        };

        foreach ($agg as $key => $a) {
            $inv = $inventory->get($key);
            $current = $inv ? Qty::norm($inv->on_hand) : '0.00';
            $reserved = $inv ? Qty::norm($inv->reserved) : '0.00';
            $target = Qty::fromCents($a['cents']);
            $delta = Qty::sub($target, $current);

            if (Qty::cmp($target, $reserved) < 0) {
                $skip['below_reserved'][] = sprintf('%s di %s: stok %s < reserved %s', $a['code'], $a['branch']->code, Qty::fmt($target), Qty::fmt($reserved));
            }

            $status = Qty::isZero($delta) ? 'UNCHANGED' : 'OK';
            $bump($a['branch'], 'matched');
            $bump($a['branch'], $status === 'OK' ? 'ok' : 'unchanged');

            $rows[] = [
                'row' => implode(',', array_slice($a['rows'], 0, 3)), 'status' => $status, 'messages' => [],
                'branch_id' => $a['branch']->id, 'branch_code' => $a['branch']->code,
                'variant_id' => $a['variant']->id, 'sku' => $a['code'],
                'product' => $this->variantLabel($a['variant']), 'unit' => $a['uom'],
                'mode' => 'set', 'input' => $target, 'note' => '', 'current' => $current,
                'target' => $target, 'delta' => $delta, 'missing' => false,
            ];
        }

        // ---- produk yang tidak ada di file -> 0 (hanya cabang yang ada di file dan punya baris cocok)
        if ($zeroMissing) {
            $inFile = [];
            foreach ($agg as $a) {
                $inFile[$a['branch']->id][$a['variant']->id] = true;
            }

            $zeroRows = $inventory->filter(fn ($i) => isset($inFile[$i->branch_id]) && ! isset($inFile[$i->branch_id][$i->variant_id])
                && Qty::cmp($i->on_hand, 0) > 0);

            $zeroVariants = ProductVariant::withTrashed()->with('product:id,name')
                ->whereIn('id', $zeroRows->pluck('variant_id')->unique())->get()->keyBy('id');
            $branchById = collect($agg)->pluck('branch')->keyBy('id');

            foreach ($zeroRows as $inv) {
                $variant = $zeroVariants->get($inv->variant_id);
                $branch = $branchById->get($inv->branch_id);
                $current = Qty::norm($inv->on_hand);
                $bump($branch, 'zeroed');

                $rows[] = [
                    'row' => '-', 'status' => 'OK', 'messages' => ['tidak ada di file Odoo'],
                    'branch_id' => $inv->branch_id, 'branch_code' => $branch->code,
                    'variant_id' => $inv->variant_id, 'sku' => $variant?->sku ?? ('#' . $inv->variant_id),
                    'product' => $variant ? $this->variantLabel($variant) : '-', 'unit' => '',
                    'mode' => 'set', 'input' => '0.00', 'note' => '', 'current' => $current,
                    'target' => '0.00', 'delta' => Qty::sub('0', $current), 'missing' => true,
                ];
            }
        }

        $summary = $this->summary($rows, $fileRows, $skip);
        $fatal = [];
        if ($summary['ok'] + $summary['zeroed'] === 0) {
            $fatal[] = 'Tidak ada perubahan stok pada file ini (stok sudah sama dengan Odoo).';
        }

        return [
            'format' => 'odoo', 'rows' => $rows, 'fatal' => $fatal, 'summary' => $summary,
            'warnings' => $this->odooWarnings($skip), 'branches' => $branchStats,
        ];
    }

    private function variantLabel(ProductVariant $variant): string
    {
        return trim(($variant->product->name ?? '') . ($variant->variant_name ? ' — ' . $variant->variant_name : ''));
    }

    /** @return list<array{title: string, count: int, items: list<string>, level: string}> */
    private function odooWarnings(array $skip): array
    {
        $w = [];
        $add = function (string $title, array $items, string $level = 'warning') use (&$w) {
            if (! $items) {
                return;
            }
            $w[] = ['title' => $title, 'count' => count($items), 'items' => array_slice($items, 0, self::WARNING_ITEMS), 'level' => $level];
        };

        $assoc = fn (array $m) => array_map(fn ($k, $n) => "{$k} ({$n} baris)", array_keys($m), $m);

        $add('Gudang tidak dikenal / tidak aktif (baris dilewati)', $assoc($skip['unknown_wh']));
        $add('Gudang di luar hak akses Anda (baris dilewati)', $assoc($skip['denied']));
        $add('Produk tanpa kode [..] (tidak bisa dicocokkan, dilewati)', array_values(array_unique($skip['no_code'])));
        $add('Kode produk tidak ditemukan di backoffice (dilewati)', array_values(array_unique($skip['unmatched'])));
        $add('Stok negatif di Odoo dianggap 0', $skip['negative']);
        $add('Stok di bawah stok reserved pesanan (available jadi 0)', $skip['below_reserved']);
        $add('Nilai stok tidak valid (baris dilewati)', $skip['bad_qty'], 'danger');

        return $w;
    }

    // ------------------------------------------------------------------ format template

    private function analyzeTemplate(array $sheet, array $header, User $user): array
    {
        $fatal = [];
        $col = [];
        foreach (['branch_code', 'variant_sku', 'new_stock', 'mode', 'note'] as $name) {
            $idx = array_search($name, $header, true);
            $col[$name] = $idx === false ? null : $idx;
        }

        foreach (self::REQUIRED_COLUMNS as $required) {
            if ($col[$required] === null) {
                $fatal[] = "Kolom wajib '{$required}' tidak ditemukan pada header.";
            }
        }
        if ($col['branch_code'] === null && $user->hasAllBranchScope()) {
            $fatal[] = "Kolom wajib 'branch_code' tidak ditemukan pada header. (Untuk export Odoo, pastikan kolom Warehouse, Product, dan Available Quantity ada.)";
        }
        if ($fatal) {
            return $this->failure('generic', ...$fatal);
        }

        // Kumpulkan baris tidak kosong
        $raw = [];
        foreach ($sheet as $i => $cells) {
            $rowNumber = $i + 2;
            if (count(array_filter($cells, fn ($c) => $c !== null && trim((string) $c) !== '')) === 0) {
                continue;
            }
            $raw[] = ['row' => $rowNumber, 'cells' => $cells];
        }

        if (! $raw) {
            return $this->failure('generic', 'Tidak ada baris data.');
        }
        if (count($raw) > self::MAX_ROWS) {
            return $this->failure('generic', 'Maksimal ' . self::MAX_ROWS . ' baris per file (ditemukan ' . count($raw) . ').');
        }

        $cell = fn (array $cells, ?int $idx) => $idx === null ? null : (isset($cells[$idx]) ? $cells[$idx] : null);
        $text = fn ($v) => $v === null ? '' : trim(is_float($v) && floor($v) == $v ? (string) (int) $v : (string) $v);

        // Preload master data
        $branches = Branch::whereNull('deleted_at')->get()->keyBy(fn ($b) => Str::upper($b->code));
        $skus = collect($raw)->map(fn ($r) => $text($cell($r['cells'], $col['variant_sku'])))->filter()->unique()->values();
        $variants = ProductVariant::withTrashed()->with(['product:id,name', 'unit:id,name'])
            ->whereIn('sku', $skus)->get()->keyBy('sku');

        $inventory = DB::table('inventory')
            ->whereNull('deleted_at')
            ->whereIn('variant_id', $variants->pluck('id'))
            ->get()
            ->keyBy(fn ($i) => $i->branch_id . ':' . $i->variant_id);

        $seen = [];
        $rows = [];

        foreach ($raw as $entry) {
            $cells = $entry['cells'];
            $errors = [];

            $branchCode = $text($cell($cells, $col['branch_code']));
            $sku = $text($cell($cells, $col['variant_sku']));
            $inputRaw = $cell($cells, $col['new_stock']);
            $mode = Str::lower($text($cell($cells, $col['mode']))) ?: 'set';
            $note = $text($cell($cells, $col['note']));

            // Cabang
            $branch = null;
            if ($branchCode === '') {
                if (! $user->hasAllBranchScope()) {
                    $branch = $user->branch;
                } else {
                    $errors[] = 'branch_code kosong';
                }
            } else {
                $branch = $branches->get(Str::upper($branchCode));
                if (! $branch) {
                    $errors[] = "cabang '{$branchCode}' tidak ditemukan";
                }
            }
            if ($branch) {
                if (! $branch->is_active) {
                    $errors[] = "cabang '{$branch->code}' tidak aktif";
                } elseif (! $user->canAccessBranch((int) $branch->id)) {
                    $errors[] = "Anda tidak memiliki akses ke cabang '{$branch->code}'";
                }
            }

            // Varian
            $variant = $sku === '' ? null : $variants->get($sku);
            if ($sku === '') {
                $errors[] = 'variant_sku kosong';
            } elseif (! $variant) {
                $errors[] = "SKU '{$sku}' tidak ditemukan";
            } elseif ($variant->trashed()) {
                $errors[] = "SKU '{$sku}' sudah dihapus";
            }

            // Mode & angka
            if (! in_array($mode, self::MODES, true)) {
                $errors[] = "mode '{$mode}' tidak valid (set / add / subtract)";
            }
            $input = Qty::parse($inputRaw);
            if ($input === null) {
                $errors[] = "new_stock '" . $text($inputRaw) . "' bukan angka valid (maksimal 2 angka di belakang koma)";
            } elseif (Qty::cmp($input, 0) < 0) {
                $errors[] = 'new_stock tidak boleh negatif';
            }

            // Duplikat
            if ($branch && $variant) {
                $dupKey = $branch->id . ':' . $variant->id;
                if (isset($seen[$dupKey])) {
                    $errors[] = "duplikat dengan baris {$seen[$dupKey]}";
                } else {
                    $seen[$dupKey] = $entry['row'];
                }
            }

            // Hitung target
            $current = '0.00';
            $reserved = '0.00';
            $target = null;
            $delta = null;
            if (! $errors && $branch && $variant) {
                $inv = $inventory->get($branch->id . ':' . $variant->id);
                $current = $inv ? Qty::norm($inv->on_hand) : '0.00';
                $reserved = $inv ? Qty::norm($inv->reserved) : '0.00';

                $target = match ($mode) {
                    'set' => $input,
                    'add' => Qty::add($current, $input),
                    'subtract' => Qty::sub($current, $input),
                };
                $delta = Qty::sub($target, $current);

                if (Qty::cmp($target, 0) < 0) {
                    $errors[] = 'stok akan menjadi negatif (' . Qty::fmt($target) . ')';
                } elseif (Qty::cmp($target, $reserved) < 0) {
                    $errors[] = 'stok baru (' . Qty::fmt($target) . ') lebih kecil dari stok reserved (' . Qty::fmt($reserved) . ')';
                }
            }

            $status = $errors ? 'ERROR' : (Qty::isZero($delta) ? 'UNCHANGED' : 'OK');

            $rows[] = [
                'row' => $entry['row'],
                'status' => $status,
                'messages' => $errors,
                'branch_id' => $branch?->id,
                'branch_code' => $branch?->code ?? $branchCode,
                'variant_id' => $variant?->id,
                'sku' => $sku,
                'product' => $variant ? $this->variantLabel($variant) : '-',
                'unit' => $variant?->unit->name ?? '',
                'mode' => $mode,
                'input' => $input,
                'note' => $note,
                'current' => $current,
                'target' => $target,
                'delta' => $delta,
                'missing' => false,
            ];
        }

        $errorRows = array_filter($rows, fn ($r) => $r['status'] === 'ERROR');
        if ($errorRows) {
            $fatal[] = count($errorRows) . ' baris bermasalah. Perbaiki file lalu unggah ulang (import bersifat semua-atau-tidak-sama-sekali).';
        } elseif (! array_filter($rows, fn ($r) => $r['status'] === 'OK')) {
            $fatal[] = 'Tidak ada perubahan stok pada file ini.';
        }

        return ['format' => 'generic', 'rows' => $rows, 'fatal' => $fatal, 'summary' => $this->summary($rows), 'warnings' => [], 'branches' => []];
    }

    private function summary(array $rows, ?int $fileRows = null, ?array $skip = null): array
    {
        $count = fn (string $s) => count(array_filter($rows, fn ($r) => $r['status'] === $s && empty($r['missing'])));
        $zeroed = count(array_filter($rows, fn ($r) => ! empty($r['missing'])));

        $skipped = 0;
        if ($skip) {
            $skipped = array_sum($skip['unknown_wh']) + array_sum($skip['denied'])
                + count($skip['no_code']) + count($skip['unmatched']) + count($skip['bad_qty']);
        }

        return [
            'total' => $fileRows ?? count($rows),
            'ok' => $count('OK'),
            'unchanged' => $count('UNCHANGED'),
            'error' => $count('ERROR'),
            'zeroed' => $zeroed,
            'skipped' => $skipped,
            'non_stock' => $skip['non_stock'] ?? 0,
            'duplicates' => $skip['duplicates'] ?? 0,
        ];
    }
}
