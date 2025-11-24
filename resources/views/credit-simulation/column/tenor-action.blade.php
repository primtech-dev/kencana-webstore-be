<div class="d-flex gap-2 justify-content-center">
    <button onclick="btnEditTenor({{ $row->id }}, {{ $row->months }})"
            class="btn btn-sm btn-soft-warning"
            data-bs-toggle="tooltip"
            title="Edit Tenor">
        <i class="ti ti-edit"></i>
    </button>

    <button onclick="btnDeleteTenor({{ $row->id }}, '{{ $row->months }} Bulan')"
            class="btn btn-sm btn-soft-danger"
            data-bs-toggle="tooltip"
            title="Hapus Tenor">
        <i class="ti ti-trash"></i>
    </button>
</div>
