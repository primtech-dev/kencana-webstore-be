@extends('layouts.vertical', ['title' => 'Edit Artikel'])

@section('styles')
    <style>
        .image-preview-placeholder {
            width: 100%;
            height: 243px;
            border: 2px dashed #dee2e6;
            border-radius: 0.375rem;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #f8f9fa;
            color: #6c757d;
            transition: all 0.3s ease;
        }

        .image-preview-placeholder:hover {
            border-color: #adb5bd;
            background-color: #e9ecef;
        }

        .image-preview-placeholder i {
            font-size: 3rem;
            opacity: 0.3;
        }
    </style>
@endsection

@section('content')
    @include('layouts.shared.page-title', [
        'title' => 'Edit Artikel',
        'subTitle' => 'Perbarui informasi artikel yang sudah ada.',
        'breadcrumbs' => [
            ['name' => 'Manajemen Konten', 'url' => '#'],
            ['name' => 'Artikel', 'url' => route('articles.index')],
            ['name' => 'Edit']
        ]
    ])

    <form action="{{ route('articles.update', $article->id) }}" method="POST" enctype="multipart/form-data" id="articleForm">
        @csrf
        @method('PUT')
        <div class="row">
            <!-- Main Content -->
            <div class="col-lg-8">
                <!-- Informasi Artikel -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Informasi Artikel</h5>
                    </div>
                    <div class="card-body">
                        <!-- Judul -->
                        <div class="mb-3">
                            <label for="title" class="form-label">
                                Judul Artikel <span class="text-danger">*</span>
                            </label>
                            <input type="text"
                                   class="form-control @error('title') is-invalid @enderror"
                                   id="title"
                                   name="title"
                                   value="{{ old('title', $article->title) }}"
                                   placeholder="Masukkan judul artikel"
                                   required>
                            @error('title')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Konten -->
                        <div class="mb-3">
                            <label for="content" class="form-label">
                                Konten Artikel <span class="text-danger">*</span>
                            </label>
                            <textarea class="form-control @error('content') is-invalid @enderror"
                                      id="content"
                                      name="content"
                                      required>{{ old('content', $article->content) }}</textarea>
                            @error('content')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <!-- Gambar -->
                            <div class="col-md-6">
                                <div class="mb-0">
                                    <label for="image" class="form-label">
                                        Gambar Artikel
                                    </label>
                                    <input type="file"
                                           class="form-control @error('image') is-invalid @enderror"
                                           id="image"
                                           name="image"
                                           accept="image/jpeg,image/jpg,image/png,image/webp">
                                    @error('image')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">Format: JPG, JPEG, PNG, WEBP (Max: 2MB)</small>

                                    <!-- Current Image (shown by default) -->
                                    @if($article->image_path)
                                        <div id="current-image-container" class="mt-2">
                                            <img src="{{ asset('storage/' . $article->image_path) }}"
                                                 alt="{{ $article->image_alt_text }}"
                                                 class="img-thumbnail current-image-display"  style="height: 243px; width: 100%; object-fit: cover;">
                                        </div>
                                    @else
                                        <!-- Placeholder jika tidak ada gambar -->
                                        <div id="image-preview-placeholder" class="image-preview-placeholder mt-2">
                                            <div class="text-center">
                                                <i class="ti ti-photo"></i>
                                                <div class="small text-muted mt-2">Belum ada gambar</div>
                                            </div>
                                        </div>
                                    @endif

                                    <!-- Image Preview (untuk gambar baru) -->
                                    <div id="image-preview" class="mt-2" style="display: none;">
                                        <img id="preview-img" src="" alt="Preview" class="img-thumbnail" style="height: 243px; width: 100%; object-fit: cover;">
                                    </div>
                                </div>
                            </div>

                            <!-- Alt Text -->
                            <div class="col-md-6">
                                <div class="mb-0">
                                    <label for="image_alt_text" class="form-label">
                                        Alt Text Gambar <span class="text-danger">*</span>
                                    </label>
                                    <input type="text"
                                           class="form-control @error('image_alt_text') is-invalid @enderror"
                                           id="image_alt_text"
                                           name="image_alt_text"
                                           value="{{ old('image_alt_text', $article->image_alt_text) }}"
                                           placeholder="Deskripsi gambar untuk SEO"
                                           maxlength="255"
                                           required>
                                    @error('image_alt_text')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">Deskripsi alternatif gambar</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Tips -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="ti ti-bulb me-1"></i> Tips Artikel
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row small text-muted">
                            <div class="col-6">
                                <ul class="mb-0 ps-3">
                                    <li>Judul menarik</li>
                                    <li>SEO URL optimal</li>
                                    <li>Tag relevan</li>
                                </ul>
                            </div>
                            <div class="col-6">
                                <ul class="mb-0 ps-3">
                                    <li>Gambar berkualitas</li>
                                    <li>Meta desc ≤ 160</li>
                                    <li>Meta title ≤ 60</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SEO & Meta Data -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">SEO & Meta Data</h5>
                    </div>
                    <div class="card-body">
                        <!-- SEO URL -->
                        <div class="mb-3">
                            <label for="seo_url" class="form-label">
                                SEO URL <span class="text-danger">*</span>
                            </label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text small">{{ url('/artikel') }}/</span>
                                <input type="text"
                                       class="form-control form-control-sm @error('seo_url') is-invalid @enderror"
                                       id="seo_url"
                                       name="seo_url"
                                       value="{{ old('seo_url', $article->seo_url) }}"
                                       placeholder="contoh-url-artikel"
                                       maxlength="255"
                                       required>
                            </div>
                            @error('seo_url')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Meta Title -->
                        <div class="mb-3">
                            <label for="meta_title" class="form-label">
                                Meta Title <span class="text-danger">*</span>
                            </label>
                            <input type="text"
                                   class="form-control form-control-sm @error('meta_title') is-invalid @enderror"
                                   id="meta_title"
                                   name="meta_title"
                                   value="{{ old('meta_title', $article->meta_title) }}"
                                   placeholder="Meta title untuk SEO"
                                   maxlength="255"
                                   required>
                            @error('meta_title')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Meta Keywords -->
                        <div class="mb-3">
                            <label for="meta_keywords" class="form-label">
                                Meta Keywords <span class="text-danger">*</span>
                            </label>
                            <input type="text"
                                   class="form-control form-control-sm @error('meta_keywords') is-invalid @enderror"
                                   id="meta_keywords"
                                   name="meta_keywords"
                                   value="{{ old('meta_keywords', $article->meta_keywords) }}"
                                   placeholder="kata kunci, artikel, blog"
                                   required>
                            @error('meta_keywords')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Meta Description -->
                        <div class="mb-0">
                            <label for="meta_description" class="form-label">
                                Meta Description <span class="text-danger">*</span>
                            </label>
                            <textarea class="form-control form-control-sm @error('meta_description') is-invalid @enderror"
                                      id="meta_description"
                                      name="meta_description"
                                      rows="3"
                                      placeholder="Deskripsi yang muncul di hasil pencarian"
                                      required>{{ old('meta_description', $article->meta_description) }}</textarea>
                            @error('meta_description')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Tag Artikel -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Tag Artikel</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-0">
                            <label for="tags" class="form-label">
                                Pilih Tag <span class="text-danger">*</span>
                            </label>
                            <select class="form-select @error('tags') is-invalid @enderror"
                                    id="tags"
                                    name="tags[]"
                                    multiple
                                    required>
                                @foreach($tags as $tag)
                                    <option value="{{ $tag->id }}"
                                        {{ in_array($tag->id, old('tags', $article->tags->pluck('id')->toArray())) ? 'selected' : '' }}>
                                        {{ $tag->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('tags')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="card">
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="ti ti-device-floppy me-1"></i> Perbarui Artikel
                            </button>
                            <a href="{{ route('articles.index') }}" class="btn btn-outline-secondary">
                                <i class="ti ti-arrow-left me-1"></i> Kembali
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection

@section('scripts')
    @vite(['resources/js/pages/content-management/articles/article-edit.js'])

    {{-- Show validation errors as toast --}}
    @if($errors->any())
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                if (window.toast) {
                    @foreach($errors->all() as $error)
                    window.toast.error('{{ addslashes($error) }}');
                    @endforeach
                }
            });
        </script>
    @endif
@endsection
