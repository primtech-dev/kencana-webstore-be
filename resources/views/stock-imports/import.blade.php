@extends('layouts.vertical', ['title' => 'Import Update Stok'])

@section('content')
    @include('layouts.shared.page-title', [
        'title' => 'Import Update Stok',
        'subTitle' => 'Samakan stok backoffice dengan export stok Odoo',
        'breadcrumbs' => [
            ['name' => 'Persediaan', 'url' => route('stock_receipts.index')],
            ['name' => 'Import Update Stok']
        ]
    ])

    <div class="row">
        <div class="col-lg-7">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Import Stok dari Odoo</h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <strong>Cara pakai:</strong> di Odoo, export <em>Quants (stock.quant)</em> ke Excel, lalu unggah file tersebut apa adanya
                        (kolom: <code>Location, Warehouse, Product, Product Category, Available Quantity, Unit of Measure</code>).
                        <ul class="mb-0 mt-2 small">
                            <li>Hanya lokasi <code>.../Stock</code> yang dipakai. Tinting, Scrap, dan Inventaris diabaikan.</li>
                            <li><strong>Warehouse</strong> dicocokkan dengan nama cabang; kode dalam kurung siku <code>[100103010003]</code> dicocokkan dengan <strong>SKU varian</strong>.</li>
                            <li>Baris yang tidak cocok (gudang/produk tidak dikenal, produk tanpa kode) dilewati dan ditampilkan sebagai peringatan, tidak memblokir import.</li>
                            <li>Stok negatif di Odoo dianggap 0. Beberapa baris produk yang sama di satu cabang dijumlahkan.</li>
                            <li>Anda akan melihat <strong>preview</strong> sebelum data disimpan. Semua perubahan tercatat di riwayat stok.</li>
                        </ul>
                    </div>

                    <form method="POST" action="{{ route('stock_imports.preview') }}" enctype="multipart/form-data">
                        @csrf

                        <div class="mb-3">
                            <label class="form-label">File Excel export Odoo</label>
                            <input type="file" name="excel" accept=".xlsx,.xls" class="form-control @error('excel') is-invalid @enderror" required>
                            @error('excel')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="form-check mb-3">
                            <input type="hidden" name="zero_missing" value="0">
                            <input class="form-check-input" type="checkbox" name="zero_missing" value="1" id="zeroMissing" checked>
                            <label class="form-check-label" for="zeroMissing">
                                Nolkan stok produk yang <strong>tidak ada di file</strong> (khusus cabang yang ada di file)
                            </label>
                            <div class="form-text">
                                Odoo biasanya tidak mengekspor produk berstok 0, jadi produk yang hilang dari file dianggap habis.
                                Matikan bila file yang diunggah hanya sebagian data. Jumlah yang akan dinolkan ditampilkan di preview.
                            </div>
                        </div>

                        <button class="btn btn-primary">Preview Import</button>
                        <a href="{{ route('stock_receipts.index') }}" class="btn btn-outline-secondary">Kembali</a>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-header"><h6 class="mb-0">Format lain: template per SKU</h6></div>
                <div class="card-body small">
                    <p class="mb-2">Untuk koreksi manual per produk. Kolom: <code>branch_code</code>, <code>variant_sku</code>, <code>new_stock</code>,
                        <code>mode</code> (<code>set</code> / <code>add</code> / <code>subtract</code>, opsional), <code>note</code>. File ini diunggah di form yang sama; formatnya dikenali otomatis.</p>
                    <a href="{{ route('stock_imports.template') }}" class="btn btn-sm btn-outline-primary">
                        <i data-lucide="download" class="me-1"></i> Template Kosong
                    </a>
                    <a href="{{ route('stock_imports.export') }}" class="btn btn-sm btn-outline-success">
                        <i data-lucide="file-spreadsheet" class="me-1"></i> Stok Saat Ini (siap diedit)
                    </a>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center"><h6 class="mb-0">Riwayat Import Terakhir</h6><a href="{{ route('stock_imports.history') }}" class="btn btn-sm btn-outline-primary">Lihat semua</a></div>
                <div class="table-responsive">
                    <table class="table table-sm mb-0 align-middle">
                        <thead class="table-light">
                        <tr><th>Waktu</th><th>Oleh</th><th>Sumber</th><th class="text-end">Diubah</th><th></th></tr>
                        </thead>
                        <tbody>
                        @forelse($logs as $log)
                            <tr>
                                <td class="small">{{ $log->created_at?->format('d M Y H:i') }}</td>
                                <td class="small">{{ $log->user->name ?? '-' }}</td>
                                <td><span class="badge bg-{{ $log->source === 'odoo' ? 'primary' : 'secondary' }}">{{ $log->source === 'odoo' ? 'Odoo' : 'Template' }}</span></td>
                                <td class="text-end small" title="{{ $log->file_name }}">
                                    {{ number_format($log->rows_changed, 0, ',', '.') }}
                                    @if($log->rows_zeroed)<span class="text-muted">({{ number_format($log->rows_zeroed, 0, ',', '.') }} dinolkan)</span>@endif
                                </td>
                                <td><a href="{{ route('stock_imports.show', $log->batch_id) }}" class="small">Detail</a></td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-muted small">Belum ada import.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
