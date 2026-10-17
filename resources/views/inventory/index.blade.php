@extends('layouts.vertical', ['title' => 'Stok'])

@section('styles')
    @vite(['node_modules/datatables.net-responsive-bs5/css/responsive.bootstrap5.min.css'])
@endsection

@section('content')
    @include('layouts.shared.page-title', [
        'title' => 'Stok Saat Ini',
        'subTitle' => $showBranch ? 'Posisi stok seluruh cabang' : 'Posisi stok cabang Anda',
        'breadcrumbs' => [['name' => 'Persediaan', 'url' => '#'], ['name' => 'Stok']]
    ])

    <div class="card">
        <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div>
                <h5 class="card-title mb-0">Daftar Stok</h5>
                @if($lastOdoo)
                    <small class="text-muted">Sinkron Odoo terakhir: {{ $lastOdoo->created_at?->format('d M Y H:i') }} oleh {{ $lastOdoo->user->name ?? '-' }}</small>
                @endif
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('inventory.movements') }}" class="btn btn-sm btn-outline-primary">Riwayat Stok</a>
                @can('stock_imports.create')
                    <a href="{{ route('stock_imports.form') }}" class="btn btn-sm btn-primary">Import Update Stok</a>
                @endcan
            </div>
        </div>
        <div class="card-body">
            <div class="row g-2 mb-3" id="stockFilters">
                @if($showBranch)
                    <div class="col-6 col-md-3">
                        <label class="form-label small mb-1">Cabang</label>
                        <select id="st_branch" class="form-select form-select-sm">
                            <option value="">Semua cabang</option>
                            @foreach($branches as $b)
                                <option value="{{ $b->id }}">{{ $b->name }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif
                <div class="col-6 col-md-3">
                    <label class="form-label small mb-1">Status stok</label>
                    <select id="st_status" class="form-select form-select-sm">
                        <option value="">Semua</option>
                        @foreach($statuses as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-2 d-flex align-items-end">
                    <button type="button" id="st_reset" class="btn btn-sm btn-outline-secondary">Reset</button>
                </div>
            </div>

            <table class="table table-striped dt-responsive align-middle w-100" id="stock-table">
                <thead class="thead-sm text-uppercase fs-xxs">
                <tr>
                    <th>No</th>
                    <th>Cabang</th>
                    <th>SKU</th>
                    <th>Produk</th>
                    <th>Satuan</th>
                    <th class="text-end">Stok Fisik</th>
                    <th class="text-end">Reserved</th>
                    <th class="text-end">Tersedia</th>
                    <th>Diperbarui</th>
                    <th class="text-center">Aksi</th>
                </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
@endsection

@section('scripts')
    @vite(['resources/js/pages/inventory/stock.js'])
    <script>
        window.inventoryRoutes = { index: '{{ route('inventory.index') }}', showBranch: @json($showBranch) };
    </script>
@endsection
