<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class MetaKeywordImportTemplateExport implements FromArray, WithHeadings
{
    public function array(): array
    {
        return [
            ['Galvalum'],
            ['Atap Metal'],
            ['Genteng Metal'],
        ];
    }

    public function headings(): array
    {
        return ['name'];
    }
}
