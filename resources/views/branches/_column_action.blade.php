<div class="d-flex gap-1 justify-content-center">
    @can('branches.view')
        <a href="{{ route('branches.show', $b->id) }}"
           class="btn btn-sm btn-outline-info"
           data-bs-toggle="tooltip"
           title="Detail">
            <i class="ti ti-eye"></i>
        </a>
    @endcan

    @can('branches.update')
        <a href="{{ route('branches.edit', $b->id) }}"
           class="btn btn-sm btn-outline-primary"
           data-bs-toggle="tooltip"
           title="Edit">
            <i class="ti ti-edit"></i>
        </a>
    @endcan

    @if(auth()->user()->hasAllBranchScope())
    @can('branches.delete')
        <button type="button"
                class="btn btn-sm btn-outline-danger js-delete-branch"
                data-id="{{ $b->id }}"
                data-name="{{ e($b->name) }}"
                data-bs-toggle="tooltip"
                title="Hapus">
            <i class="ti ti-trash"></i>
        </button>
    @endcan
    @endif
</div>
