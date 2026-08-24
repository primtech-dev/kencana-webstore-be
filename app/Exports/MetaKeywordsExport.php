<?php

namespace App\Exports;

use App\Models\MetaKeyword;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class MetaKeywordsExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        return MetaKeyword::orderBy('name')->get()->map(function (MetaKeyword $k) {
            return [
                'name' => $k->name,
                'slug' => $k->slug,
                'is_active' => $k->is_active ? 'Aktif' : 'Non-aktif',
            ];
        });
    }

    public function headings(): array
    {
        return ['Name', 'Slug', 'Status Aktif'];
    }
}
