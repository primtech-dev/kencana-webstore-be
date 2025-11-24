@extends('layouts.vertical', ['title' => 'Manajemen Testimoni'])

@section('styles')
    @vite(['node_modules/datatables.net-responsive-bs5/css/responsive.bootstrap5.min.css'])
    <style>
        /* Star Rating Styles */
        .star-rating {
            display: inline-flex;
            gap: 5px;
            font-size: 1.5rem;
            cursor: pointer;
        }

        .star-rating input[type="radio"] {
            display: none;
        }

        .star-rating label {
            cursor: pointer;
            color: #ddd;
            transition: color 0.2s;
            margin: 0;
        }

        .star-rating label:hover,
        .star-rating label:hover ~ label,
        .star-rating input[type="radio"]:checked ~ label {
            color: #fbbf24;
        }

        .star-rating {
            direction: rtl;
        }

        .star-rating label {
            direction: ltr;
        }

        /* Image Preview */
        .image-preview-placeholder {
            width: 100%;
            height: 200px;
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

        .current-image-display,
        .preview-image-display {
            height: 200px;
            width: 100%;
            object-fit: cover;
            border-radius: 0.375rem;
        }
    </style>
@endsection

@section('content')
    @include('layouts.shared.page-title', [
        'title' => 'Manajemen Testimoni',
        'subTitle' => 'Kelola testimoni pelanggan untuk meningkatkan kepercayaan pengunjung.',
        'breadcrumbs' => [
            ['name' => 'Manajemen Konten', 'url' => '#'],
            ['name' => 'Testimoni']
        ]
    ])

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Daftar Testimoni</h5>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createTestimonialModal">
                        <i class="ti ti-plus me-1"></i> Tambah Testimoni
                    </button>
                </div>
                <div class="card-body">
                    <table class="table table-striped dt-responsive align-middle w-100" id="testimonials-table">
                        <thead class="thead-sm text-uppercase fs-xxs">
                        <tr>
                            <th width="5%">No</th>
                            <th width="20%">Nama</th>
                            <th width="15%">Pekerjaan</th>
                            <th width="10%">Rating</th>
                            <th width="30%">Komentar</th>
                            <th width="10%">Dibuat Pada</th>
                            <th width="10%" class="text-center">Aksi</th>
                        </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Create Modal -->
    <div class="modal fade" id="createTestimonialModal" tabindex="-1" aria-labelledby="createTestimonialModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="createTestimonialModalLabel">Tambah Testimoni Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('testimonials.store') }}" method="POST" enctype="multipart/form-data" id="createTestimonialForm">
                    @csrf
                    <div class="modal-body">
                        <div class="row">
                            <!-- Left Column -->
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="create_name" class="form-label">
                                        Nama <span class="text-danger">*</span>
                                    </label>
                                    <input type="text"
                                           class="form-control @error('name') is-invalid @enderror"
                                           id="create_name"
                                           name="name"
                                           value="{{ old('name') }}"
                                           placeholder="Masukkan nama pelanggan"
                                           required>
                                    @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="create_job" class="form-label">
                                        Pekerjaan <span class="text-danger">*</span>
                                    </label>
                                    <input type="text"
                                           class="form-control @error('job') is-invalid @enderror"
                                           id="create_job"
                                           name="job"
                                           value="{{ old('job') }}"
                                           placeholder="Masukkan pekerjaan"
                                           maxlength="50"
                                           required>
                                    @error('job')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">
                                        Rating <span class="text-danger">*</span>
                                    </label>
                                    <div class="star-rating" id="create_star_rating">
                                        <input type="radio" name="rating" value="5" id="create_star5">
                                        <label for="create_star5" title="5 bintang">★</label>
                                        <input type="radio" name="rating" value="4" id="create_star4">
                                        <label for="create_star4" title="4 bintang">★</label>
                                        <input type="radio" name="rating" value="3" id="create_star3">
                                        <label for="create_star3" title="3 bintang">★</label>
                                        <input type="radio" name="rating" value="2" id="create_star2">
                                        <label for="create_star2" title="2 bintang">★</label>
                                        <input type="radio" name="rating" value="1" id="create_star1">
                                        <label for="create_star1" title="1 bintang">★</label>
                                    </div>
                                    @error('rating')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-0">
                                    <label for="create_comment" class="form-label">
                                        Komentar <span class="text-danger">*</span>
                                    </label>
                                    <textarea class="form-control @error('comment') is-invalid @enderror"
                                              id="create_comment"
                                              name="comment"
                                              rows="4"
                                              placeholder="Masukkan komentar testimoni"
                                              required>{{ old('comment') }}</textarea>
                                    @error('comment')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Right Column -->
                            <div class="col-md-6">
                                <div class="mb-0">
                                    <label for="create_image" class="form-label">
                                        Foto Pelanggan <span class="text-danger">*</span>
                                    </label>
                                    <input type="file"
                                           class="form-control @error('image') is-invalid @enderror"
                                           id="create_image"
                                           name="image"
                                           accept="image/jpeg,image/jpg,image/png,image/webp"
                                           required>
                                    @error('image')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">Format: JPG, JPEG, PNG, WEBP (Max: 2MB)</small>

                                    <!-- Image Preview Placeholder -->
                                    <div id="create_image_preview_placeholder" class="image-preview-placeholder mt-2">
                                        <div class="text-center">
                                            <i class="ti ti-photo"></i>
                                            <div class="small text-muted mt-2">Preview foto akan muncul di sini</div>
                                        </div>
                                    </div>

                                    <!-- Image Preview -->
                                    <div id="create_image_preview" class="mt-2" style="display: none;">
                                        <img id="create_preview_img" src="" alt="Preview" class="img-thumbnail preview-image-display">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="ti ti-device-floppy me-1"></i> Simpan Testimoni
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Modal -->
    <div class="modal fade" id="editTestimonialModal" tabindex="-1" aria-labelledby="editTestimonialModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editTestimonialModalLabel">Edit Testimoni</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('testimonials.update', ':id') }}" method="POST" enctype="multipart/form-data" id="editTestimonialForm">
                    @csrf
                    @method('PUT')
                    <div class="modal-body">
                        <div class="row">
                            <!-- Left Column -->
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="edit_name" class="form-label">
                                        Nama <span class="text-danger">*</span>
                                    </label>
                                    <input type="text"
                                           class="form-control @error('name') is-invalid @enderror"
                                           id="edit_name"
                                           name="name"
                                           value="{{ old('name') }}"
                                           placeholder="Masukkan nama pelanggan"
                                           required>
                                    @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="edit_job" class="form-label">
                                        Pekerjaan <span class="text-danger">*</span>
                                    </label>
                                    <input type="text"
                                           class="form-control @error('job') is-invalid @enderror"
                                           id="edit_job"
                                           name="job"
                                           value="{{ old('job') }}"
                                           placeholder="Masukkan pekerjaan"
                                           maxlength="50"
                                           required>
                                    @error('job')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">
                                        Rating <span class="text-danger">*</span>
                                    </label>
                                    <div class="star-rating" id="edit_star_rating">
                                        <input type="radio" name="rating" value="5" id="edit_star5">
                                        <label for="edit_star5" title="5 bintang">★</label>
                                        <input type="radio" name="rating" value="4" id="edit_star4">
                                        <label for="edit_star4" title="4 bintang">★</label>
                                        <input type="radio" name="rating" value="3" id="edit_star3">
                                        <label for="edit_star3" title="3 bintang">★</label>
                                        <input type="radio" name="rating" value="2" id="edit_star2">
                                        <label for="edit_star2" title="2 bintang">★</label>
                                        <input type="radio" name="rating" value="1" id="edit_star1">
                                        <label for="edit_star1" title="1 bintang">★</label>
                                    </div>
                                    @error('rating')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-0">
                                    <label for="edit_comment" class="form-label">
                                        Komentar <span class="text-danger">*</span>
                                    </label>
                                    <textarea class="form-control @error('comment') is-invalid @enderror"
                                              id="edit_comment"
                                              name="comment"
                                              rows="4"
                                              placeholder="Masukkan komentar testimoni"
                                              required>{{ old('comment') }}</textarea>
                                    @error('comment')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Right Column -->
                            <div class="col-md-6">
                                <div class="mb-0">
                                    <label for="edit_image" class="form-label">
                                        Foto Pelanggan
                                    </label>
                                    <input type="file"
                                           class="form-control @error('image') is-invalid @enderror"
                                           id="edit_image"
                                           name="image"
                                           accept="image/jpeg,image/jpg,image/png,image/webp">
                                    @error('image')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">Format: JPG, JPEG, PNG, WEBP (Max: 2MB) - Kosongkan jika tidak ingin mengubah</small>

                                    <!-- Current Image -->
                                    <div id="edit_current_image_container" class="mt-2">
                                        <img id="edit_current_img" src="" alt="Current Image" class="img-thumbnail current-image-display">
                                    </div>

                                    <!-- New Image Preview -->
                                    <div id="edit_image_preview" class="mt-2" style="display: none;">
                                        <img id="edit_preview_img" src="" alt="Preview" class="img-thumbnail preview-image-display">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="ti ti-device-floppy me-1"></i> Perbarui Testimoni
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Delete Modal Component --}}
    <x-delete-modal
        id="deleteTestimonialModal"
        formId="deleteTestimonialForm"
        :route="route('testimonials.destroy', ':id')"
        itemNameId="delete_testimonial_name"
        title="Konfirmasi Hapus Testimoni"
        message="Apakah Anda yakin ingin menghapus testimoni dari"
        itemType="testimoni"
    />
@endsection

@section('scripts')
    @vite(['resources/js/pages/content-management/testimonial.js'])

    @if($errors->any())
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                @if(old('_method') === 'PUT')
                const editModal = new bootstrap.Modal(document.getElementById('editTestimonialModal'));
                editModal.show();
                @else
                const createModal = new bootstrap.Modal(document.getElementById('createTestimonialModal'));
                createModal.show();
                @endif
            });
        </script>
    @endif

    <script>
        window.testimonialRoutes = {
            index: '{{ route('testimonials.index') }}',
            destroy: '{{ route('testimonials.destroy', ':id') }}'
        };
    </script>
@endsection
