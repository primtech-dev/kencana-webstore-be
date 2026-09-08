<div class="d-flex gap-1 justify-content-center">
    @can('sub_categories.update')
        <a href="{{ route('sub_categories.edit', $c->id) }}" class="btn btn-sm btn-outline-primary" data-bs-toggle="tooltip" title="Edit">
            <i class="ti ti-edit"></i>
        </a>
    @endcan

    @can('sub_categories.delete')
        <button type="button"
                class="btn btn-sm btn-outline-danger js-delete-sub-category"
                data-id="{{ $c->id }}"
                data-name="{{ e($c->name) }}"
                data-bs-toggle="tooltip"
                title="Hapus">
            <i class="ti ti-trash"></i>
        </button>
    @endcan
</div>
