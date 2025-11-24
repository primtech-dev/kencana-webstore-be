@extends('layouts.vertical', ['title' => 'Manajemen Produk'])

@section('styles')
    @vite(['node_modules/datatables.net-responsive-bs5/css/responsive.bootstrap5.min.css'])
@endsection

@section('content')
    @include('layouts.shared.page-title', [
        'title' => 'Manajemen Produk',
        'subTitle' => 'Kelola produk yang ditampilkan kepada pengunjung.',
        'breadcrumbs' => [
            ['name' => 'Manajemen Konten', 'url' => '#'],
            ['name' => 'Produk']
        ]
    ])

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Daftar Produk</h5>
                    <a href="{{ route('products.create') }}" class="btn btn-primary">
                        <i class="ti ti-plus me-1"></i> Tambah Produk
                    </a>
                </div>

                <div class="card-body">
                    <table class="table table-striped dt-responsive align-middle w-100" id="products-table">
                        <thead class="thead-sm text-uppercase fs-xxs">
                        <tr>
                            <th width="5%">No</th>
                            <th width="25%">Nama Produk</th>
                            <th width="20%">Slug</th>
                            <th width="20%">Status</th>
                            <th width="15%">Dibuat Pada</th>
                            <th width="15%" class="text-center">Aksi</th>
                        </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Delete Modal Component --}}
    <x-delete-modal
        id="deleteProductModal"
        formId="deleteProductForm"
        :route="route('products.destroy', ':id')"
        itemNameId="delete_product_title"
        title="Konfirmasi Hapus Produk"
        message="Apakah Anda yakin ingin menghapus produk"
        itemType="produk"
    />
@endsection

@section('scripts')
    @vite(['resources/js/pages/content-management/products/product.js'])

    <script>
        window.productRoutes = {
            index: '{{ route('products.index') }}',
            edit: '{{ route('products.edit', ':id') }}',
            destroy: '{{ route('products.destroy', ':id') }}'
        };
    </script>
@endsection
