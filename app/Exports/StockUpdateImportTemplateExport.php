<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

/**
 * Template Import Update Stok. Kolom kode & SKU diformat TEXT agar angka nol di depan tidak hilang.
 */
class StockUpdateImportTemplateExport implements FromArray, WithHeadings, WithColumnFormatting, ShouldAutoSize, WithStrictNullComparison
{
    public function __construct(private string $sampleBranchCode = 'KNC-JKT1')
    {
    }

    public function headings(): array
    {
        return ['branch_code', 'variant_sku', 'new_stock', 'mode', 'note'];
    }

    public function array(): array
    {
        return [
            [$this->sampleBranchCode, 'SKU-001', 25, 'set', 'Stok opname Oktober'],
            [$this->sampleBranchCode, 'SKU-002', 10.5, 'add', 'Tambah dari supplier'],
            [$this->sampleBranchCode, 'SKU-003', 2.25, 'subtract', 'Rusak'],
        ];
    }

    public function columnFormats(): array
    {
        return [
            'A' => NumberFormat::FORMAT_TEXT,
            'B' => NumberFormat::FORMAT_TEXT,
        ];
    }
}
