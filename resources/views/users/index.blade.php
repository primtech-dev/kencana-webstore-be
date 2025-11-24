@extends('layouts.vertical', ['title' => 'Manajemen User'])

@section('styles')
    @vite(['node_modules/datatables.net-responsive-bs5/css/responsive.bootstrap5.min.css'])
@endsection

@section('content')
    @include('layouts.shared.page-title', [
        'title' => 'Manajemen User',
        'subTitle' => 'Kelola user untuk mengatur akses dan hak akses pengguna.',
        'breadcrumbs' => [
            ['name' => 'User & Akses', 'url' => '#'],
            ['name' => 'User']
        ]
    ])

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Daftar User</h5>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createUserModal">
                        <i class="ti ti-plus me-1"></i> Tambah User
                    </button>
                </div>
                <div class="card-body">
                    <table class="table table-striped dt-responsive align-middle w-100" id="users-table">
                        <thead class="thead-sm text-uppercase fs-xxs">
                        <tr>
                            <th width="5%">No</th>
                            <th width="30%">Nama</th>
                            <th width="30%">Email</th>
                            <th width="15%">Dibuat Pada</th>
                            <th width="20%" class="text-center">Aksi</th>
                        </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Create Modal -->
    <div class="modal fade" id="createUserModal" tabindex="-1" aria-labelledby="createUserModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="createUserModalLabel">Tambah User Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('users.store') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="create_name" class="form-label">
                                Nama <span class="text-danger">*</span>
                            </label>
                            <input type="text"
                                   class="form-control @error('name') is-invalid @enderror"
                                   id="create_name"
                                   name="name"
                                   value="{{ old('name') }}"
                                   placeholder="Masukkan nama user"
                                   required>
                            @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-0">
                            <label for="create_email" class="form-label">
                                Email <span class="text-danger">*</span>
                            </label>
                            <input type="email"
                                   class="form-control @error('email') is-invalid @enderror"
                                   id="create_email"
                                   name="email"
                                   value="{{ old('email') }}"
                                   placeholder="Masukkan email user"
                                   required>
                            @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Password default: <code>password</code></small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="ti ti-device-floppy me-1"></i> Simpan User
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Modal -->
    <div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editUserModalLabel">Edit User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('users.update', ':id') }}" method="POST" id="editUserForm">
                    @csrf
                    @method('PUT')
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="edit_name" class="form-label">
                                Nama <span class="text-danger">*</span>
                            </label>
                            <input type="text"
                                   class="form-control @error('name') is-invalid @enderror"
                                   id="edit_name"
                                   name="name"
                                   value="{{ old('name') }}"
                                   placeholder="Masukkan nama user"
                                   required>
                            @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-0">
                            <label for="edit_email" class="form-label">
                                Email <span class="text-danger">*</span>
                            </label>
                            <input type="email"
                                   class="form-control @error('email') is-invalid @enderror"
                                   id="edit_email"
                                   name="email"
                                   value="{{ old('email') }}"
                                   placeholder="Masukkan email user"
                                   required>
                            @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="ti ti-device-floppy me-1"></i> Perbarui User
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Reset Password Modal -->
    <div class="modal fade" id="resetPasswordModal" tabindex="-1" aria-labelledby="resetPasswordModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="resetPasswordModalLabel">Reset Password</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('users.reset-password', ':id') }}" method="POST" id="resetPasswordForm">
                    @csrf
                    @method('PATCH')
                    <div class="modal-body">
                        <div class="alert alert-warning">
                            <i class="ti ti-alert-triangle me-2"></i>
                            <strong>Peringatan!</strong> Password user <strong id="reset_user_name"></strong> akan direset menjadi: <code>password</code>
                        </div>
                        <p class="mb-0 text-muted">Apakah Anda yakin ingin melanjutkan?</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-warning">
                            <i class="ti ti-refresh me-1"></i> Reset Password
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Delete Modal Component --}}
    <x-delete-modal
        id="deleteUserModal"
        formId="deleteUserForm"
        :route="route('users.destroy', ':id')"
        itemNameId="delete_user_name"
        title="Konfirmasi Hapus User"
        message="Apakah Anda yakin ingin menghapus user"
        itemType="user"
    />
@endsection

@section('scripts')
    @vite(['resources/js/pages/users/user.js'])

    @if($errors->any())
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                @if(old('_method') === 'PUT')
                const editModal = new bootstrap.Modal(document.getElementById('editUserModal'));
                editModal.show();
                @else
                const createModal = new bootstrap.Modal(document.getElementById('createUserModal'));
                createModal.show();
                @endif
            });
        </script>
    @endif

    <script>
        window.userRoutes = {
            index: '{{ route('users.index') }}',
            destroy: '{{ route('users.destroy', ':id') }}',
            resetPassword: '{{ route('users.reset-password', ':id') }}'
        };
    </script>
@endsection
