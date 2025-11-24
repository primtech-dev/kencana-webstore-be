@extends('layouts.vertical', ['title' => 'Tambah Produk'])

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
        'title' => 'Tambah Produk',
        'subTitle' => 'Buat produk baru untuk ditampilkan kepada pengunjung.',
        'breadcrumbs' => [
            ['name' => 'Manajemen Konten', 'url' => '#'],
            ['name' => 'Produk', 'url' => route('products.index')],
            ['name' => 'Tambah']
        ]
    ])

    <form action="{{ route('products.store') }}" method="POST" enctype="multipart/form-data" id="productForm">
    @csrf

        <div class="row">
            <div class="col-12">

                <!-- Card: Informasi Produk -->
                <div class="card mb-3">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Informasi Produk</h5>
                    </div>
                    <div class="card-body">

                        <!-- Name -->
                        <div class="mb-3">
                            <label for="name" class="form-label">Nama Produk <span class="text-danger">*</span></label>
                            <input type="text"
                                id="name"
                                name="name"
                                class="form-control @error('name') is-invalid @enderror"
                                value="{{ old('name') }}"
                                placeholder="Masukkan nama produk"
                                required>
                            @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Content -->
                        <div class="mb-3">
                            <label for="content" class="form-label">Deskripsi Produk <span class="text-danger">*</span></label>
                            <textarea id="content"
                                    name="content"
                                    class="form-control @error('content') is-invalid @enderror"
                                    required>{{ old('content') }}</textarea>
                            @error('content')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Image + Alt Text -->
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="image" class="form-label">Gambar Produk <span class="text-danger">*</span></label>
                                <input type="file"
                                    id="image"
                                    name="image"
                                    class="form-control @error('image') is-invalid @enderror"
                                    accept="image/jpeg,image/jpg,image/png,image/webp"
                                    required>
                                @error('image')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Format: JPG, JPEG, PNG, WEBP (Max: 2MB)</small>

                                <div id="image-preview-placeholder" class="image-preview-placeholder mt-2">
                                    <div class="text-center">
                                        <i class="ti ti-photo"></i>
                                        <div class="small text-muted mt-2">Preview gambar akan muncul di sini</div>
                                    </div>
                                </div>

                                <div id="image-preview" class="mt-2" style="display:none;">
                                    <img id="preview-img" src="" class="img-thumbnail"
                                        style="max-height:243px; width:100%; object-fit:cover;">
                                </div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="alt_text" class="form-label">Alt Text Gambar <span class="text-danger">*</span></label>
                                <input type="text"
                                    id="alt_text"
                                    name="alt_text"
                                    value="{{ old('alt_text') }}"
                                    class="form-control @error('alt_text') is-invalid @enderror"
                                    placeholder="Deskripsi gambar untuk SEO"
                                    maxlength="255"
                                    required>
                                @error('alt_text')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                    </div>
                </div>

                <!-- Terms & Conditions -->
                <div class="card mb-3">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Terms & Condition</h5>
                    </div>
                    <div class="card-body">
                        <textarea
                            class="form-control @error('terms_and_condition') is-invalid @enderror"
                            id="terms_and_condition"
                            name="terms_and_condition"
                            required>{{ old('terms_and_condition') }}</textarea>

                        @error('terms_and_condition')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <!-- Status -->
                <div class="card mb-3">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Status Produk</h5>
                    </div>
                    <div class="card-body">
                        <label for="is_active" class="form-label">Status</label>
                        <select id="is_active" name="is_active" class="form-select">
                            <option value="1" {{ old('is_active', 1) == 1 ? 'selected' : '' }}>Aktif</option>
                            <option value="0" {{ old('is_active', 1) == 0 ? 'selected' : '' }}>Nonaktif</option>
                        </select>
                    </div>
                </div>

                <!-- Action Button Row -->
                <div class="card mb-3">
                    <div class="card-body">
                        <div class="d-flex gap-2">
                            <a href="{{ route('products.index') }}" class="btn btn-outline-secondary">
                                <i class="ti ti-arrow-left me-1"></i> Kembali
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="ti ti-device-floppy me-1"></i> Simpan Produk
                            </button>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </form>
@endsection

@section('scripts')
    @vite(['resources/js/pages/content-management/products/product-create.js'])

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
