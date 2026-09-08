<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ProductImportTemplateExport implements FromArray, WithHeadings
{
    public function array(): array
    {
        return [
            [
                'SKU-001', 'Genteng Metal Sakura',
                'Genteng metal ringan dan tahan karat', 'Genteng metal berkualitas tinggi, cocok untuk atap rumah tinggal maupun ruko',
                5000, 'Pcs', 'online',
                'Atap Metal', 'Genteng Metal|Galvalum Lembaran', 'genteng,atap metal,genteng ringan',
                'SKU-001-A', 'Merah', 75000, 80, 76, 0.3, 1,
                'genteng-metal-sakura.jpg', 'genteng-metal-sakura-merah.jpg',
            ],
            [
                'SKU-001', 'Genteng Metal Sakura',
                'Genteng metal ringan dan tahan karat', 'Genteng metal berkualitas tinggi, cocok untuk atap rumah tinggal maupun ruko',
                5000, 'Pcs', 'online',
                'Atap Metal', 'Genteng Metal|Galvalum Lembaran', 'genteng,atap metal,genteng ringan',
                'SKU-001-B', 'Hijau', 75000, 80, 76, 0.3, 1,
                '', 'genteng-metal-sakura-hijau.jpg',
            ],
            [
                'SKU-002', 'Paku Baja Anti Karat',
                'Paku baja untuk pemasangan atap metal', 'Paku baja anti karat, tahan lama untuk berbagai kebutuhan konstruksi atap',
                1000, 'Pack', 'manual',
                'Aksesoris', 'Paku Baja', 'paku,aksesoris atap',
                'SKU-002-A', '5 cm', 15000, null, null, null, 1,
                'paku-baja.jpg', '',
            ],
        ];
    }

    public function headings(): array
    {
        return [
            'product_sku', 'product_name',
            'short_description', 'description',
            'weight_gram', 'unit', 'purchase_type',
            'categories', 'sub_categories', 'meta_keyword',
            'variant_sku', 'variant_name', 'price', 'length', 'width', 'height', 'is_active',
            'product_images', 'variant_images',
        ];
    }
}
