@extends('layouts.vertical', ['title' => 'Edit Kategori'])

@section('styles')
    <style>
        .card-help {
            background: #fbfbfc;
            border: 1px solid #eef2f6;
        }

        .preview-card img {
            transition: 0.2s ease-in-out;
        }

        .preview-card img:hover {
            transform: scale(1.02);
        }

        .preview-empty {
            background: #f8f9fa;
            border: 1px dashed #dee2e6;
            padding: 40px 20px;
            border-radius: 8px;
            text-align: center;
            color: #6c757d;
            font-size: 14px;
        }
    </style>
@endsection

@section('content')
    @include('layouts.shared.page-title', [
        'title' => 'Edit Kategori',
        'subTitle' => 'Perbarui data kategori',
        'breadcrumbs' => [
            ['name' => 'Pengaturan', 'url' => route('categories.index')],
            ['name' => 'Kategori', 'url' => route('categories.index')],
            ['name' => 'Edit'],
        ],
    ])

    <form action="{{ route('categories.update', $category->id) }}" method="POST" enctype="multipart/form-data"
        id="categoryForm">
        @csrf
        @method('PUT')

        <div class="row">

            <!-- ================= LEFT SIDE ================= -->
            <div class="col-lg-8">

                <!-- DETAIL KATEGORI -->
                <div class="card mb-3">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Detail Kategori</h5>
                    </div>
                    <div class="card-body">

                        <div class="mb-3">
                            <label class="form-label">Nama Kategori <span class="text-danger">*</span></label>
                            <input type="text" id="name" name="name"
                                class="form-control @error('name') is-invalid @enderror"
                                value="{{ old('name', $category->name) }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Slug</label>
                            <input type="text" id="slug" name="slug"
                                class="form-control @error('slug') is-invalid @enderror"
                                value="{{ old('slug', $category->slug) }}">
                            @error('slug')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Parent Kategori</label>
                            <select name="parent_id" class="form-select @error('parent_id') is-invalid @enderror">
                                <option value="">— Tidak ada —</option>
                                @foreach ($parents ?? [] as $p)
                                    <option value="{{ $p->id }}"
                                        {{ old('parent_id', $category->parent_id) == $p->id ? 'selected' : '' }}>
                                        {{ $p->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('parent_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Posisi</label>
                            <input type="number" name="position" id="position" min="0"
                                class="form-control @error('position') is-invalid @enderror"
                                value="{{ old('position', $category->position) }}">
                            @error('position')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Upload Banner</label>
                            <input type="file" name="banner" class="form-control @error('banner') is-invalid @enderror"
                                accept="image/*">
                            @error('banner')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Rekomendasi 1200×400px</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Alt Text Banner</label>
                            <input type="text" name="banner_alt" class="form-control"
                                value="{{ old('banner_alt', $category->banner_alt) }}">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Upload Thumbnail</label>
                            <input type="file" name="thumbnail"
                                class="form-control @error('thumbnail') is-invalid @enderror" accept="image/*">
                            @error('thumbnail')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Rekomendasi 600×600px</small>
                        </div>

                    </div>
                </div>

                <!-- PENGATURAN -->
                <div class="card mb-3 card-help">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Pengaturan</h5>
                    </div>
                    <div class="card-body">

                        <div class="form-check mb-3">
                            <input type="hidden" name="is_active" value="0">
                            <input type="checkbox" name="is_active" value="1" class="form-check-input"
                                {{ old('is_active', $category->is_active) ? 'checked' : '' }}>
                            <label class="form-check-label">Aktif</label>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Dibuat</label>
                            <input type="text" class="form-control"
                                value="{{ $category->created_at?->format('d M Y H:i') }}" disabled>
                        </div>

                        <div>
                            <label class="form-label">Terakhir Diupdate</label>
                            <input type="text" class="form-control"
                                value="{{ $category->updated_at?->format('d M Y H:i') }}" disabled>
                        </div>

                    </div>
                </div>

                <!-- TIPS -->
                <div class="card mb-3">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Tips</h5>
                    </div>
                    <div class="card-body small text-muted">
                        <ul>
                            <li>Gunakan slug unik untuk URL SEO.</li>
                            <li>Jangan jadikan kategori sebagai parent dirinya sendiri.</li>
                            <li>Gunakan posisi untuk prioritas tampilan.</li>
                        </ul>
                    </div>
                </div>

                <!-- BUTTON -->
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex gap-2 justify-content-start">
                            <button type="submit" class="btn btn-primary">
                                <i data-lucide="save" class="me-1"></i>
                                Simpan Perubahan
                            </button>

                            <a href="{{ route('categories.index') }}" class="btn btn-outline-secondary">
                                <i data-lucide="arrow-left" class="me-1"></i>
                                Kembali
                            </a>
                        </div>
                    </div>
                </div>

            </div>

            <!-- ================= RIGHT SIDE (PREVIEW) ================= -->
            <div class="col-lg-4">

                <!-- PREVIEW BANNER -->
                <div class="card mb-3 preview-card">
                    <div class="card-header">
                        <h6 class="mb-0">Preview Banner</h6>
                    </div>
                    <div class="card-body text-center">
                        @if ($category->banner_url)
                            <img src="{{ $category->banner_url }}" class="img-fluid rounded shadow-sm"
                                style="max-height:240px;object-fit:cover;width:100%;">
                        @else
                            <div class="preview-empty">
                                Belum ada banner
                            </div>
                        @endif
                    </div>
                </div>

                <!-- PREVIEW THUMBNAIL -->
                <div class="card mb-3 preview-card">
                    <div class="card-header">
                        <h6 class="mb-0">Preview Thumbnail</h6>
                    </div>
                    <div class="card-body text-center">
                        @if ($category->thumbnail_url)
                            <img src="{{ $category->thumbnail_url }}" class="rounded shadow-sm"
                                style="width:160px;height:160px;object-fit:cover;">
                        @else
                            <div class="preview-empty">
                                Belum ada thumbnail
                            </div>
                        @endif
                    </div>
                </div>

            </div>

        </div>
    </form>
@endsection

@section('scripts')
    @vite(['resources/js/pages/categories/categories-form.js'])

    @if ($errors->any())
        <script>
            window.serverValidationErrors = {!! json_encode($errors->all()) !!};
        </script>
    @endif
@endsection
