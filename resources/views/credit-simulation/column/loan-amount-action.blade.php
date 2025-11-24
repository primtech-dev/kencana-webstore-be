<div class="d-flex gap-2 justify-content-center">
    <a href="{{ route('credit-simulation.show-installments', $row->id) }}"
       class="btn btn-sm btn-soft-info"
       data-bs-toggle="tooltip"
       title="Lihat Tabel Angsuran">
        <i class="ti ti-table"></i>
    </a>

    <button onclick="btnEditLoanAmount({{ $row->id }}, {{ $row->amount }})"
            class="btn btn-sm btn-soft-warning"
            data-bs-toggle="tooltip"
            title="Edit Jumlah Pencairan">
        <i class="ti ti-edit"></i>
    </button>

    <button onclick="btnDeleteLoanAmount({{ $row->id }}, 'Rp {{ number_format($row->amount, 0, ',', '.') }}')"
            class="btn btn-sm btn-soft-danger"
            data-bs-toggle="tooltip"
            title="Hapus Jumlah Pencairan">
        <i class="ti ti-trash"></i>
    </button>
</div>
