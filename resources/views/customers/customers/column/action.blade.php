<div class="d-flex gap-2 justify-content-center">
    <button onclick="btnEdit({{ $customer->id }}, '{{ addslashes($customer->name) }}', '{{ $customer->car_unit }}', '{{ addslashes($customer->phone_number) }}', '{{ addslashes($customer->address) }}')"
            class="btn btn-sm btn-soft-warning"
            data-bs-toggle="tooltip"
            title="Edit Customer">
        <i class="ti ti-edit"></i>
    </button>

    <button onclick="btnDelete({{ $customer->id }}, '{{ addslashes($customer->name) }}')"
            class="btn btn-sm btn-soft-danger"
            data-bs-toggle="tooltip"
            title="Hapus Customer">
        <i class="ti ti-trash"></i>
    </button>
</div>
