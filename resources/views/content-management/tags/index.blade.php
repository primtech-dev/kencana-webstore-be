@extends('layouts.vertical', ['title' => 'Manajemen Tag'])

@section('styles')
    @vite(['node_modules/datatables.net-responsive-bs5/css/responsive.bootstrap5.min.css'])
@endsection

@section('content')
    @include('layouts.shared.page-title', [
        'title' => 'Manajemen Tag',
        'subTitle' => 'Kelola tag untuk mengelompokkan data dengan lebih mudah.',
        'breadcrumbs' => [
            ['name' => 'Manajemen Konten', 'url' => '#'],
            ['name' => 'Tag']
        ]
    ])

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Daftar Tag</h5>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                            data-bs-target="#createTagModal">
                        <i class="ti ti-plus me-1"></i> Tambah Tag
                    </button>
                </div>
                <div class="card-body">
                    <table class="table table-striped dt-responsive align-middle w-100" id="tags-table">
                        <thead class="thead-sm text-uppercase fs-xxs">
                            <tr>
                                <th width="5%">No</th>
                                <th width="40%">Nama Tag</th>
                                <th width="15%">Dibuat Pada</th>
                                <th width="12%" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Create Modal -->
    <div class="modal fade" id="createTagModal" tabindex="-1" aria-labelledby="createTagModalLabel"
         aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="createTagModalLabel">Tambah Tag Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <form action="{{ route('tags.store') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="tag_name" class="form-label">Nama Tag <span class="text-danger">*</span></label>
                            <input type="text"
                                   class="form-control @error('name') is-invalid @enderror"
                                   id="tag_name"
                                   name="name"
                                   value="{{ old('name') }}"
                                   placeholder="Masukkan nama tag"
                                   required>
                            @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="ti ti-device-floppy me-1"></i> Simpan
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>

    <!-- Edit Modal -->
    <div class="modal fade" id="editTagModal" tabindex="-1" aria-labelledby="editTagModalLabel"
         aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title" id="editTagModalLabel">Edit Tag</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <form action="{{ route('tags.update', '__ID__') }}" method="POST" id="editTagForm">
                    @csrf
                    @method('PUT')

                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="edit_tag_name" class="form-label">Nama Tag <span class="text-danger">*</span></label>
                            <input type="text"
                                   class="form-control @error('name') is-invalid @enderror"
                                   id="edit_tag_name"
                                   name="name"
                                   value="{{ old('name') }}"
                                   placeholder="Masukkan nama tag"
                                   required>
                            @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="ti ti-device-floppy me-1"></i> Perbarui
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>

    {{-- Delete Modal --}}
    <x-delete-modal
        id="deleteTagModal"
        formId="deleteTagForm"
        :route="route('tags.destroy', ':id')"
        itemNameId="delete_tag_name"
        title="Konfirmasi Hapus Tag"
        message="Apakah Anda yakin ingin menghapus tag"
        itemType="tag"
    />
@endsection

@section('scripts')
    @vite(['resources/js/pages/content-management/tag.js'])

    @if($errors->any())
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                @if(old('_method') === 'PUT')
                    new bootstrap.Modal(document.getElementById('editTagModal')).show();
                @else
                    new bootstrap.Modal(document.getElementById('createTagModal')).show();
                @endif
            });
        </script>
    @endif

    <script>
        window.tagRoutes = {
            index: '{{ route('tags.index') }}',
            destroy: '{{ route('tags.destroy', ':id') }}'
        };
    </script>
@endsection
