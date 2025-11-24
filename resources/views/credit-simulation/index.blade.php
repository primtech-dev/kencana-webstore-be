@extends('layouts.vertical', ['title' => 'Simulasi Kredit'])

@section('styles')
    @vite(['node_modules/datatables.net-responsive-bs5/css/responsive.bootstrap5.min.css'])
@endsection

@section('content')
    @include('layouts.shared.page-title', [
        'title' => 'Simulasi Kredit',
        'subTitle' => 'Kelola jumlah pencairan dan tenor untuk simulasi kredit',
        'breadcrumbs' => [
            ['name' => 'Simulasi Kredit', 'url' => '#'],
            ['name' => 'Daftar Simulasi']
        ]
    ])

    <div class="row">
        <!-- Loan Amount Table -->
        <div class="col-lg-7">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Jumlah Pencairan</h5>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createLoanAmountModal">
                        <i class="ti ti-plus me-1"></i> Tambah Jumlah Pencairan
                    </button>
                </div>
                <div class="card-body">
                    <table class="table table-striped dt-responsive align-middle w-100" id="loan-amount-table">
                        <thead class="thead-sm text-uppercase fs-xxs">
                        <tr>
                            <th width="10%">No</th>
                            <th width="60%">Jumlah Pencairan</th>
                            <th width="30%" class="text-center">Aksi</th>
                        </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Tenor Table -->
        <div class="col-lg-5">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Tenor</h5>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createTenorModal">
                        <i class="ti ti-plus me-1"></i> Tambah Tenor
                    </button>
                </div>
                <div class="card-body">
                    <table class="table table-striped dt-responsive align-middle w-100" id="tenor-table">
                        <thead class="thead-sm text-uppercase fs-xxs">
                        <tr>
                            <th width="15%">No</th>
                            <th width="55%">Tenor (Bulan)</th>
                            <th width="30%" class="text-center">Aksi</th>
                        </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Create Loan Amount Modal -->
    <div class="modal fade" id="createLoanAmountModal" tabindex="-1" aria-labelledby="createLoanAmountModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="createLoanAmountModalLabel">Tambah Jumlah Pencairan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('credit-simulation.store-loan-amount') }}" method="POST" id="createLoanAmountForm">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-0">
                            <label for="create_amount" class="form-label">
                                Jumlah Pencairan <span class="text-danger">*</span>
                            </label>
                            <input type="number"
                                   class="form-control @error('amount') is-invalid @enderror"
                                   id="create_amount"
                                   name="amount"
                                   value="{{ old('amount') }}"
                                   placeholder="Masukkan jumlah pencairan"
                                   min="1"
                                   step="1"
                                   required>
                            @error('amount')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Contoh: 5000000 untuk Rp 5.000.000</small>
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

    <!-- Edit Loan Amount Modal -->
    <div class="modal fade" id="editLoanAmountModal" tabindex="-1" aria-labelledby="editLoanAmountModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editLoanAmountModalLabel">Edit Jumlah Pencairan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('credit-simulation.update-loan-amount', ':id') }}" method="POST" id="editLoanAmountForm">
                    @csrf
                    @method('PUT')
                    <div class="modal-body">
                        <div class="mb-0">
                            <label for="edit_amount" class="form-label">
                                Jumlah Pencairan <span class="text-danger">*</span>
                            </label>
                            <input type="number"
                                   class="form-control"
                                   id="edit_amount"
                                   name="amount"
                                   placeholder="Masukkan jumlah pencairan"
                                   min="1"
                                   step="1"
                                   required>
                            <small class="text-muted">Contoh: 5000000 untuk Rp 5.000.000</small>
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

    <!-- Create Tenor Modal -->
    <div class="modal fade" id="createTenorModal" tabindex="-1" aria-labelledby="createTenorModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="createTenorModalLabel">Tambah Tenor</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('credit-simulation.store-tenor') }}" method="POST" id="createTenorForm">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-0">
                            <label for="create_months" class="form-label">
                                Tenor (Bulan) <span class="text-danger">*</span>
                            </label>
                            <input type="number"
                                   class="form-control @error('months') is-invalid @enderror"
                                   id="create_months"
                                   name="months"
                                   value="{{ old('months') }}"
                                   placeholder="Masukkan tenor dalam bulan"
                                   min="1"
                                   step="1"
                                   required>
                            @error('months')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Contoh: 12 untuk 12 bulan</small>
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

    <!-- Edit Tenor Modal -->
    <div class="modal fade" id="editTenorModal" tabindex="-1" aria-labelledby="editTenorModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editTenorModalLabel">Edit Tenor</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('credit-simulation.update-tenor', ':id') }}" method="POST" id="editTenorForm">
                    @csrf
                    @method('PUT')
                    <div class="modal-body">
                        <div class="mb-0">
                            <label for="edit_months" class="form-label">
                                Tenor (Bulan) <span class="text-danger">*</span>
                            </label>
                            <input type="number"
                                   class="form-control"
                                   id="edit_months"
                                   name="months"
                                   placeholder="Masukkan tenor dalam bulan"
                                   min="1"
                                   step="1"
                                   required>
                            <small class="text-muted">Contoh: 12 untuk 12 bulan</small>
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

    {{-- Delete Loan Amount Modal --}}
    <x-delete-modal
        id="deleteLoanAmountModal"
        formId="deleteLoanAmountForm"
        :route="route('credit-simulation.destroy-loan-amount', ':id')"
        itemNameId="delete_loan_amount_name"
        title="Konfirmasi Hapus Jumlah Pencairan"
        message="Apakah Anda yakin ingin menghapus jumlah pencairan"
        itemType="loan_amount"
    />

    {{-- Delete Tenor Modal --}}
    <x-delete-modal
        id="deleteTenorModal"
        formId="deleteTenorForm"
        :route="route('credit-simulation.destroy-tenor', ':id')"
        itemNameId="delete_tenor_name"
        title="Konfirmasi Hapus Tenor"
        message="Apakah Anda yakin ingin menghapus tenor"
        itemType="tenor"
    />
@endsection

@section('scripts')
    @vite(['resources/js/pages/credit-simulations/credit-simulation.js'])

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

    <script>
        window.creditSimulationRoutes = {
            indexLoanAmount: '{{ route('credit-simulation.index') }}?type=loan_amount',
            indexTenor: '{{ route('credit-simulation.index') }}?type=tenor',
            destroyLoanAmount: '{{ route('credit-simulation.destroy-loan-amount', ':id') }}',
            destroyTenor: '{{ route('credit-simulation.destroy-tenor', ':id') }}',
            showInstallments: '{{ route('credit-simulation.show-installments', ':id') }}'
        };
    </script>
@endsection
