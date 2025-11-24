@extends('layouts.vertical', ['title' => 'Manajemen Customer'])

@section('styles')
    @vite(['node_modules/datatables.net-responsive-bs5/css/responsive.bootstrap5.min.css'])
@endsection

@section('content')
    @include('layouts.shared.page-title', [
        'title' => 'Manajemen Pelanggan',
        'subTitle' => 'Kelola data pelanggan untuk meningkatkan layanan dan hubungan bisnis.',
        'breadcrumbs' => [
            ['name' => 'Pelanggan', 'url' => '#'],
            ['name' => 'Daftar Pelanggan']
        ]
    ])

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Daftar Pelanggan</h5>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createCustomerModal">
                        <i class="ti ti-plus me-1"></i> Tambah Pelanggan
                    </button>
                </div>
                <div class="card-body">
                    <table class="table table-striped dt-responsive align-middle w-100" id="customers-table">
                        <thead class="thead-sm text-uppercase fs-xxs">
                        <tr>
                            <th width="5%">No</th>
                            <th width="20%">Nama</th>
                            <th width="10%">Unit Mobil</th>
                            <th width="25%">Alamat</th>
                            <th width="15%">No. Telepon</th>
                            <th width="15%">Dibuat Pada</th>
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
    <div class="modal fade" id="createCustomerModal" tabindex="-1" aria-labelledby="createCustomerModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="createCustomerModalLabel">Tambah Pelanggan Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('customers.store') }}" method="POST" id="createCustomerForm">
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
                                   placeholder="Masukkan nama pelanggan"
                                   maxlength="255"
                                   required>
                            @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="create_car_unit" class="form-label">
                                Unit Mobil <span class="text-danger">*</span>
                            </label>
                            <input type="text"
                                   class="form-control @error('car_unit') is-invalid @enderror"
                                   id="create_car_unit"
                                   name="car_unit"
                                   value="{{ old('car_unit') }}"
                                   placeholder="Masukkan Unit Mobil"
                                   maxlength="255"
                                   required>
                            @error('car_unit')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="create_phone_number" class="form-label">
                                No. Telepon <span class="text-danger">*</span>
                            </label>
                            <input type="text"
                                   class="form-control @error('phone_number') is-invalid @enderror"
                                   id="create_phone_number"
                                   name="phone_number"
                                   value="{{ old('phone_number') }}"
                                   placeholder="Masukkan nomor telepon (10-15 digit)"
                                   pattern="[0-9]{10,15}"
                                   required>
                            @error('phone_number')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Format: 10-15 digit angka</small>
                        </div>

                        <div class="mb-0">
                            <label for="create_address" class="form-label">
                                Alamat <span class="text-danger">*</span>
                            </label>
                            <textarea class="form-control @error('address') is-invalid @enderror"
                                      id="create_address"
                                      name="address"
                                      rows="3"
                                      placeholder="Masukkan alamat lengkap"
                                      maxlength="255"
                                      required>{{ old('address') }}</textarea>
                            @error('address')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="ti ti-device-floppy me-1"></i> Simpan Pelanggan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Modal -->
    <div class="modal fade" id="editCustomerModal" tabindex="-1" aria-labelledby="editCustomerModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editCustomerModalLabel">Edit Pelanggan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('customers.update', ':id') }}" method="POST" id="editCustomerForm">
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
                                   placeholder="Masukkan nama pelanggan"
                                   maxlength="255"
                                   required>
                            @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="edit_car_unit" class="form-label">
                                Nama <span class="text-danger">*</span>
                            </label>
                            <input type="text"
                                   class="form-control @error('car_unit') is-invalid @enderror"
                                   id="edit_car_unit"
                                   name="car_unit"
                                   value="{{ old('car_unit') }}"
                                   placeholder="Masukkan unit mobil"
                                   maxlength="255"
                                   required>
                            @error('car_unit')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>


                        <div class="mb-3">
                            <label for="edit_phone_number" class="form-label">
                                No. Telepon <span class="text-danger">*</span>
                            </label>
                            <input type="text"
                                   class="form-control @error('phone_number') is-invalid @enderror"
                                   id="edit_phone_number"
                                   name="phone_number"
                                   value="{{ old('phone_number') }}"
                                   placeholder="Masukkan nomor telepon (10-15 digit)"
                                   pattern="[0-9]{10,15}"
                                   required>
                            @error('phone_number')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Format: 10-15 digit angka</small>
                        </div>

                        <div class="mb-0">
                            <label for="edit_address" class="form-label">
                                Alamat <span class="text-danger">*</span>
                            </label>
                            <textarea class="form-control @error('address') is-invalid @enderror"
                                      id="edit_address"
                                      name="address"
                                      rows="3"
                                      placeholder="Masukkan alamat lengkap"
                                      maxlength="255"
                                      required>{{ old('address') }}</textarea>
                            @error('address')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="ti ti-device-floppy me-1"></i> Perbarui Customer
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Delete Modal Component --}}
    <x-delete-modal
        id="deleteCustomerModal"
        formId="deleteCustomerForm"
        :route="route('customers.destroy', ':id')"
        itemNameId="delete_customer_name"
        title="Konfirmasi Hapus Pelanggan"
        message="Apakah Anda yakin ingin menghapus pelanggan"
        itemType="customer"
    />
@endsection

@section('scripts')
    @vite(['resources/js/pages/customers/customer.js'])

    @if($errors->any())
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                @if(old('_method') === 'PUT')
                const editModal = new bootstrap.Modal(document.getElementById('editCustomerModal'));
                editModal.show();
                @else
                const createModal = new bootstrap.Modal(document.getElementById('createCustomerModal'));
                createModal.show();
                @endif
            });
        </script>
    @endif

    <script>
        window.customerRoutes = {
            index: '{{ route('customers.index') }}',
            destroy: '{{ route('customers.destroy', ':id') }}'
        };
    </script>
@endsection
