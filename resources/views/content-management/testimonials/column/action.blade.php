<div class="d-flex gap-2 justify-content-center">
    <button onclick="btnEdit({{ $testimonial->id }}, '{{ addslashes($testimonial->name) }}', '{{ addslashes($testimonial->job) }}', {{ $testimonial->rating }}, '{{ addslashes($testimonial->comment) }}', '{{ asset('storage/' . $testimonial->image_path) }}')"
            class="btn btn-sm btn-soft-warning"
            data-bs-toggle="tooltip"
            title="Edit Testimoni">
        <i class="ti ti-edit"></i>
    </button>

    <button onclick="btnDelete({{ $testimonial->id }}, '{{ addslashes($testimonial->name) }}')"
            class="btn btn-sm btn-soft-danger"
            data-bs-toggle="tooltip"
            title="Hapus Testimoni">
        <i class="ti ti-trash"></i>
    </button>
</div>
