<div class="d-flex gap-2 justify-content-center">
    <a href="{{ route('products.edit', $product->id) }}"
       class="btn btn-sm btn-soft-warning"
       data-bs-toggle="tooltip"
       title="Edit Product">
        <i class="ti ti-edit"></i>
    </a>

    <button onclick="btnDelete({{ $product->id }}, '{{ addslashes($product->title) }}')"
            class="btn btn-sm btn-soft-danger"
            data-bs-toggle="tooltip"
            title="Hapus Product">
        <i class="ti ti-trash"></i>
    </button>
</div>
