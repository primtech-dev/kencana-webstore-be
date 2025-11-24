@extends('layouts.vertical', ['title' => 'Tabel Angsuran'])

@section('styles')
    <style>
        .installment-row {
            transition: background-color 0.2s ease;
        }
        .installment-row:hover {
            background-color: #f8f9fa;
        }
        .installment-input {
            min-width: 200px;
        }
        .installment-row:hover .remove-row-btn {
            opacity: 1;
        }
    </style>
@endsection

@section('content')
    @include('layouts.shared.page-title', [
        'title' => 'Tabel Angsuran',
        'subTitle' => 'Kelola angsuran untuk jumlah pencairan Rp ' . number_format($loanAmount->amount, 0, ',', '.'),
        'breadcrumbs' => [
            ['name' => 'Simulasi Kredit', 'url' => route('credit-simulation.index')],
            ['name' => 'Tabel Angsuran']
        ]
    ])

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="card-title mb-1">Pengaturan Angsuran</h5>
                        <p class="text-muted mb-0">Jumlah Pencairan: <strong>Rp {{ number_format($loanAmount->amount, 0, ',', '.') }}</strong></p>
                    </div>
                    <a href="{{ route('credit-simulation.index') }}" class="btn btn-secondary">
                        <i class="ti ti-arrow-left me-1"></i> Kembali
                    </a>
                </div>
                <div class="card-body">
                    <form action="{{ route('credit-simulation.bulk-save-installments') }}" method="POST" id="installmentForm">
                        @csrf

                        <div class="table-responsive">
                            <table class="table table-bordered align-middle">
                                <thead class="table-light">
                                <tr>
                                    <th width="5%">No</th>
                                    <th width="25%">Tenor</th>
                                    <th width="35%">Angsuran per Bulan</th>
                                    <th width="10%" class="text-center">Aksi</th>
                                </tr>
                                </thead>
                                <tbody id="installmentTableBody">
                                @foreach($assignedTenors as $index => $assigned)
                                    <tr class="installment-row" data-row-index="{{ $index }}">
                                        <td class="text-center">{{ $index + 1 }}</td>
                                        <td>
                                            <input type="hidden" name="installments[{{ $index }}][loan_amount_id]" value="{{ $loanAmount->id }}">
                                            <input type="hidden" name="installments[{{ $index }}][tenor_id]" value="{{ $assigned->tenor->id }}">
                                            <span class="fw-semibold">{{ $assigned->tenor->months }} Bulan</span>
                                        </td>
                                        <td>
                                            <input type="number"
                                                   class="form-control installment-input"
                                                   name="installments[{{ $index }}][installment]"
                                                   value="{{ floatval($assigned->installment) }}"
                                                   placeholder="Masukkan angsuran"
                                                   min="0"
                                                   step="0.01"
                                                   required>
                                        </td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-sm btn-soft-danger remove-row-btn" onclick="removeRow(this)">
                                                <i class="ti ti-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>

                        @if(count($unassignedTenors) > 0)
                            <div class="mt-3">
                                <button type="button" class="btn btn-outline-primary" onclick="addNewRow()">
                                    <i class="ti ti-plus me-1"></i> Tambah Tenor Baru
                                </button>
                            </div>
                        @endif

                        <div class="mt-4 d-flex justify-content-end gap-2">
                            <a href="{{ route('credit-simulation.index') }}" class="btn btn-secondary">
                                Batal
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="ti ti-device-floppy me-1"></i> Simpan Semua Perubahan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Template for new row -->
    <template id="newRowTemplate">
        <tr class="installment-row" data-row-index="">
            <td class="text-center row-number"></td>
            <td>
                <input type="hidden" name="installments[][loan_amount_id]" value="{{ $loanAmount->id }}">
                <select class="form-select tenor-select" name="installments[][tenor_id]" required>
                    <option value="">Pilih Tenor</option>
                    @foreach($unassignedTenors as $tenor)
                        <option value="{{ $tenor->id }}" data-months="{{ $tenor->months }}">{{ $tenor->months }} Bulan</option>
                    @endforeach
                </select>
            </td>
            <td>
                <input type="number"
                       class="form-control installment-input"
                       name="installments[][installment]"
                       placeholder="Masukkan angsuran"
                       min="0"
                       step="0.01"
                       required>
            </td>
            <td class="text-center">
                <button type="button" class="btn btn-sm btn-soft-danger remove-row-btn" onclick="removeRow(this)">
                    <i class="ti ti-trash"></i>
                </button>
            </td>
        </tr>
    </template>
@endsection

@section('scripts')
    @vite(['resources/js/pages/credit-simulations/installment.js'])

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
        // Pass unassigned tenors to JavaScript
        window.unassignedTenors = @json($unassignedTenors);
        window.loanAmountId = {{ $loanAmount->id }};
    </script>
@endsection
