@extends('layouts.vertical', ['title' => 'Import Update Stok'])

@section('content')
    @include('layouts.shared.page-title', [
        'title' => 'Import Update Stok',
        'subTitle' => 'Perbarui stok banyak varian sekaligus dari Excel',
        'breadcrumbs' => [
            ['name' => 'Persediaan', 'url' => route('stock_receipts.index')],
            ['name' => 'Import Update Stok']
        ]
    ])

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Import Update Stok (Excel)</h5>
        </div>
        <div class="card-body">

            <div class="card mb-3">
                <div class="card-body d-flex flex-wrap gap-2">
                    <a href="{{ route('stock_imports.template') }}" class="btn btn-outline-primary">
                        <i data-lucide="download" class="me-1"></i> Download Template Kosong
                    </a>
                    <a href="{{ route('stock_imports.export') }}" class="btn btn-outline-success">
                        <i data-lucide="file-spreadsheet" class="me-1"></i> Download Stok Saat Ini (siap diedit)
                    </a>
                </div>
            </div>

            <div class="alert alert-info mb-4">
                <ul class="mb-0">
                    <li>Kolom: <strong>branch_code</strong>, <strong>variant_sku</strong>, <strong>new_stock</strong>, <strong>mode</strong> (opsional), <strong>note</strong> (opsional).
                        @unless(auth()->user()->hasAllBranchScope())
                            Kolom <em>branch_code</em> boleh dikosongkan: otomatis memakai cabang Anda.
                        @endunless
                    </li>
                    <li><strong>mode</strong>: <code>set</code> (default) = stok akhir; <code>add</code> = tambah; <code>subtract</code> = kurangi.</li>
                    <li>Stok boleh desimal, maksimal 2 angka di belakang koma (contoh <code>12.25</code> atau <code>12,25</code>).</li>
                    <li>Sistem menampilkan <strong>preview</strong> (stok sekarang &rarr; stok baru) sebelum disimpan. Satu baris error akan memblokir seluruh import.</li>
                    <li>Setiap perubahan dicatat sebagai riwayat stok (alasan <code>import_adjustment</code>).</li>
                </ul>
            </div>

            <form method="POST" action="{{ route('stock_imports.preview') }}" enctype="multipart/form-data">
                @csrf

                <div class="mb-3">
                    <label class="form-label">File Excel Update Stok</label>
                    <input type="file" name="excel" accept=".xlsx,.xls" class="form-control @error('excel') is-invalid @enderror" required>
                    @error('excel')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <button class="btn btn-primary">Preview Import</button>
                <a href="{{ route('stock_receipts.index') }}" class="btn btn-outline-secondary">Kembali</a>
            </form>
        </div>
    </div>
@endsection
