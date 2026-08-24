@extends('layouts.vertical', ['title' => 'Tambah Meta Keyword'])

@section('styles')
    <style>
        .card-help { background:#fbfbfc; border:1px solid #eef2f6; }
    </style>
@endsection

@section('content')
    @include('layouts.shared.page-title', [
        'title' => 'Tambah Meta Keyword',
        'subTitle' => 'Buat meta keyword baru',
        'breadcrumbs' => [
            ['name' => 'Pengaturan', 'url' => route('meta_keywords.index')],
            ['name' => 'Meta Keyword', 'url' => route('meta_keywords.index')],
            ['name' => 'Tambah']
        ]
    ])

    <form action="{{ route('meta_keywords.store') }}" method="POST" id="metaKeywordForm">
        @csrf

        <div class="row">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header"><h5 class="card-title mb-0">Detail Meta Keyword</h5></div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Nama Meta Keyword <span class="text-danger">*</span></label>
                            <input id="name" type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                   value="{{ old('name', $metaKeyword->name ?? '') }}" required>
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            <small class="text-muted">Contoh: Galvalum, Atap Metal, Genteng Metal.</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Slug <small class="text-muted">(opsional)</small></label>
                            <input id="slug" type="text" name="slug" class="form-control @error('slug') is-invalid @enderror"
                                   value="{{ old('slug', $metaKeyword->slug ?? '') }}">
                            @error('slug')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            <small class="text-muted">Jika kosong, sistem akan membuat slug otomatis dari nama.</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card mb-3 card-help">
                    <div class="card-header"><h5 class="card-title mb-0">Pengaturan</h5></div>
                    <div class="card-body">
                        <div class="mb-3 form-check">
                            <input type="hidden" name="is_active" value="0">
                            <input id="isActive" type="checkbox" name="is_active" value="1" class="form-check-input" {{ old('is_active', true) ? 'checked' : '' }}>
                            <label class="form-check-label" for="isActive">Aktif</label>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i data-lucide="plus" class="me-1"></i> Buat Meta Keyword
                            </button>
                            <a href="{{ route('meta_keywords.index') }}" class="btn btn-outline-secondary">
                                <i data-lucide="arrow-left" class="me-1"></i> Kembali
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection

@section('scripts')
    @vite(['resources/js/pages/meta-keywords/meta-keywords-form.js'])

    @if($errors->any())
        <script>
            window.serverValidationErrors = {!! json_encode($errors->all()) !!};
        </script>
    @endif
@endsection
