@php
    $__current = \App\Enums\OrderStatus::tryFrom((string) $order->status);
    $__allowed = $__current ? array_map(fn ($s) => $s->value, $__current->allowedNext()) : [];
    $__canDelete = in_array($__current, [\App\Enums\OrderStatus::Pending, \App\Enums\OrderStatus::Cancelled], true);
@endphp
<div class="btn-group" role="group">
    @can('orders.view')
        <a href="{{ route('admin.orders.show', $order->id) }}" class="btn btn-sm btn-outline-primary" data-bs-toggle="tooltip" title="Detail">
            <i class="ti ti-eye"></i>
        </a>

        <a href="{{ route('admin.orders.print', $order->id) }}"
           class="btn btn-sm btn-outline-warning"
           data-bs-toggle="tooltip"
           title="Print Order"
           target="_blank">
            <i class="ti ti-printer"></i>
        </a>
    @endcan

    @can('orders.manage')
        <button type="button" class="btn btn-sm btn-outline-success js-change-status"
                data-id="{{ $order->id }}"
                data-no="{{ $order->order_no }}"
                data-status="{{ $order->status }}"
                data-allowed='@json($__allowed)'
                data-bs-toggle="tooltip" title="Ubah Status">
            <i class="ti ti-edit"></i>
        </button>
        @if($__canDelete && auth()->user()->can('orders.delete'))
            <button type="button" class="btn btn-sm btn-outline-danger js-delete-order" data-id="{{ $order->id }}" data-name="{{ e($order->order_no) }}" data-bs-toggle="tooltip" title="Hapus">
                <i class="ti ti-trash"></i>
            </button>
        @endif
    @endcan
</div>
