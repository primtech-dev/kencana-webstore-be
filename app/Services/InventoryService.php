<?php

namespace App\Services;

use App\Exceptions\InventoryException;
use App\Models\Order;
use App\Models\StockMovement;
use App\Support\Qty;
use Illuminate\Support\Facades\DB;

/**
 * Semua perubahan stok (decimal 2 digit) melalui service ini.
 *
 * - Setiap method membuka transaksi sendiri (savepoint bila dipanggil di dalam transaksi).
 * - Baris inventory dikunci (FOR UPDATE) sebelum dihitung ulang.
 * - Kegagalan domain dilempar sebagai InventoryException (pesan aman untuk pengguna);
 *   tidak ada lagi kegagalan yang diam-diam dilewati.
 */
class InventoryService
{
    /**
     * Tambah / kurangi on_hand sebesar $delta (bisa negatif) dan catat StockMovement.
     */
    public function applyDelta(
        int $branchId,
        int $variantId,
        string|int|float $delta,
        string $reason,
        ?string $referenceType = null,
        ?string $referenceId = null,
        ?int $performedBy = null,
        array $metadata = [],
    ): StockMovement {
        return DB::transaction(function () use ($branchId, $variantId, $delta, $reason, $referenceType, $referenceId, $performedBy, $metadata) {
            $deltaCents = Qty::toCents($delta);
            $row = $this->lockRow($branchId, $variantId, create: $deltaCents > 0);

            if (! $row) {
                throw new InventoryException("Stok varian #{$variantId} di cabang #{$branchId} belum ada.");
            }

            $newOnHand = Qty::toCents($row->on_hand) + $deltaCents;
            if ($newOnHand < 0) {
                throw new InventoryException(sprintf(
                    'Stok tidak cukup (varian #%d, cabang #%d): tersedia %s, dibutuhkan %s.',
                    $variantId, $branchId, Qty::fmt($row->on_hand), Qty::fmt(Qty::fromCents(abs($deltaCents)))
                ));
            }

            $this->writeRow($row, $newOnHand, Qty::toCents($row->reserved));

            return $this->recordMovement(
                $branchId, $variantId, Qty::fromCents($deltaCents), Qty::fromCents($newOnHand),
                $reason, $referenceType, $referenceId, $performedBy, $metadata
            );
        });
    }

    /**
     * Set stok absolut. Mengembalikan movement, atau null bila tidak ada perubahan.
     * Ditolak bila stok baru lebih kecil dari reserved, kecuali $allowBelowReserved
     * (mis. sinkron dari Odoo: stok fisik adalah acuan, available dipotong jadi 0).
     */
    public function setStock(
        int $branchId,
        int $variantId,
        string|int|float $newOnHand,
        string $reason,
        ?string $referenceType = null,
        ?string $referenceId = null,
        ?int $performedBy = null,
        array $metadata = [],
        bool $allowBelowReserved = false,
    ): ?StockMovement {
        return DB::transaction(function () use ($branchId, $variantId, $newOnHand, $reason, $referenceType, $referenceId, $performedBy, $metadata, $allowBelowReserved) {
            $targetCents = Qty::toCents($newOnHand);
            if ($targetCents < 0) {
                throw new InventoryException('Stok tidak boleh negatif.');
            }

            $row = $this->lockRow($branchId, $variantId, create: true);
            $currentCents = Qty::toCents($row->on_hand);
            $reservedCents = Qty::toCents($row->reserved);

            if ($targetCents < $reservedCents && ! $allowBelowReserved) {
                throw new InventoryException(sprintf(
                    'Stok baru (%s) lebih kecil dari stok yang sudah dipesan/reserved (%s).',
                    Qty::fmt(Qty::fromCents($targetCents)), Qty::fmt(Qty::fromCents($reservedCents))
                ));
            }

            if ($targetCents === $currentCents) {
                return null;
            }

            $this->writeRow($row, $targetCents, $reservedCents);

            return $this->recordMovement(
                $branchId, $variantId, Qty::fromCents($targetCents - $currentCents), Qty::fromCents($targetCents),
                $reason, $referenceType, $referenceId, $performedBy,
                $metadata + ['previous_on_hand' => Qty::fromCents($currentCents)]
            );
        });
    }

    /**
     * Lepas reservasi stok (order dibatalkan sebelum dikirim). on_hand tidak berubah.
     */
    public function unreserveOrder(Order $order, ?int $performedBy = null): void
    {
        DB::transaction(function () use ($order, $performedBy) {
            foreach ($this->sortedItems($order) as $item) {
                $row = $this->lockRow($order->branch_id, $item->variant_id, create: false);
                if (! $row) {
                    throw new InventoryException("Baris stok untuk item \"{$item->product_name}\" tidak ditemukan di cabang order.");
                }

                $reserved = Qty::toCents($row->reserved);
                $release = min($reserved, Qty::toCents($item->line_quantity));
                $onHand = Qty::toCents($row->on_hand);

                $this->writeRow($row, $onHand, $reserved - $release);

                $this->recordMovement(
                    $order->branch_id, $item->variant_id, '0.00', Qty::fromCents($onHand),
                    'order_unreserved', 'order', $order->public_id, $performedBy,
                    ['order_id' => $order->id, 'order_item_id' => $item->id, 'released' => Qty::fromCents($release)]
                );
            }
        });
    }

    /**
     * Kirim barang: kurangi on_hand dan reserved. Gagal bila stok fisik tidak cukup.
     */
    public function shipOrder(Order $order, ?int $performedBy = null): void
    {
        DB::transaction(function () use ($order, $performedBy) {
            foreach ($this->sortedItems($order) as $item) {
                $qty = Qty::toCents($item->line_quantity);
                $row = $this->lockRow($order->branch_id, $item->variant_id, create: false);
                if (! $row) {
                    throw new InventoryException("Baris stok untuk item \"{$item->product_name}\" tidak ditemukan di cabang order.");
                }

                $onHand = Qty::toCents($row->on_hand);
                if ($onHand < $qty) {
                    throw new InventoryException(sprintf(
                        'Stok "%s" tidak cukup untuk dikirim: tersedia %s, dibutuhkan %s.',
                        $item->product_name, Qty::fmt(Qty::fromCents($onHand)), Qty::fmt(Qty::fromCents($qty))
                    ));
                }

                $newOnHand = $onHand - $qty;
                $newReserved = max(0, Qty::toCents($row->reserved) - $qty);
                $this->writeRow($row, $newOnHand, $newReserved);

                $this->recordMovement(
                    $order->branch_id, $item->variant_id, Qty::fromCents(-$qty), Qty::fromCents($newOnHand),
                    'sale_shipment', 'order', $order->public_id, $performedBy,
                    ['order_id' => $order->id, 'order_item_id' => $item->id]
                );
            }
        });
    }

    /**
     * Barang kembali ke stok (retur / pembatalan setelah dikirim): tambah on_hand.
     */
    public function restockOrder(Order $order, ?int $performedBy = null): void
    {
        DB::transaction(function () use ($order, $performedBy) {
            foreach ($this->sortedItems($order) as $item) {
                $this->applyDelta(
                    $order->branch_id, $item->variant_id, $item->line_quantity,
                    'order_return', 'order', $order->public_id, $performedBy,
                    ['order_id' => $order->id, 'order_item_id' => $item->id]
                );
            }
        });
    }

    // ------------------------------------------------------------------ internals

    /** Item order yang punya varian, urut per variant_id agar urutan lock konsisten (anti-deadlock). */
    private function sortedItems(Order $order)
    {
        $order->loadMissing('items');

        foreach ($order->items as $item) {
            if (! $item->variant_id) {
                throw new InventoryException("Item \"{$item->product_name}\" tidak terhubung ke varian produk (varian sudah dihapus).");
            }
        }

        return $order->items->sortBy('variant_id')->values();
    }

    /**
     * Ambil baris inventory dengan FOR UPDATE. Bila $create, baris dibuat dulu (race-safe)
     * atau dipulihkan bila sebelumnya soft-deleted.
     */
    private function lockRow(int $branchId, int $variantId, bool $create): ?object
    {
        if ($create) {
            DB::table('inventory')->insertOrIgnore([
                'variant_id' => $variantId,
                'branch_id' => $branchId,
                'on_hand' => 0,
                'available' => 0,
                'reserved' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $row = DB::table('inventory')
            ->where('variant_id', $variantId)
            ->where('branch_id', $branchId)
            ->lockForUpdate()
            ->first();

        if ($row && $row->deleted_at !== null) {
            if (! $create) {
                return null;
            }
            DB::table('inventory')->where('id', $row->id)->update([
                'deleted_at' => null, 'on_hand' => 0, 'available' => 0, 'reserved' => 0, 'updated_at' => now(),
            ]);
            $row->on_hand = $row->available = $row->reserved = '0.00';
            $row->deleted_at = null;
        }

        return $row ?: null;
    }

    private function writeRow(object $row, int $onHandCents, int $reservedCents): void
    {
        DB::table('inventory')->where('id', $row->id)->update([
            'on_hand' => Qty::fromCents($onHandCents),
            'reserved' => Qty::fromCents($reservedCents),
            'available' => Qty::fromCents(max(0, $onHandCents - $reservedCents)),
            'updated_at' => now(),
        ]);
    }

    private function recordMovement(
        int $branchId, int $variantId, string $change, string $resultingOnHand,
        string $reason, ?string $referenceType, ?string $referenceId, ?int $performedBy, array $metadata
    ): StockMovement {
        return StockMovement::create([
            'variant_id' => $variantId,
            'branch_id' => $branchId,
            'change' => $change,
            'resulting_on_hand' => $resultingOnHand,
            'reason' => $reason,
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
            'performed_by' => $performedBy,
            'metadata' => $metadata ?: null,
            'created_at' => now(),
        ]);
    }
}
