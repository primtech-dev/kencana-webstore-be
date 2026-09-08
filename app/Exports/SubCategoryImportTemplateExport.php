<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class SubCategoryImportTemplateExport implements FromArray, WithHeadings
{
    public function array(): array
    {
        return [
            ['Genteng Metal', 'Atap Metal'],
            ['Galvalum Lembaran', 'Atap Metal'],
            ['Paku Baja', 'Aksesoris'],
        ];
    }

    public function headings(): array
    {
        return ['name', 'parent_category'];
    }
}
