<?php

namespace App\Exports;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

/**
 * Stok saat ini per (cabang aktif x varian), sudah dalam format import:
 * ubah kolom new_stock lalu upload kembali. Baris tanpa perubahan akan dilewati.
 */
class StockSnapshotExport implements FromQuery, WithHeadings, WithMapping, WithColumnFormatting, ShouldAutoSize, WithStrictNullComparison
{
    public function __construct(private User $user)
    {
    }

    public function query()
    {
        $q = DB::table('product_variants as v')
            ->crossJoin('branches as b')
            ->leftJoin('products as p', 'p.id', '=', 'v.product_id')
            ->leftJoin('units as u', 'u.id', '=', 'v.unit_id')
            ->leftJoin('inventory as i', function ($join) {
                $join->on('i.variant_id', '=', 'v.id')
                    ->on('i.branch_id', '=', 'b.id')
                    ->whereNull('i.deleted_at');
            })
            ->whereNull('v.deleted_at')
            ->whereNull('b.deleted_at')
            ->where('b.is_active', true)
            ->select([
                'b.code as branch_code', 'v.sku as variant_sku',
                'p.name as product_name', 'v.variant_name', 'u.name as unit_name',
                DB::raw('COALESCE(i.on_hand, 0) as on_hand'),
            ])
            ->orderBy('b.code')
            ->orderBy('v.sku');

        if (! $this->user->hasAllBranchScope()) {
            $q->where('b.id', (int) $this->user->branch_id);
        }

        return $q;
    }

    public function headings(): array
    {
        return ['branch_code', 'variant_sku', 'new_stock', 'mode', 'note', 'product_name', 'variant_name', 'unit', 'current_stock'];
    }

    public function map($row): array
    {
        $stock = (float) $row->on_hand;

        return [
            (string) $row->branch_code,
            (string) $row->variant_sku,
            $stock,
            'set',
            '',
            $row->product_name,
            $row->variant_name,
            $row->unit_name,
            $stock,
        ];
    }

    public function columnFormats(): array
    {
        return [
            'A' => NumberFormat::FORMAT_TEXT,
            'B' => NumberFormat::FORMAT_TEXT,
            'C' => '0.00',
            'I' => '0.00',
        ];
    }
}
