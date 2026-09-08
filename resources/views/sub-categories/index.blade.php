@extends('layouts.vertical', ['title' => 'Manajemen Sub Kategori'])

@section('styles')
    @vite(['node_modules/datatables.net-responsive-bs5/css/responsive.bootstrap5.min.css'])
@endsection

@section('content')
    @include('layouts.shared.page-title', [
        'title' => 'Manajemen Sub Kategori',
        'subTitle' => 'Kelola sub kategori (child dari kategori)',
        'breadcrumbs' => [
            ['name' => 'Pengaturan', 'url' => '#'],
            ['name' => 'Sub Kategori']
        ]
    ])

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <h5 class="card-title mb-0">Daftar Sub Kategori</h5>
                    <div class="d-flex gap-2">
                        @can('sub_categories.create')
                            <a href="{{ route('sub_categories.import.form') }}" class="btn btn-outline-secondary">
                                <i data-lucide="upload" class="me-1"></i> Import
                            </a>
                            <a href="{{ route('sub_categories.create') }}" class="btn btn-primary">
                                <i data-lucide="plus" class="me-1"></i> Tambah Sub Kategori
                            </a>
                        @endcan
                    </div>
                </div>
                <div class="card-body">
                    <table class="table table-striped dt-responsive align-middle w-100" id="sub-categories-table">
                        <thead class="thead-sm text-uppercase fs-xxs">
                        <tr>
                            <th width="5%">No</th>
                            <th>Nama</th>
                            <th width="8%">Thumbnail</th>
                            <th>Slug</th>
                            <th>Kategori Induk</th>
                            <th width="8%">Posisi</th>
                            <th width="10%">Status</th>
                            <th width="12%">Created At</th>
                            <th width="12%" class="text-center">Aksi</th>
                        </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <x-delete-modal
        id="deleteSubCategoryModal"
        formId="deleteSubCategoryForm"
        :route="route('sub_categories.destroy', ':id')"
        itemNameId="delete_sub_category_title"
        title="Konfirmasi Hapus Sub Kategori"
        message="Apakah Anda yakin ingin menghapus sub kategori ini?"
        itemType="sub kategori"
    />
@endsection

@section('scripts')
    @vite(['resources/js/pages/sub-categories/sub-categories.js'])

    <script>
        window.subCategoryRoutes = {
            index: '{{ route('sub_categories.index') }}',
            edit: '{{ route('sub_categories.edit', ':id') }}',
            destroy: '{{ route('sub_categories.destroy', ':id') }}'
        };
    </script>
@endsection
