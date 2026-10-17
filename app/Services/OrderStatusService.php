<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Exceptions\OrderStatusException;
use App\Models\Order;
use App\Models\OrderStatusHistory;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Satu-satunya pintu perubahan status order.
 *
 * - Order dikunci (FOR UPDATE) sebelum status dibaca -> aman dari klik ganda / request paralel.
 * - Transisi divalidasi lewat OrderStatus::allowedNext().
 * - Efek stok mengikuti transisi dan di-rollback bersama perubahan status bila gagal:
 *     -> cancelled : lepas reservasi (hanya mungkin sebelum dikirim)
 *     -> shipped   : kurangi on_hand + reserved
 *     -> return    : stok kembali (on_hand bertambah)
 */
class OrderStatusService
{
    public function __construct(private InventoryService $inventory)
    {
    }

    public function changeStatus(int $orderId, OrderStatus $to, User $actor, ?string $note = null): Order
    {
        return DB::transaction(function () use ($orderId, $to, $actor, $note) {
            /** @var Order $order */
            $order = Order::whereKey($orderId)->lockForUpdate()->firstOrFail();

            $from = OrderStatus::tryFrom((string) $order->status);
            if (! $from) {
                throw new OrderStatusException("Status order saat ini ('{$order->status}') tidak dikenali.");
            }

            $note = $note !== null ? trim($note) : null;

            // Status sama: hanya menambah catatan (tidak ada efek stok / riwayat status)
            if ($from === $to) {
                if ($note === null || $note === '') {
                    throw new OrderStatusException('Order sudah berstatus ' . $to->label() . '.');
                }
                $order->notes = $this->appendNote($order->notes, $actor, $note);
                $order->save();

                return $order;
            }

            if (! $from->canTransitionTo($to)) {
                throw new OrderStatusException(sprintf(
                    'Status tidak dapat diubah dari %s ke %s.', $from->label(), $to->label()
                ));
            }

            match ($to) {
                OrderStatus::Cancelled => $this->inventory->unreserveOrder($order, $actor->id),
                OrderStatus::Shipped => $this->inventory->shipOrder($order, $actor->id),
                OrderStatus::Return => $this->inventory->restockOrder($order, $actor->id),
                default => null,
            };

            $order->status = $to->value;

            if ($to === OrderStatus::Cancelled) {
                $order->cancelled_at = now();
            }
            if ($to === OrderStatus::Paid) {
                $order->payment_status = 'paid';
            }
            if ($to === OrderStatus::Return && $order->payment_status === 'paid') {
                $order->payment_status = 'refunded';
                $order->is_refunded = true;
            }
            if ($note !== null && $note !== '') {
                $order->notes = $this->appendNote($order->notes, $actor, $note);
            }
            $order->save();

            OrderStatusHistory::create([
                'order_id' => $order->id,
                'from_status' => $from->value,
                'to_status' => $to->value,
                'user_id' => $actor->id,
                'note' => $note ?: null,
                'created_at' => now(),
            ]);

            return $order;
        });
    }

    /**
     * Hapus (soft delete) order. Hanya untuk order yang tidak punya reservasi/stok keluar aktif:
     * pending (reservasi dilepas dulu) dan cancelled.
     */
    public function delete(int $orderId, User $actor): void
    {
        DB::transaction(function () use ($orderId, $actor) {
            $order = Order::whereKey($orderId)->lockForUpdate()->firstOrFail();
            $status = OrderStatus::tryFrom((string) $order->status);

            if (! in_array($status, [OrderStatus::Pending, OrderStatus::Cancelled], true)) {
                throw new OrderStatusException('Hanya order berstatus Pending atau Dibatalkan yang dapat dihapus. Batalkan order terlebih dahulu.');
            }

            if ($status === OrderStatus::Pending) {
                $this->inventory->unreserveOrder($order, $actor->id);
                $order->status = OrderStatus::Cancelled->value;
                $order->cancelled_at = now();
                $order->save();

                OrderStatusHistory::create([
                    'order_id' => $order->id,
                    'from_status' => OrderStatus::Pending->value,
                    'to_status' => OrderStatus::Cancelled->value,
                    'user_id' => $actor->id,
                    'note' => 'Order dihapus oleh ' . $actor->name,
                    'created_at' => now(),
                ]);
            }

            $order->delete();
        });
    }

    private function appendNote(?string $existing, User $actor, string $note): string
    {
        $line = now()->format('Y-m-d H:i') . ' - ' . $actor->name . ': ' . $note;

        return trim(($existing ? $existing . "\n\n" : '') . $line);
    }
}
