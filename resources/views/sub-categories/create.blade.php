@extends('layouts.vertical', ['title' => 'Tambah Sub Kategori'])

@section('styles')
    <style>
        .card-help { background:#fbfbfc; border:1px solid #eef2f6; }
    </style>
@endsection

@section('content')
    @include('layouts.shared.page-title', [
        'title' => 'Tambah Sub Kategori',
        'subTitle' => 'Buat sub kategori baru sebagai child dari kategori',
        'breadcrumbs' => [
            ['name' => 'Pengaturan', 'url' => route('sub_categories.index')],
            ['name' => 'Sub Kategori', 'url' => route('sub_categories.index')],
            ['name' => 'Tambah']
        ]
    ])

    <form action="{{ route('sub_categories.store') }}" method="POST" id="subCategoryForm" enctype="multipart/form-data">
        @csrf

        <div class="row">
            <!-- Main -->
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header"><h5 class="card-title mb-0">Detail Sub Kategori</h5></div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Kategori Induk <span class="text-danger">*</span></label>
                            <select id="parent_id" name="parent_id" class="form-select @error('parent_id') is-invalid @enderror" required>
                                <option value="">— Pilih kategori induk —</option>
                                @foreach($parents ?? [] as $p)
                                    <option value="{{ $p->id }}" {{ old('parent_id') == $p->id ? 'selected' : '' }}>
                                        {{ $p->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('parent_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            <small class="text-muted">Sub kategori wajib memiliki kategori induk.</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Nama Sub Kategori <span class="text-danger">*</span></label>
                            <input id="name" type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                   value="{{ old('name', $subCategory->name ?? '') }}" required>
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            <small class="text-muted">Contoh: Sepatu Lari, Kemeja Formal, Casing HP.</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Slug <small class="text-muted">(opsional)</small></label>
                            <input id="slug" type="text" name="slug" class="form-control @error('slug') is-invalid @enderror"
                                   value="{{ old('slug', $subCategory->slug ?? '') }}">
                            @error('slug')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            <small class="text-muted">Jika kosong, sistem akan membuat slug otomatis dari nama.</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Posisi (Urutan tampil)</label>
                            <input id="position" type="number" name="position" class="form-control @error('position') is-invalid @enderror"
                                   value="{{ old('position', $subCategory->position ?? 0) }}" min="0">
                            @error('position')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            <small class="text-muted">Semakin kecil angkanya, semakin atas tampilannya.</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Thumbnail Sub Kategori <small class="text-muted">(opsional)</small></label>
                            <input type="file"
                                   name="thumbnail"
                                   class="form-control @error('thumbnail') is-invalid @enderror"
                                   accept="image/*">
                            @error('thumbnail')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            <small class="text-muted">
                                Digunakan untuk icon / list sub kategori. Rekomendasi: 400×400 px. Max: 3 MB
                            </small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <div class="card mb-3 card-help">
                    <div class="card-header"><h5 class="card-title mb-0">Pengaturan</h5></div>
                    <div class="card-body">
                        <div class="mb-3 form-check">
                            <input type="hidden" name="is_active" value="0">
                            <input id="isActive" type="checkbox" name="is_active" value="1" class="form-check-input" {{ old('is_active', true) ? 'checked' : '' }}>
                            <label class="form-check-label" for="isActive">Aktif</label>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Tanggal dibuat</label>
                            <input type="text" class="form-control" value="{{ now()->format('d M Y H:i') }}" disabled>
                        </div>
                    </div>
                </div>

                <!-- Tips & Actions -->
                <div class="card mb-3">
                    <div class="card-header"><h5 class="card-title mb-0">Tips</h5></div>
                    <div class="card-body small text-muted">
                        <ul>
                            <li>Sub kategori hanya boleh punya kategori utama sebagai induk (tidak bisa bersarang lebih dari 1 level).</li>
                            <li>Gunakan nama yang jelas dan singkat.</li>
                            <li>Atur posisi agar sub kategori penting berada di urutan atas.</li>
                        </ul>
                    </div>
                </div>

                <div class="card">
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i data-lucide="plus" class="me-1"></i> Buat Sub Kategori
                            </button>
                            <a href="{{ route('sub_categories.index') }}" class="btn btn-outline-secondary">
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
    @vite(['resources/js/pages/sub-categories/sub-categories-form.js'])

    @if($errors->any())
        <script>
            window.serverValidationErrors = {!! json_encode($errors->all()) !!};
        </script>
    @endif
@endsection
