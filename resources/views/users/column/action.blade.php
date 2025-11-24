<div class="d-flex gap-2 justify-content-center">
    <button onclick="btnEdit({{ $user->id }}, '{{ addslashes($user->name) }}', '{{ addslashes($user->email) }}')"
            class="btn btn-sm btn-soft-warning"
            data-bs-toggle="tooltip"
            title="Edit User">
        <i class="ti ti-edit"></i>
    </button>

    <button onclick="btnResetPassword({{ $user->id }}, '{{ addslashes($user->name) }}')"
            class="btn btn-sm btn-soft-info"
            data-bs-toggle="tooltip"
            title="Reset Password">
        <i class="ti ti-refresh"></i>
    </button>

    <button onclick="btnDelete({{ $user->id }}, '{{ addslashes($user->name) }}')"
            class="btn btn-sm btn-soft-danger"
            data-bs-toggle="tooltip"
            title="Hapus User">
        <i class="ti ti-trash"></i>
    </button>
</div>
