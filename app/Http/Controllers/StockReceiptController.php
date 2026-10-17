<?php

namespace App\Http\Controllers;

use App\Exceptions\InventoryException;
use App\Models\Branch;
use App\Models\ProductVariant;
use App\Models\StockReceipt;
use App\Models\StockReceiptItem;
use App\Services\InventoryService;
use App\Support\Qty;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class StockReceiptController extends Controller
{
    public function __construct(private InventoryService $inventory)
    {
    }

    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = StockReceipt::visibleTo($request->user())
                ->with('branch', 'creator')
                ->select(['id', 'public_id', 'branch_id', 'supplier_name', 'reference_no', 'status', 'total_items', 'created_at', 'received_at']);

            return datatables()->eloquent($query)
                ->addIndexColumn()
                ->addColumn('branch', fn ($r) => $r->branch->name ?? '-')
                ->addColumn('created_at', fn ($r) => $r->created_at?->format('d M Y H:i') ?? '-')
                ->addColumn('action', fn ($r) => view('stock_receipts._column_action', ['r' => $r])->render())
                ->rawColumns(['action'])
                ->toJson();
        }

        return view('stock_receipts.index');
    }

    public function create(Request $request)
    {
        return view('stock_receipts.create', [
            'receipt' => new StockReceipt(),
            'branches' => $this->selectableBranches($request),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate($this->rules($request));
        $branchId = $this->resolveBranchId($request, $validated['branch_id']);
        $items = $this->mergeItems($validated['items']);
        $userId = $request->user()->id;

        try {
            $receipt = DB::transaction(function () use ($validated, $branchId, $items, $userId) {
                $receipt = StockReceipt::create([
                    'branch_id' => $branchId,
                    'supplier_name' => $validated['supplier_name'] ?? null,
                    'reference_no' => $validated['reference_no'] ?? null,
                    'notes' => $validated['notes'] ?? null,
                    'status' => $validated['status'],
                    'total_items' => count($items),
                    'created_by' => $userId,
                    'received_at' => $validated['status'] === 'received' ? Carbon::now() : null,
                ]);

                foreach ($items as $variantId => $cents) {
                    StockReceiptItem::create([
                        'receipt_id' => $receipt->id,
                        'variant_id' => $variantId,
                        'quantity_received' => Qty::fromCents($cents),
                    ]);

                    if ($receipt->status === 'received') {
                        $this->receive($receipt, $branchId, $variantId, $cents, $userId, 'receipt');
                    }
                }

                return $receipt;
            });

            return redirect()->route('stock_receipts.show', $receipt->id)->with('success', 'Stok masuk berhasil dibuat.');
        } catch (InventoryException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        } catch (\Throwable $th) {
            Log::error('StockReceipt.store ERROR: ' . $th->getMessage(), ['exception' => $th]);

            return back()->withInput()->with('error', 'Gagal menyimpan stok masuk. Silakan coba lagi.');
        }
    }

    public function show(Request $request, $id)
    {
        $receipt = StockReceipt::visibleTo($request->user())
            ->with(['items.variant.product', 'items.variant.unit', 'branch', 'creator'])
            ->findOrFail($id);

        return view('stock_receipts.show', compact('receipt'));
    }

    // helper search untuk autocomplete varian
    public function variantSearch(Request $request)
    {
        $q = trim($request->input('q', ''));

        $query = ProductVariant::with(['product', 'unit'])->select(['id', 'sku', 'variant_name', 'product_id', 'unit_id']);

        if ($q !== '') {
            $like = '%' . mb_strtolower($q) . '%';
            $query->where(function ($sub) use ($like) {
                $sub->whereRaw('LOWER(sku) LIKE ?', [$like])
                    ->orWhereRaw('LOWER(variant_name) LIKE ?', [$like])
                    ->orWhereHas('product', fn ($p) => $p->whereRaw('LOWER(name) LIKE ?', [$like]));
            });
        }

        $results = $query->limit(20)->get()->map(function ($v) {
            $labelName = $v->variant_name ?? ($v->product->name ?? null);

            return [
                'id' => $v->id,
                'text' => trim($v->sku . ($labelName ? ' — ' . $labelName : '')),
                'sku' => $v->sku,
                'variant_name' => $labelName,
                'unit' => $v->unit->name ?? null,
            ];
        });

        return response()->json(['results' => $results]);
    }

    public function edit(Request $request, int $id)
    {
        $receipt = StockReceipt::visibleTo($request->user())->with('items.variant.product')->findOrFail($id);

        return view('stock_receipts.edit', [
            'receipt' => $receipt,
            'branches' => $this->selectableBranches($request, $receipt->branch_id),
        ]);
    }

    public function update(Request $request, int $id)
    {
        $receipt = StockReceipt::visibleTo($request->user())->with('items')->findOrFail($id);
        $validated = $request->validate($this->rules($request, $receipt->branch_id));
        $newBranchId = $this->resolveBranchId($request, $validated['branch_id']);
        $newItems = $this->mergeItems($validated['items']);
        $userId = $request->user()->id;

        try {
            DB::transaction(function () use ($receipt, $validated, $newBranchId, $newItems, $userId) {
                // Kunci receipt agar tidak diedit bersamaan
                $receipt = StockReceipt::whereKey($receipt->id)->lockForUpdate()->with('items')->firstOrFail();

                $oldBranchId = (int) $receipt->branch_id;
                $oldStatus = $receipt->status;
                $newStatus = $validated['status'];

                $oldItems = [];
                foreach ($receipt->items as $it) {
                    $oldItems[$it->variant_id] = ($oldItems[$it->variant_id] ?? 0) + Qty::toCents($it->quantity_received);
                }

                $wasReceived = $oldStatus === 'received';
                $isReceived = $newStatus === 'received';

                if ($wasReceived && $isReceived && $oldBranchId === $newBranchId) {
                    // Cabang sama: terapkan selisih per varian
                    foreach (array_unique(array_merge(array_keys($oldItems), array_keys($newItems))) as $variantId) {
                        $diff = ($newItems[$variantId] ?? 0) - ($oldItems[$variantId] ?? 0);
                        if ($diff > 0) {
                            $this->receive($receipt, $newBranchId, $variantId, $diff, $userId, 'receipt_adjustment');
                        } elseif ($diff < 0) {
                            $this->reverse($receipt, $oldBranchId, $variantId, -$diff, $userId, 'receipt_adjustment');
                        }
                    }
                } else {
                    // Batalkan seluruh penerimaan lama di CABANG LAMA, lalu terapkan yang baru di cabang baru
                    if ($wasReceived) {
                        foreach ($oldItems as $variantId => $cents) {
                            $this->reverse($receipt, $oldBranchId, $variantId, $cents, $userId, 'receipt_reverse_on_update');
                        }
                    }
                    if ($isReceived) {
                        foreach ($newItems as $variantId => $cents) {
                            $this->receive($receipt, $newBranchId, $variantId, $cents, $userId, 'receipt');
                        }
                    }
                }

                $receipt->update([
                    'branch_id' => $newBranchId,
                    'supplier_name' => $validated['supplier_name'] ?? null,
                    'reference_no' => $validated['reference_no'] ?? null,
                    'notes' => $validated['notes'] ?? null,
                    'status' => $newStatus,
                    'total_items' => count($newItems),
                    'received_at' => $isReceived ? ($receipt->received_at ?? Carbon::now()) : null,
                ]);

                StockReceiptItem::where('receipt_id', $receipt->id)->delete();
                foreach ($newItems as $variantId => $cents) {
                    StockReceiptItem::create([
                        'receipt_id' => $receipt->id,
                        'variant_id' => $variantId,
                        'quantity_received' => Qty::fromCents($cents),
                    ]);
                }
            });

            return redirect()->route('stock_receipts.show', $receipt->id)->with('success', 'Stock receipt berhasil diperbarui.');
        } catch (InventoryException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        } catch (\Throwable $th) {
            Log::error('StockReceipt.update ERROR: ' . $th->getMessage(), ['exception' => $th]);

            return back()->withInput()->with('error', 'Gagal memperbarui stok masuk. Silakan coba lagi.');
        }
    }

    public function destroy(Request $request, int $id)
    {
        $receipt = StockReceipt::visibleTo($request->user())->with('items')->findOrFail($id);
        $userId = $request->user()->id;

        try {
            DB::transaction(function () use ($receipt, $userId) {
                if ($receipt->status === 'received') {
                    foreach ($receipt->items as $it) {
                        $this->reverse($receipt, (int) $receipt->branch_id, $it->variant_id, Qty::toCents($it->quantity_received), $userId, 'receipt_deleted');
                    }
                }

                StockReceiptItem::where('receipt_id', $receipt->id)->delete();
                $receipt->delete();
            });

            return redirect()->route('stock_receipts.index')->with('success', 'Stock receipt berhasil dihapus.');
        } catch (InventoryException $e) {
            return back()->with('error', $e->getMessage());
        } catch (\Throwable $th) {
            Log::error('StockReceipt.destroy ERROR: ' . $th->getMessage(), ['exception' => $th]);

            return back()->with('error', 'Gagal menghapus stock receipt. Silakan coba lagi.');
        }
    }

    // ------------------------------------------------------------------ helpers

    private function rules(Request $request, ?int $currentBranchId = null): array
    {
        return [
            'branch_id' => [
                'required', 'integer',
                Rule::exists('branches', 'id')->whereNull('deleted_at')->where(function ($q) use ($currentBranchId) {
                    // cabang nonaktif hanya boleh dipertahankan bila memang cabang receipt saat ini
                    $q->where(fn ($w) => $w->where('is_active', true)->when($currentBranchId, fn ($x) => $x->orWhere('id', $currentBranchId)));
                }),
            ],
            'supplier_name' => 'nullable|string|max:255',
            'reference_no' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.variant_id' => 'required|integer|exists:product_variants,id',
            'items.*.quantity_received' => 'required|numeric|min:0.01|max:999999999999|decimal:0,2',
            'status' => ['required', Rule::in(['draft', 'received', 'cancelled'])],
        ];
    }

    /** User cabang hanya boleh memakai cabangnya sendiri. */
    private function resolveBranchId(Request $request, $requested): int
    {
        $user = $request->user();
        if (! $user->canAccessBranch((int) $requested)) {
            abort(403, 'Anda tidak memiliki akses ke cabang tersebut.');
        }

        return (int) $requested;
    }

    private function selectableBranches(Request $request, ?int $includeId = null)
    {
        return Branch::accessibleBy($request->user())
            ->where(fn ($q) => $q->where('is_active', true)->when($includeId, fn ($x) => $x->orWhere('id', $includeId)))
            ->orderBy('name')
            ->get();
    }

    /** Gabungkan baris duplikat per varian; hasil: [variant_id => cents]. */
    private function mergeItems(array $items): array
    {
        $merged = [];
        foreach ($items as $it) {
            $variantId = (int) $it['variant_id'];
            $merged[$variantId] = ($merged[$variantId] ?? 0) + Qty::toCents($it['quantity_received']);
        }

        return $merged;
    }

    private function receive(StockReceipt $receipt, int $branchId, int $variantId, int $cents, ?int $userId, string $reason): void
    {
        $this->inventory->applyDelta(
            $branchId, $variantId, Qty::fromCents($cents), $reason, 'receipt', $receipt->public_id, $userId,
            ['note' => 'Stock received via StockReceipt id=' . $receipt->id]
        );
    }

    private function reverse(StockReceipt $receipt, int $branchId, int $variantId, int $cents, ?int $userId, string $reason): void
    {
        $this->inventory->applyDelta(
            $branchId, $variantId, Qty::fromCents(-$cents), $reason, 'receipt', $receipt->public_id, $userId,
            ['note' => 'Reversal for StockReceipt id=' . $receipt->id]
        );
    }
}
