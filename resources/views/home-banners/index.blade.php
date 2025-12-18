@extends('layouts.vertical', ['title' => 'Home Banner'])

@section('content')
    @include('layouts.shared.page-title', [
        'title' => 'Home Banner',
        'subTitle' => 'Kelola banner halaman utama'
    ])

    <div class="card">
        <div class="card-header d-flex justify-content-between">
            <h5 class="mb-0">Daftar Banner</h5>
            <a href="{{ route('admin.home-banners.create') }}" class="btn btn-primary">
                <i data-lucide="plus"></i> Tambah Banner
            </a>
        </div>
        <div class="card-body">
            <table class="table" id="bannerTable">
                <thead>
                <tr>
                    <th>No</th>
                    <th>Banner</th>
                    <th>Kode</th>
                    <th>Judul</th>
                    <th>Urutan</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
                </thead>
            </table>
        </div>
    </div>
@endsection

@section('scripts')
    @vite(['resources/js/pages/home-banners/index.js'])

    <script>
        window.bannerRoutes = {
            index: '{{ route('admin.home-banners.index') }}',
            edit: '{{ route('admin.home-banners.edit', ':id') }}',
            destroy: '{{ route('admin.home-banners.destroy', ':id') }}'
        };
    </script>
@endsection
