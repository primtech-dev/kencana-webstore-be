@extends('layouts.vertical', ['title' => 'Import Sub Kategori'])

@section('content')
    @include('layouts.shared.page-title', [
        'title' => 'Import Sub Kategori',
        'subTitle' => 'Upload data sub kategori dari Excel',
        'breadcrumbs' => [
            ['name' => 'Sub Kategori', 'url' => route('sub_categories.index')],
            ['name' => 'Import']
        ]
    ])

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Import Sub Kategori (Excel)</h5>
        </div>
        <div class="card-body">

            <div class="card mb-3">
                <div class="card-body d-flex flex-wrap gap-2">
                    <a href="{{ route('sub_categories.import.template') }}"
                       class="btn btn-outline-primary">
                        <i data-lucide="download" class="me-1"></i>
                        Download Template Excel
                    </a>
                </div>
            </div>

            <div class="alert alert-info mt-2 mb-4">
                <ul class="mb-0">
                    <li>File Excel harus punya kolom header <strong>name</strong> (nama sub kategori) dan <strong>parent_category</strong> (nama kategori induk)</li>
                    <li>Kategori induk yang dituliskan harus sudah ada sebagai kategori utama (bukan sub kategori lain); jika tidak ditemukan, baris tersebut akan dilewati</li>
                    <li>Sub kategori yang namanya sudah ada di master (tidak peduli huruf besar/kecil) akan dilewati, bukan diduplikasi</li>
                    <li>Sub kategori yang belum ada akan otomatis dibuat dalam kondisi aktif</li>
                </ul>
            </div>

            <form method="POST" action="{{ route('sub_categories.import.process') }}" enctype="multipart/form-data">
                @csrf

                <div class="mb-3">
                    <label class="form-label">File Excel Sub Kategori</label>
                    <input type="file" name="excel" class="form-control @error('excel') is-invalid @enderror" required>
                    @error('excel')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <button class="btn btn-primary">
                    Import Sub Kategori
                </button>
                <a href="{{ route('sub_categories.index') }}" class="btn btn-outline-secondary">Kembali</a>
            </form>
        </div>
    </div>
@endsection
