@extends('layouts.vertical', ['title' => 'Detail Import Stok'])

@section('styles')
    @vite(['node_modules/datatables.net-responsive-bs5/css/responsive.bootstrap5.min.css'])
@endsection

@section('content')
    @include('layouts.shared.page-title', [
        'title' => 'Detail Import Stok',
        'subTitle' => $log->file_name ?: $log->batch_id,
        'breadcrumbs' => [
            ['name' => 'Persediaan', 'url' => route('inventory.index')],
            ['name' => 'Import Stok', 'url' => route('stock_imports.history')],
            ['name' => 'Detail']
        ]
    ])

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">Ringkasan</h5>
            <a href="{{ route('stock_imports.history') }}" class="btn btn-sm btn-outline-secondary">Kembali</a>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <dl class="row mb-0 small">
                        <dt class="col-4">Waktu</dt><dd class="col-8">{{ $log->created_at?->format('d M Y H:i') }}</dd>
                        <dt class="col-4">Oleh</dt><dd class="col-8">{{ $log->user->name ?? '-' }}</dd>
                        <dt class="col-4">Sumber</dt>
                        <dd class="col-8"><span class="badge bg-{{ $log->source === 'odoo' ? 'primary' : 'secondary' }}">{{ $log->source === 'odoo' ? 'Odoo' : 'Template' }}</span></dd>
                        <dt class="col-4">File</dt><dd class="col-8">{{ $log->file_name ?: '-' }}</dd>
                        <dt class="col-4">Batch</dt><dd class="col-8 text-break"><code>{{ $log->batch_id }}</code></dd>
                    </dl>
                </div>
                <div class="col-md-6">
                    <dl class="row mb-0 small">
                        <dt class="col-6">Baris di file</dt><dd class="col-6">{{ number_format($log->rows_total, 0, ',', '.') }}</dd>
                        <dt class="col-6">Stok diubah</dt><dd class="col-6">{{ number_format($log->rows_changed, 0, ',', '.') }}</dd>
                        <dt class="col-6">Di antaranya dinolkan</dt><dd class="col-6">{{ number_format($log->rows_zeroed, 0, ',', '.') }}</dd>
                        <dt class="col-6">Dilewati (tidak cocok)</dt><dd class="col-6">{{ number_format($log->rows_skipped, 0, ',', '.') }}</dd>
                        <dt class="col-6">Cabang</dt><dd class="col-6">{{ implode(', ', $log->branches ?? []) ?: '-' }}</dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header"><h5 class="card-title mb-0">Perubahan Stok pada Import Ini</h5></div>
        <div class="card-body">
            @include('inventory._movements', ['variantId' => null, 'batchId' => $log->batch_id, 'initialBranch' => null])
        </div>
    </div>
@endsection

@section('scripts')
    @vite(['resources/js/pages/inventory/movements.js'])
@endsection
