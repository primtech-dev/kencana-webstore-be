@extends('layouts.vertical', ['title' => 'Manajemen Artikel'])

@section('styles')
    @vite(['node_modules/datatables.net-responsive-bs5/css/responsive.bootstrap5.min.css'])
@endsection

@section('content')
    @include('layouts.shared.page-title', [
        'title' => 'Manajemen Artikel',
        'subTitle' => 'Kelola artikel untuk memberikan informasi dan konten menarik kepada pengunjung.',
        'breadcrumbs' => [
            ['name' => 'Manajemen Konten', 'url' => '#'],
            ['name' => 'Artikel']
        ]
    ])

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Daftar Artikel</h5>
                    <a href="{{ route('articles.create') }}" class="btn btn-primary">
                        <i class="ti ti-plus me-1"></i> Tambah Artikel
                    </a>
                </div>
                <div class="card-body">
                    <table class="table table-striped dt-responsive align-middle w-100" id="articles-table">
                        <thead class="thead-sm text-uppercase fs-xxs">
                        <tr>
                            <th width="5%">No</th>
                            <th width="25%">Judul</th>
                            <th width="20%">SEO URL</th>
                            <th width="20%">Meta Title</th>
                            <th width="12%">Penulis</th>
                            <th width="12%">Dibuat Pada</th>
                            <th width="12%" class="text-center">Aksi</th>
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
            id="deleteArticleModal"
            formId="deleteArticleForm"
            :route="route('articles.destroy', ':id')"
            itemNameId="delete_article_title"
            title="Konfirmasi Hapus Artikel"
            message="Apakah Anda yakin ingin menghapus artikel"
            itemType="artikel"
    />
@endsection

@section('scripts')
    @vite(['resources/js/pages/content-management/articles/article.js'])

    <script>
        window.articleRoutes = {
            index: '{{ route('articles.index') }}',
            edit: '{{ route('articles.edit', ':id') }}',
            destroy: '{{ route('articles.destroy', ':id') }}'
        };
    </script>
@endsection
