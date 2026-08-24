<div class="d-flex gap-1 justify-content-center">
    @can('meta_keywords.update')
        <a href="{{ route('meta_keywords.edit', $k->id) }}" class="btn btn-sm btn-outline-primary" data-bs-toggle="tooltip" title="Edit">
            <i class="ti ti-edit"></i>
        </a>
    @endcan

    @can('meta_keywords.delete')
        <button type="button"
                class="btn btn-sm btn-outline-danger js-delete-meta-keyword"
                data-id="{{ $k->id }}"
                data-name="{{ e($k->name) }}"
                data-bs-toggle="tooltip"
                title="Hapus">
            <i class="ti ti-trash"></i>
        </button>
    @endcan
</div>
