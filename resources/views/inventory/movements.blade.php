@extends('layouts.vertical', ['title' => 'Riwayat Stok'])

@section('styles')
    @vite(['node_modules/datatables.net-responsive-bs5/css/responsive.bootstrap5.min.css'])
@endsection

@section('content')
    @include('layouts.shared.page-title', [
        'title' => 'Riwayat Stok',
        'subTitle' => $showBranch ? 'Semua perubahan stok di seluruh cabang' : 'Semua perubahan stok di cabang Anda',
        'breadcrumbs' => [['name' => 'Persediaan', 'url' => route('inventory.index')], ['name' => 'Riwayat Stok']]
    ])

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">Riwayat Perubahan Stok</h5>
            <a href="{{ route('inventory.index') }}" class="btn btn-sm btn-outline-secondary">Stok Saat Ini</a>
        </div>
        <div class="card-body">
            @include('inventory._movements', ['variantId' => null, 'initialBranch' => null])
        </div>
    </div>
@endsection

@section('scripts')
    @vite(['resources/js/pages/inventory/movements.js'])
@endsection
