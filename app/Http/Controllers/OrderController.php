<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatus;
use App\Exceptions\InventoryException;
use App\Exceptions\OrderStatusException;
use App\Models\Branch;
use App\Models\Order;
use App\Services\OrderStatusService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class OrderController extends Controller
{
    private const PAYMENT_STATUSES = ['pending', 'paid', 'failed', 'refunded'];

    public function __construct(private OrderStatusService $statusService)
    {
    }

    public function index(Request $request)
    {
        $user = $request->user();

        if ($request->ajax()) {
            $query = Order::visibleTo($user)
                ->select([
                    'orders.id', 'orders.public_id', 'orders.order_no', 'orders.customer_id', 'orders.branch_id',
                    'orders.status', 'orders.total_amount', 'orders.payment_status', 'orders.created_at',
                ])
                ->with(['customer:id,full_name', 'branch:id,name']);

            $filters = $request->validate([
                'status' => ['nullable', Rule::in(OrderStatus::values())],
                'payment_status' => ['nullable', Rule::in(self::PAYMENT_STATUSES)],
                'branch_id' => ['nullable', 'integer'],
                'date_from' => ['nullable', 'date'],
                'date_to' => ['nullable', 'date'],
            ]);

            if (! empty($filters['status'])) {
                $query->where('orders.status', $filters['status']);
            }
            if (! empty($filters['payment_status'])) {
                $query->where('orders.payment_status', $filters['payment_status']);
            }
            // Filter cabang hanya berlaku untuk pusat; user cabang sudah dibatasi oleh visibleTo()
            if (! empty($filters['branch_id']) && $user->hasAllBranchScope()) {
                $query->where('orders.branch_id', (int) $filters['branch_id']);
            }
            if (! empty($filters['date_from'])) {
                $query->where('orders.created_at', '>=', $filters['date_from'] . ' 00:00:00');
            }
            if (! empty($filters['date_to'])) {
                $query->where('orders.created_at', '<=', $filters['date_to'] . ' 23:59:59');
            }

            return datatables()->eloquent($query)
                ->addIndexColumn()
                // Pencarian global menggantikan auto-filter yajra (yang meng-AND semua kolom)
                ->filter(function ($q) use ($request) {
                    $keyword = trim((string) $request->input('search.value'));
                    if ($keyword === '') {
                        return;
                    }
                    $like = '%' . mb_strtolower($keyword) . '%';
                    $q->where(function ($w) use ($like) {
                        $w->whereRaw('LOWER(orders.order_no) LIKE ?', [$like])
                            ->orWhereRaw('LOWER(CAST(orders.public_id AS TEXT)) LIKE ?', [$like])
                            ->orWhereHas('customer', fn ($c) => $c->whereRaw('LOWER(full_name) LIKE ?', [$like]));
                    });
                }, true)
                ->addColumn('customer', fn (Order $o) => $o->customer ? e($o->customer->full_name) : '-')
                ->addColumn('branch', fn (Order $o) => $o->branch ? e($o->branch->name) : '-')
                ->editColumn('total_amount', fn (Order $o) => 'Rp ' . number_format($o->total_amount, 0, ',', '.'))
                ->orderColumn('total_amount', fn ($q, $order) => $q->orderBy('orders.total_amount', $order))
                ->editColumn('status', function (Order $o) {
                    return '<span class="badge bg-' . OrderStatus::badgeFor($o->status) . '">'
                        . e(OrderStatus::labelFor($o->status)) . '</span>';
                })
                ->orderColumn('status', fn ($q, $order) => $q->orderBy('orders.status', $order))
                ->editColumn('created_at', fn (Order $o) => $o->created_at ? $o->created_at->format('d M Y H:i') : '-')
                ->orderColumn('created_at', fn ($q, $order) => $q->orderBy('orders.created_at', $order))
                ->addColumn('action', fn (Order $o) => view('orders._column_action', ['order' => $o])->render())
                ->rawColumns(['action', 'status'])
                ->toJson();
        }

        return view('orders.index', [
            'statuses' => OrderStatus::labels(),
            'paymentStatuses' => self::PAYMENT_STATUSES,
            'branches' => $user->hasAllBranchScope() ? Branch::orderBy('name')->get(['id', 'name']) : collect(),
            'showBranch' => $user->hasAllBranchScope(),
        ]);
    }

    public function show(Request $request, int $id)
    {
        $order = Order::visibleTo($request->user())
            ->with(['items.unit', 'customer', 'branch', 'paymentMethod', 'address', 'statusHistories.user'])
            ->findOrFail($id);

        return view('orders.show', compact('order'));
    }

    /**
     * Ubah status (hanya transisi yang diizinkan; efek stok ikut transaksi).
     */
    public function updateStatus(Request $request, int $id)
    {
        $order = Order::visibleTo($request->user())->findOrFail($id);

        $validator = Validator::make($request->all(), [
            'status' => ['required', Rule::in(OrderStatus::values())],
            'notes' => 'nullable|string|max:1000',
        ], ['status.in' => 'Status tidak valid', 'status.required' => 'Status wajib dipilih']);

        if ($validator->fails()) {
            return redirect()->back()->with('error', $validator->errors()->first());
        }

        $target = OrderStatus::from($validator->validated()['status']);

        // Izin per-aksi
        $needed = match ($target) {
            OrderStatus::Cancelled => 'orders.cancel',
            OrderStatus::Shipped => 'orders.ship',
            default => null,
        };
        if ($needed && ! $request->user()->can($needed)) {
            return redirect()->back()->with('error', 'Anda tidak memiliki izin untuk aksi ini (' . $needed . ').');
        }

        try {
            $this->statusService->changeStatus($order->id, $target, $request->user(), $validator->validated()['notes'] ?? null);

            return redirect()->route('admin.orders.show', $order->id)->with('success', 'Status order berhasil diperbarui');
        } catch (OrderStatusException|InventoryException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        } catch (\Throwable $th) {
            Log::error('Order.updateStatus failed', ['order_id' => $id, 'error' => $th->getMessage()]);

            return redirect()->back()->with('error', 'Gagal memperbarui status order. Silakan coba lagi.');
        }
    }

    public function destroy(Request $request, int $id)
    {
        $order = Order::visibleTo($request->user())->findOrFail($id);

        if (! $request->user()->can('orders.delete')) {
            return redirect()->back()->with('error', 'Anda tidak memiliki izin untuk menghapus order.');
        }

        try {
            $this->statusService->delete($order->id, $request->user());

            return redirect()->route('admin.orders.index')->with('success', 'Order berhasil dihapus');
        } catch (OrderStatusException|InventoryException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        } catch (\Throwable $th) {
            Log::error('Order.destroy failed', ['order_id' => $id, 'error' => $th->getMessage()]);

            return redirect()->back()->with('error', 'Gagal menghapus order. Silakan coba lagi.');
        }
    }

    public function print(Request $request, int $id)
    {
        $order = Order::visibleTo($request->user())
            ->with(['items.unit', 'customer', 'branch', 'paymentMethod', 'address'])
            ->findOrFail($id);

        // view khusus print (tanpa layout sidebar)
        return view('orders.print', compact('order'));
    }
}
