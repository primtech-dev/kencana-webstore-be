<?php

namespace App\Http\Controllers;

use App\Exceptions\InventoryException;
use App\Exports\StockSnapshotExport;
use App\Exports\StockUpdateImportTemplateExport;
use App\Models\Branch;
use App\Models\ProductVariant;
use App\Models\User;
use App\Services\InventoryService;
use App\Support\Qty;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

/**
 * Import Update Stok (Excel): form -> preview -> konfirmasi.
 *
 * Kolom: branch_code, variant_sku, new_stock, mode (set|add|subtract, default set), note.
 * Seluruh file bersifat all-or-nothing: satu baris error memblokir import.
 */
class StockImportController extends Controller
{
    private const MAX_ROWS = 5000;
    private const MODES = ['set', 'add', 'subtract'];
    private const REQUIRED_COLUMNS = ['variant_sku', 'new_stock'];
    private const SESSION_KEY = 'stock_import';

    public function __construct(private InventoryService $inventory)
    {
    }

    public function form()
    {
        return view('stock-imports.import');
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
            'excel' => 'required|file|mimes:xlsx,xls|max:10240',
        ], [
            'excel.required' => 'File Excel wajib diunggah',
            'excel.mimes' => 'File harus berformat .xlsx atau .xls',
            'excel.max' => 'Ukuran file maksimal 10 MB',
        ]);

        $file = $request->file('excel');
        $path = $file->store('tmp/stock-import', 'local');

        $result = $this->analyze(Storage::disk('local')->path($path), $request->user());

        $token = (string) Str::uuid();
        $request->session()->put(self::SESSION_KEY . '.' . $token, [
            'path' => $path,
            'user_id' => $request->user()->id,
            'name' => $file->getClientOriginalName(),
        ]);

        return view('stock-imports.import-preview', [
            'rows' => $result['rows'],
            'fatalErrors' => $result['fatal'],
            'summary' => $result['summary'],
            'token' => $token,
            'fileName' => $file->getClientOriginalName(),
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

        $user = $request->user();

        // Validasi ulang (jangan percaya tombol "disabled" di halaman preview)
        $result = $this->analyze(Storage::disk('local')->path($meta['path']), $user);
        if ($result['fatal']) {
            return redirect()->route('stock_imports.form')
                ->with('error', 'Import diblokir: ' . implode(' ', array_slice($result['fatal'], 0, 3)));
        }

        $changes = array_values(array_filter($result['rows'], fn ($r) => $r['status'] === 'OK'));
        usort($changes, fn ($a, $b) => [$a['variant_id'], $a['branch_id']] <=> [$b['variant_id'], $b['branch_id']]);

        $batchId = (string) Str::uuid();

        try {
            DB::transaction(function () use ($changes, $batchId, $user, $meta) {
                foreach ($changes as $r) {
                    $metadata = [
                        'source' => 'stock_import',
                        'file' => $meta['name'],
                        'row' => $r['row'],
                        'mode' => $r['mode'],
                        'input' => $r['input'],
                        'note' => $r['note'],
                    ];

                    if ($r['mode'] === 'set') {
                        $this->inventory->setStock($r['branch_id'], $r['variant_id'], $r['target'], 'import_adjustment', 'stock_import', $batchId, $user->id, $metadata);
                    } else {
                        $this->inventory->applyDelta($r['branch_id'], $r['variant_id'], $r['delta'], 'import_adjustment', 'stock_import', $batchId, $user->id, $metadata);
                    }
                }
            });
        } catch (InventoryException $e) {
            return redirect()->route('stock_imports.form')->with('error', 'Import dibatalkan: ' . $e->getMessage());
        } catch (\Throwable $th) {
            Log::error('Stock import failed', ['error' => $th->getMessage(), 'batch' => $batchId]);

            return redirect()->route('stock_imports.form')->with('error', 'Import gagal diproses. Tidak ada stok yang berubah.');
        }

        Storage::disk('local')->delete($meta['path']);
        $request->session()->forget($key);

        $unchanged = count($result['rows']) - count($changes);

        return redirect()->route('stock_receipts.index')->with(
            'success',
            sprintf('Import stok berhasil: %d baris diperbarui, %d baris tanpa perubahan. Batch: %s', count($changes), $unchanged, $batchId)
        );
    }

    // ------------------------------------------------------------------ analisis file

    /**
     * Baca & validasi file. Tidak mengubah data.
     *
     * @return array{rows: list<array>, fatal: list<string>, summary: array}
     */
    private function analyze(string $fullPath, User $user): array
    {
        $fatal = [];

        try {
            $sheet = Excel::toArray([], $fullPath)[0] ?? [];
        } catch (\Throwable $e) {
            Log::warning('Stock import: unreadable file', ['error' => $e->getMessage()]);

            return ['rows' => [], 'fatal' => ['File Excel tidak dapat dibaca.'], 'summary' => $this->summary([])];
        }

        if (count($sheet) < 2) {
            return ['rows' => [], 'fatal' => ['File kosong: tidak ada baris data.'], 'summary' => $this->summary([])];
        }

        $header = array_map(fn ($h) => Str::lower(trim((string) $h)), array_shift($sheet));
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
            $fatal[] = "Kolom wajib 'branch_code' tidak ditemukan pada header.";
        }
        if ($fatal) {
            return ['rows' => [], 'fatal' => $fatal, 'summary' => $this->summary([])];
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
            return ['rows' => [], 'fatal' => ['Tidak ada baris data.'], 'summary' => $this->summary([])];
        }
        if (count($raw) > self::MAX_ROWS) {
            return ['rows' => [], 'fatal' => ['Maksimal ' . self::MAX_ROWS . ' baris per file (ditemukan ' . count($raw) . ').'], 'summary' => $this->summary([])];
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
                'product' => $variant ? trim(($variant->product->name ?? '') . ($variant->variant_name ? ' — ' . $variant->variant_name : '')) : '-',
                'unit' => $variant?->unit->name ?? '',
                'mode' => $mode,
                'input' => $input,
                'note' => $note,
                'current' => $current,
                'target' => $target,
                'delta' => $delta,
            ];
        }

        $errorRows = array_filter($rows, fn ($r) => $r['status'] === 'ERROR');
        if ($errorRows) {
            $fatal[] = count($errorRows) . ' baris bermasalah. Perbaiki file lalu unggah ulang (import bersifat semua-atau-tidak-sama-sekali).';
        } elseif (! array_filter($rows, fn ($r) => $r['status'] === 'OK')) {
            $fatal[] = 'Tidak ada perubahan stok pada file ini.';
        }

        return ['rows' => $rows, 'fatal' => $fatal, 'summary' => $this->summary($rows)];
    }

    private function summary(array $rows): array
    {
        $count = fn (string $s) => count(array_filter($rows, fn ($r) => $r['status'] === $s));

        return [
            'total' => count($rows),
            'ok' => $count('OK'),
            'unchanged' => $count('UNCHANGED'),
            'error' => $count('ERROR'),
        ];
    }
}
