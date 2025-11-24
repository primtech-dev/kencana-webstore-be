<div class="d-flex gap-2 justify-content-center">
    <button onclick="btnEdit({{ $faq->id }}, '{{ addslashes($faq->question) }}', '{{ addslashes($faq->answer) }}')"
            class="btn btn-sm btn-soft-warning"
            data-bs-toggle="tooltip"
            title="Edit FAQ">
        <i class="ti ti-edit"></i>
    </button>

    <button onclick="btnDelete({{ $faq->id }}, '{{ addslashes($faq->question) }}')"
            class="btn btn-sm btn-soft-danger"
            data-bs-toggle="tooltip"
            title="Hapus FAQ">
        <i class="ti ti-trash"></i>
    </button>
</div>
