@extends('layouts.vertical', ['title' => 'Riwayat Import Stok'])

@section('styles')
    @vite(['node_modules/datatables.net-responsive-bs5/css/responsive.bootstrap5.min.css'])
@endsection

@section('content')
    @include('layouts.shared.page-title', [
        'title' => 'Riwayat Import Stok',
        'subTitle' => 'Daftar seluruh import update stok',
        'breadcrumbs' => [['name' => 'Persediaan', 'url' => route('inventory.index')], ['name' => 'Import Stok']]
    ])

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">Daftar Import</h5>
            <a href="{{ route('stock_imports.form') }}" class="btn btn-primary">
                <i data-lucide="upload" class="me-1"></i> Import Baru
            </a>
        </div>
        <div class="card-body">
            <div class="row g-2 mb-3" id="importFilters">
                <div class="col-6 col-md-3">
                    <label class="form-label small mb-1">Sumber</label>
                    <select id="im_source" class="form-select form-select-sm">
                        <option value="">Semua</option>
                        <option value="odoo">Odoo</option>
                        <option value="template">Template</option>
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label small mb-1">Dari tanggal</label>
                    <input type="date" id="im_date_from" class="form-control form-control-sm">
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label small mb-1">Sampai tanggal</label>
                    <input type="date" id="im_date_to" class="form-control form-control-sm">
                </div>
                <div class="col-6 col-md-2 d-flex align-items-end">
                    <button type="button" id="im_reset" class="btn btn-sm btn-outline-secondary">Reset</button>
                </div>
            </div>

            <table class="table table-striped dt-responsive align-middle w-100" id="imports-table">
                <thead class="thead-sm text-uppercase fs-xxs">
                <tr>
                    <th>No</th>
                    <th>Waktu</th>
                    <th>Oleh</th>
                    <th>Sumber</th>
                    <th>File</th>
                    <th>Cabang</th>
                    <th class="text-end">Diubah</th>
                    <th class="text-end">Dinolkan</th>
                    <th class="text-end">Dilewati</th>
                    <th class="text-center">Aksi</th>
                </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
@endsection

@section('scripts')
    @vite(['resources/js/pages/stock-imports/history.js'])
    <script>
        window.importRoutes = { history: '{{ route('stock_imports.history') }}' };
    </script>
@endsection
