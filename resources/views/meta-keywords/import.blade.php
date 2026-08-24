@extends('layouts.vertical', ['title' => 'Import Meta Keyword'])

@section('content')
    @include('layouts.shared.page-title', [
        'title' => 'Import Meta Keyword',
        'subTitle' => 'Upload data meta keyword dari Excel',
        'breadcrumbs' => [
            ['name' => 'Meta Keyword', 'url' => route('meta_keywords.index')],
            ['name' => 'Import']
        ]
    ])

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Import Meta Keyword (Excel)</h5>
        </div>
        <div class="card-body">

            <div class="alert alert-info mt-2 mb-4">
                <ul class="mb-0">
                    <li>File Excel harus punya kolom header <strong>name</strong> berisi nama meta keyword (satu keyword per baris)</li>
                    <li>Keyword yang namanya sudah ada di master (tidak peduli huruf besar/kecil) akan dilewati, bukan diduplikasi</li>
                    <li>Keyword yang belum ada akan otomatis dibuat</li>
                </ul>
            </div>

            <form method="POST" action="{{ route('meta_keywords.import.process') }}" enctype="multipart/form-data">
                @csrf

                <div class="mb-3">
                    <label class="form-label">File Excel Meta Keyword</label>
                    <input type="file" name="excel" class="form-control @error('excel') is-invalid @enderror" required>
                    @error('excel')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <button class="btn btn-primary">
                    Import Meta Keyword
                </button>
                <a href="{{ route('meta_keywords.index') }}" class="btn btn-outline-secondary">Kembali</a>
            </form>
        </div>
    </div>
@endsection
