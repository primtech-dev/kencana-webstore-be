<div class="d-flex gap-2 justify-content-center">
    <button onclick="btnEdit({{ $tag->id }}, '{{ addslashes($tag->name) }}')"
            class="btn btn-sm btn-soft-warning"
            data-bs-toggle="tooltip"
            title="Edit Tag">
        <i class="ti ti-edit"></i>
    </button>

    <button onclick="btnDelete({{ $tag->id }}, '{{ addslashes($tag->question) }}')"
            class="btn btn-sm btn-soft-danger"
            data-bs-toggle="tooltip"
            title="Hapus Tag">
        <i class="ti ti-trash"></i>
    </button>
</div>
