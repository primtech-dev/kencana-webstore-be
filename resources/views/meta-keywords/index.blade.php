@extends('layouts.vertical', ['title' => 'Manajemen Meta Keyword'])

@section('styles')
    @vite(['node_modules/datatables.net-responsive-bs5/css/responsive.bootstrap5.min.css'])
@endsection

@section('content')
    @include('layouts.shared.page-title', [
        'title' => 'Manajemen Meta Keyword',
        'subTitle' => 'Kelola master meta keyword produk',
        'breadcrumbs' => [
            ['name' => 'Pengaturan', 'url' => '#'],
            ['name' => 'Meta Keyword']
        ]
    ])

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <h5 class="card-title mb-0">Daftar Meta Keyword</h5>
                    <div class="d-flex gap-2">
                        @can('meta_keywords.view')
                            <a href="{{ route('meta_keywords.export') }}" class="btn btn-outline-secondary">
                                <i data-lucide="download" class="me-1"></i> Export
                            </a>
                        @endcan
                        @can('meta_keywords.create')
                            <a href="{{ route('meta_keywords.import.form') }}" class="btn btn-outline-secondary">
                                <i data-lucide="upload" class="me-1"></i> Import
                            </a>
                            <a href="{{ route('meta_keywords.create') }}" class="btn btn-primary">
                                <i data-lucide="plus" class="me-1"></i> Tambah Meta Keyword
                            </a>
                        @endcan
                    </div>
                </div>
                <div class="card-body">
                    <table class="table table-striped dt-responsive align-middle w-100" id="meta-keywords-table">
                        <thead class="thead-sm text-uppercase fs-xxs">
                        <tr>
                            <th width="5%">No</th>
                            <th>Nama</th>
                            <th>Slug</th>
                            <th width="10%">Status</th>
                            <th width="15%">Created At</th>
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
        id="deleteMetaKeywordModal"
        formId="deleteMetaKeywordForm"
        :route="route('meta_keywords.destroy', ':id')"
        itemNameId="delete_meta_keyword_title"
        title="Konfirmasi Hapus Meta Keyword"
        message="Apakah Anda yakin ingin menghapus meta keyword ini?"
        itemType="meta keyword"
    />
@endsection

@section('scripts')
    @vite(['resources/js/pages/meta-keywords/meta-keywords.js'])

    <script>
        window.metaKeywordRoutes = {
            index: '{{ route('meta_keywords.index') }}',
            edit: '{{ route('meta_keywords.edit', ':id') }}',
            destroy: '{{ route('meta_keywords.destroy', ':id') }}'
        };
    </script>
@endsection
