@extends('layouts.vertical', ['title' => 'Edit Produk'])

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
        'title' => 'Edit Produk',
        'subTitle' => 'Perbarui informasi produk.',
        'breadcrumbs' => [
            ['name' => 'Manajemen Konten', 'url' => '#'],
            ['name' => 'Produk', 'url' => route('products.index')],
            ['name' => 'Edit']
        ]
    ])

    <form action="{{ route('products.update', $product->id) }}"
          method="POST"
          enctype="multipart/form-data"
          id="productForm">

        @csrf
        @method('PUT')

        <div class="row">

            <!-- MAIN COLUMN -->
            <div class="col-lg-12">

                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Informasi Produk</h5>
                    </div>

                    <div class="card-body">

                        {{-- NAME --}}
                        <div class="mb-3">
                            <label for="name" class="form-label">
                                Nama Produk <span class="text-danger">*</span>
                            </label>
                            <input type="text"
                                   id="name"
                                   name="name"
                                   class="form-control @error('name') is-invalid @enderror"
                                   value="{{ old('name', $product->name) }}"
                                   placeholder="Masukkan nama produk"
                                   required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- CONTENT --}}
                        <div class="mb-3">
                            <label for="content" class="form-label">
                                Konten Produk <span class="text-danger">*</span>
                            </label>
                            <textarea id="content"
                                      name="content"
                                      class="form-control @error('content') is-invalid @enderror"
                                      required>{{ old('content', $product->content) }}</textarea>
                            @error('content')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- TERMS --}}
                        <div class="mb-3">
                            <label for="terms_and_condition" class="form-label">
                                Syarat & Ketentuan <span class="text-danger">*</span>
                            </label>
                            <textarea id="terms_and_condition"
                                      name="terms_and_condition"
                                      class="form-control @error('terms_and_condition') is-invalid @enderror"
                                      required>{{ old('terms_and_condition', $product->terms_and_condition) }}</textarea>
                            @error('terms_and_condition')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">

                            {{-- IMAGE --}}
                            <div class="col-md-6">
                                <div class="mb-0">
                                    <label for="image" class="form-label">Gambar Produk</label>
                                    <input type="file"
                                           id="image"
                                           name="image"
                                           accept="image/jpeg,image/jpg,image/png,image/webp"
                                           class="form-control @error('image') is-invalid @enderror">
                                    @error('image')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted d-block">Format: JPG, JPEG, PNG, WEBP (Max 2MB)</small>

                                    {{-- CURRENT IMAGE --}}
                                    @if($product->image_path)
                                        <div id="current-image-container" class="mt-2">
                                            <img src="{{ asset('storage/' . $product->image_path) }}"
                                                 class="img-thumbnail"
                                                 style="height:243px;width:100%;object-fit:cover;">
                                        </div>
                                    @else
                                        <div class="image-preview-placeholder mt-2">
                                            <div class="text-center">
                                                <i class="ti ti-photo"></i>
                                                <div class="small text-muted mt-2">Belum ada gambar</div>
                                            </div>
                                        </div>
                                    @endif

                                    {{-- NEW PREVIEW --}}
                                    <div id="image-preview" class="mt-2" style="display:none;">
                                        <img id="preview-img"
                                             class="img-thumbnail"
                                             style="height:243px;width:100%;object-fit:cover;">
                                    </div>
                                </div>
                            </div>

                            {{-- ALT TEXT --}}
                            <div class="col-md-6">
                                <div class="mb-0">
                                    <label for="alt_text" class="form-label">
                                        Alt Text Gambar
                                    </label>
                                    <input type="text"
                                           id="alt_text"
                                           name="alt_text"
                                           class="form-control @error('alt_text') is-invalid @enderror"
                                           value="{{ old('alt_text', $product->alt_text) }}"
                                           maxlength="255">
                                    @error('alt_text')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">Deskripsi alternatif untuk SEO</small>
                                </div>
                            </div>

                        </div>

                    </div>
                </div>

                {{-- STATUS --}}
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Status</h5>
                    </div>

                    <div class="card-body">
                        <div class="mb-0">
                            <label class="form-label">Aktif?</label>
                            <select id="is_active"
                                    name="is_active"
                                    class="form-control form-select">
                                <option value="1" {{ $product->is_active ? 'selected' : '' }}>Aktif</option>
                                <option value="0" {{ !$product->is_active ? 'selected' : '' }}>Nonaktif</option>
                            </select>
                        </div>
                    </div>
                </div>

                {{-- BUTTONS --}}
                <div class="card">
                    <div class="card-body d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="ti ti-device-floppy me-1"></i>
                            Perbarui Produk
                        </button>
                        <a href="{{ route('products.index') }}" class="btn btn-outline-secondary">
                            <i class="ti ti-arrow-left me-1"></i>
                            Kembali
                        </a>
                    </div>
                </div>

            </div>

            <!-- SIDEBAR -->
            <div class="col-lg-4">



            </div>
        </div>

    </form>
@endsection

@section('scripts')
    @vite(['resources/js/pages/content-management/products/product-edit.js'])

    @if($errors->any())
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                @foreach($errors->all() as $error)
                    window.toast?.error("{{ addslashes($error) }}");
                @endforeach
            });
        </script>
    @endif
@endsection
