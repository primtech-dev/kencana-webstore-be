<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\ProductVariant;
use App\Models\StockImportLog;
use App\Models\User;
use App\Support\Qty;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/**
 * Manajemen stok: stok saat ini per cabang + riwayat perubahan stok.
 * Admin pusat melihat semua cabang (bisa memilih cabang); admin cabang hanya cabangnya.
 */
class InventoryController extends Controller
{
    /** Pengelompokan alasan pergerakan stok untuk filter dan label. */
    public const REASON_GROUPS = [
        'receipt' => ['label' => 'Stok masuk', 'reasons' => ['receipt']],
        'receipt_correction' => ['label' => 'Koreksi stok masuk', 'reasons' => ['receipt_reverse', 'receipt_reverse_on_update', 'receipt_adjustment', 'receipt_deleted']],
        'shipment' => ['label' => 'Pengiriman order', 'reasons' => ['sale_shipment']],
        'unreserved' => ['label' => 'Reservasi dilepas (order batal)', 'reasons' => ['order_unreserved']],
        'return' => ['label' => 'Retur order', 'reasons' => ['order_return']],
        'import' => ['label' => 'Import stok', 'reasons' => ['import_adjustment']],
    ];

    private const STOCK_STATUSES = ['ada' => 'Tersedia (available > 0)', 'habis' => 'Habis (stok fisik 0)', 'reserved' => 'Ada reservasi'];

    // ------------------------------------------------------------------ stok saat ini

    public function index(Request $request)
    {
        $user = $request->user();

        if ($request->ajax()) {
            $filters = $request->validate([
                'branch_id' => ['nullable', 'integer'],
                'status' => ['nullable', Rule::in(array_keys(self::STOCK_STATUSES))],
            ]);

            $q = DB::table('inventory as i')
                ->join('product_variants as v', 'v.id', '=', 'i.variant_id')
                ->leftJoin('products as p', 'p.id', '=', 'v.product_id')
                ->join('branches as b', 'b.id', '=', 'i.branch_id')
                ->leftJoin('units as u', 'u.id', '=', 'v.unit_id')
                ->whereNull('i.deleted_at')->whereNull('v.deleted_at')->whereNull('b.deleted_at')
                ->select([
                    'i.id', 'i.variant_id', 'i.branch_id', 'i.on_hand', 'i.reserved', 'i.available', 'i.updated_at',
                    'b.name as branch_name', 'b.code as branch_code',
                    'v.sku', 'v.variant_name', 'p.name as product_name', 'u.name as unit_name',
                ]);

            $this->scopeBranch($q, $user, 'i.branch_id', $filters['branch_id'] ?? null);

            match ($filters['status'] ?? null) {
                'ada' => $q->where('i.available', '>', 0),
                'habis' => $q->where('i.on_hand', '<=', 0),
                'reserved' => $q->where('i.reserved', '>', 0),
                default => null,
            };

            return datatables()->query($q)
                ->addIndexColumn()
                ->filter(function ($query) use ($request) {
                    $keyword = mb_strtolower(trim((string) $request->input('search.value')));
                    if ($keyword === '') {
                        return;
                    }
                    $like = '%' . $keyword . '%';
                    $query->where(function ($w) use ($like) {
                        $w->whereRaw('LOWER(v.sku) LIKE ?', [$like])
                            ->orWhereRaw('LOWER(v.variant_name) LIKE ?', [$like])
                            ->orWhereRaw('LOWER(p.name) LIKE ?', [$like]);
                    });
                })
                ->addColumn('product', fn ($r) => '<a target="_blank" rel="noopener" href="' . route('inventory.variant', $r->variant_id) . '?branch_id=' . $r->branch_id . '">'
                    . e($r->product_name ?: '-') . '</a><div class="small text-muted">' . e($r->variant_name) . '</div>')
                ->editColumn('on_hand', fn ($r) => Qty::fmt($r->on_hand))
                ->editColumn('reserved', fn ($r) => Qty::fmt($r->reserved))
                ->editColumn('available', fn ($r) => '<span class="fw-semibold ' . (Qty::cmp($r->available, 0) > 0 ? 'text-success' : 'text-danger') . '">'
                    . Qty::fmt($r->available) . '</span>')
                ->editColumn('updated_at', fn ($r) => $r->updated_at ? \Carbon\Carbon::parse($r->updated_at)->format('d M Y H:i') : '-')
                ->addColumn('action', fn ($r) => '<a class="btn btn-sm btn-outline-primary" target="_blank" rel="noopener" href="' . route('inventory.variant', $r->variant_id) . '?branch_id=' . $r->branch_id . '">Riwayat</a>')
                ->orderColumn('branch_name', 'b.name $1')
                ->orderColumn('sku', 'v.sku $1')
                ->orderColumn('product', 'p.name $1')
                ->orderColumn('on_hand', 'i.on_hand $1')
                ->orderColumn('reserved', 'i.reserved $1')
                ->orderColumn('available', 'i.available $1')
                ->orderColumn('updated_at', 'i.updated_at $1')
                ->rawColumns(['product', 'available', 'action'])
                ->toJson();
        }

        $lastOdoo = StockImportLog::with('user:id,name')->where('source', 'odoo')->latest('id')->first();

        return view('inventory.index', [
            'branches' => $this->branchOptions($user),
            'showBranch' => $user->hasAllBranchScope(),
            'statuses' => self::STOCK_STATUSES,
            'lastOdoo' => $lastOdoo,
        ]);
    }

    // ------------------------------------------------------------------ riwayat

    public function movements(Request $request)
    {
        $user = $request->user();

        if ($request->ajax()) {
            return $this->movementsData($request, $user);
        }

        return view('inventory.movements', $this->movementViewData($user));
    }

    public function variant(Request $request, int $id)
    {
        $user = $request->user();
        $variant = ProductVariant::with(['product:id,name', 'unit:id,name'])->findOrFail($id);

        $branches = $this->branchOptions($user);
        $rows = DB::table('inventory')->whereNull('deleted_at')->where('variant_id', $id)
            ->whereIn('branch_id', $branches->pluck('id'))->get()->keyBy('branch_id');

        $perBranch = $branches->map(function ($b) use ($rows) {
            $r = $rows->get($b->id);

            return [
                'id' => $b->id, 'code' => $b->code, 'name' => $b->name, 'active' => $b->is_active,
                'on_hand' => $r ? Qty::norm($r->on_hand) : '0.00',
                'reserved' => $r ? Qty::norm($r->reserved) : '0.00',
                'available' => $r ? Qty::norm($r->available) : '0.00',
                'has_row' => (bool) $r,
            ];
        })->filter(fn ($r) => $r['active'] || $r['has_row'])->values();

        $total = ['on_hand' => '0.00', 'reserved' => '0.00', 'available' => '0.00'];
        foreach ($perBranch as $r) {
            foreach ($total as $k => $_) {
                $total[$k] = Qty::add($total[$k], $r[$k]);
            }
        }

        return view('inventory.variant', $this->movementViewData($user) + [
            'variant' => $variant,
            'perBranch' => $perBranch,
            'total' => $total,
            'initialBranch' => $user->hasAllBranchScope() ? (int) $request->query('branch_id') : (int) $user->branch_id,
        ]);
    }

    // ------------------------------------------------------------------ helpers

    private function movementViewData(User $user): array
    {
        return [
            'branches' => $this->branchOptions($user),
            'showBranch' => $user->hasAllBranchScope(),
            'reasonGroups' => collect(self::REASON_GROUPS)->map(fn ($g) => $g['label'])->all(),
        ];
    }

    private function movementsData(Request $request, User $user)
    {
        $filters = $request->validate([
            'branch_id' => ['nullable', 'integer'],
            'variant_id' => ['nullable', 'integer'],
            'reason' => ['nullable', Rule::in(array_keys(self::REASON_GROUPS))],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date'],
        ]);

        $q = DB::table('stock_movements as sm')
            ->join('branches as b', 'b.id', '=', 'sm.branch_id')
            ->join('product_variants as v', 'v.id', '=', 'sm.variant_id')
            ->leftJoin('products as p', 'p.id', '=', 'v.product_id')
            ->leftJoin('users as usr', 'usr.id', '=', 'sm.performed_by')
            ->leftJoin('orders as o', function ($j) {
                $j->on('o.public_id', '=', 'sm.reference_id')->where('sm.reference_type', '=', 'order');
            })
            ->leftJoin('stock_receipts as sr', function ($j) {
                $j->on('sr.public_id', '=', 'sm.reference_id')->where('sm.reference_type', '=', 'receipt');
            })
            ->whereNull('sm.deleted_at')
            ->select([
                'sm.id', 'sm.created_at', 'sm.variant_id', 'sm.branch_id', 'sm.change', 'sm.resulting_on_hand',
                'sm.reason', 'sm.reference_type', 'sm.reference_id', 'sm.metadata',
                'b.name as branch_name', 'b.code as branch_code',
                'v.sku', 'v.variant_name', 'p.name as product_name',
                'usr.name as performer', 'o.id as order_id', 'o.order_no', 'sr.id as receipt_id',
            ]);

        $this->scopeBranch($q, $user, 'sm.branch_id', $filters['branch_id'] ?? null);

        if (! empty($filters['variant_id'])) {
            $q->where('sm.variant_id', (int) $filters['variant_id']);
        }
        if (! empty($filters['reason'])) {
            $q->whereIn('sm.reason', self::REASON_GROUPS[$filters['reason']]['reasons']);
        }
        if (! empty($filters['date_from'])) {
            $q->where('sm.created_at', '>=', $filters['date_from'] . ' 00:00:00');
        }
        if (! empty($filters['date_to'])) {
            $q->where('sm.created_at', '<=', $filters['date_to'] . ' 23:59:59');
        }

        $canOrder = $user->can('orders.view');
        $canReceipt = $user->can('stock_receipts.view');
        $labels = $this->reasonLabels();

        return datatables()->query($q)
            ->addIndexColumn()
            ->filter(function ($query) use ($request) {
                $keyword = mb_strtolower(trim((string) $request->input('search.value')));
                if ($keyword === '') {
                    return;
                }
                $like = '%' . $keyword . '%';
                $query->where(function ($w) use ($like) {
                    $w->whereRaw('LOWER(v.sku) LIKE ?', [$like])
                        ->orWhereRaw('LOWER(v.variant_name) LIKE ?', [$like])
                        ->orWhereRaw('LOWER(p.name) LIKE ?', [$like])
                        ->orWhereRaw('LOWER(o.order_no) LIKE ?', [$like]);
                });
            })
            ->editColumn('created_at', fn ($r) => \Carbon\Carbon::parse($r->created_at)->format('d M Y H:i'))
                        ->addColumn('product', fn ($r) => '<a href="' . route('inventory.variant', $r->variant_id) . '?branch_id=' . $r->branch_id . '">'
                . e($r->product_name ?: '-') . '</a><div class="small text-muted">' . e($r->sku) . ' &middot; ' . e($r->variant_name) . '</div>')
            ->editColumn('change', function ($r) {
                $positive = Qty::cmp($r->change, 0) > 0;
                $zero = Qty::isZero($r->change);

                return '<span class="fw-semibold ' . ($zero ? 'text-muted' : ($positive ? 'text-success' : 'text-danger')) . '">'
                    . ($positive ? '+' : '') . Qty::fmt($r->change) . '</span>';
            })
            ->editColumn('resulting_on_hand', fn ($r) => $r->resulting_on_hand === null ? '-' : Qty::fmt($r->resulting_on_hand))
            ->editColumn('reason', fn ($r) => '<span class="badge bg-light text-dark border">' . e($labels[$r->reason] ?? $r->reason) . '</span>')
            ->addColumn('reference', function ($r) use ($canOrder, $canReceipt) {
                if ($r->order_no) {
                    return $canOrder ? '<a href="' . route('admin.orders.show', $r->order_id) . '">Order ' . e($r->order_no) . '</a>' : 'Order ' . e($r->order_no);
                }
                if ($r->receipt_id) {
                    return $canReceipt ? '<a href="' . route('stock_receipts.show', $r->receipt_id) . '">Stok masuk #' . $r->receipt_id . '</a>' : 'Stok masuk #' . $r->receipt_id;
                }
                if ($r->reference_type === 'stock_import') {
                    return 'Import stok';
                }

                return '-';
            })
            ->addColumn('note', function ($r) {
                $meta = is_string($r->metadata) ? json_decode($r->metadata, true) : (array) $r->metadata;
                $meta = is_array($meta) ? $meta : [];
                $parts = array_filter([
                    $meta['note'] ?? null,
                    isset($meta['file']) ? 'File: ' . $meta['file'] : null,
                    ! empty($meta['missing_in_file']) ? 'tidak ada di file import' : null,
                ]);

                return implode(' · ', $parts);
            })
            ->editColumn('performer', fn ($r) => $r->performer ?: 'Sistem')
            ->orderColumn('created_at', 'sm.created_at $1, sm.id $1')
            ->orderColumn('branch_name', 'b.name $1')
            ->orderColumn('product', 'p.name $1')
            ->orderColumn('change', 'sm.change $1')
            ->orderColumn('resulting_on_hand', 'sm.resulting_on_hand $1')
            ->orderColumn('reason', 'sm.reason $1')
            ->rawColumns(['product', 'change', 'reason', 'reference'])
            ->toJson();
    }

    private function reasonLabels(): array
    {
        $labels = [];
        foreach (self::REASON_GROUPS as $group) {
            foreach ($group['reasons'] as $reason) {
                $labels[$reason] = $group['label'];
            }
        }
        // alasan spesifik koreksi stok masuk
        $labels['receipt_reverse'] = 'Koreksi stok masuk (pembatalan)';
        $labels['receipt_reverse_on_update'] = 'Koreksi stok masuk (diubah)';
        $labels['receipt_adjustment'] = 'Koreksi stok masuk (selisih)';
        $labels['receipt_deleted'] = 'Koreksi stok masuk (dihapus)';

        return $labels;
    }

    /** User cabang selalu dipaksa ke cabangnya; pusat boleh memilih satu cabang atau semua. */
    private function scopeBranch($query, User $user, string $column, $requestedBranchId): void
    {
        if (! $user->hasAllBranchScope()) {
            $query->where($column, (int) $user->branch_id);

            return;
        }

        if (! empty($requestedBranchId)) {
            $query->where($column, (int) $requestedBranchId);
        }
    }

    private function branchOptions(User $user)
    {
        return Branch::accessibleBy($user)->orderBy('name')->get(['id', 'code', 'name', 'is_active']);
    }
}
