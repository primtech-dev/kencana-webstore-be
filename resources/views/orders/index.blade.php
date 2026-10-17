@extends('layouts.vertical', ['title' => 'Manajemen Order'])

@section('styles')
    @vite(['node_modules/datatables.net-responsive-bs5/css/responsive.bootstrap5.min.css'])
@endsection

@section('content')
    @include('layouts.shared.page-title', [
        'title' => 'Manajemen Order',
        'subTitle' => 'Kelola pesanan pelanggan',
        'breadcrumbs' => [
            ['name' => 'Penjualan', 'url' => '#'],
            ['name' => 'Order']
        ]
    ])

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Daftar Order</h5>
                </div>

                <div class="card-body">
                    <div class="row g-2 mb-3" id="orderFilters">
                        <div class="col-6 col-md-2">
                            <label class="form-label small mb-1">Status</label>
                            <select id="filter_status" class="form-select form-select-sm">
                                <option value="">Semua</option>
                                @foreach($statuses as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-6 col-md-2">
                            <label class="form-label small mb-1">Pembayaran</label>
                            <select id="filter_payment_status" class="form-select form-select-sm">
                                <option value="">Semua</option>
                                @foreach($paymentStatuses as $ps)
                                    <option value="{{ $ps }}">{{ ucfirst($ps) }}</option>
                                @endforeach
                            </select>
                        </div>
                        @if($showBranch)
                            <div class="col-6 col-md-2">
                                <label class="form-label small mb-1">Cabang</label>
                                <select id="filter_branch" class="form-select form-select-sm">
                                    <option value="">Semua cabang</option>
                                    @foreach($branches as $b)
                                        <option value="{{ $b->id }}">{{ $b->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endif
                        <div class="col-6 col-md-2">
                            <label class="form-label small mb-1">Dari tanggal</label>
                            <input type="date" id="filter_date_from" class="form-control form-control-sm">
                        </div>
                        <div class="col-6 col-md-2">
                            <label class="form-label small mb-1">Sampai tanggal</label>
                            <input type="date" id="filter_date_to" class="form-control form-control-sm">
                        </div>
                        <div class="col-6 col-md-2 d-flex align-items-end">
                            <button type="button" id="btnResetFilters" class="btn btn-sm btn-outline-secondary">Reset</button>
                        </div>
                    </div>

                    <table class="table table-striped dt-responsive align-middle w-100" id="orders-table">
                        <thead class="thead-sm text-uppercase fs-xxs">
                        <tr>
                            <th width="5%">No</th>
                            <th>No. Order</th>
                            <th>Pelanggan</th>
                            @if($showBranch)<th>Cabang</th>@endif
                            <th width="12%" class="text-end">Total</th>
                            <th width="10%">Status</th>
                            <th width="12%">Tanggal</th>
                            <th width="12%" class="text-center">Aksi</th>
                        </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>

    <x-delete-modal
        id="deleteOrderModal"
        formId="deleteOrderForm"
        :route="route('admin.orders.destroy', ':id')"
        itemNameId="delete_order_title"
        title="Konfirmasi Hapus Order"
        message="Apakah Anda yakin ingin menghapus order ini? Hanya order Pending/Dibatalkan yang dapat dihapus."
        itemType="order"
    />

    @include('orders._change_status_modal')
@endsection

@section('scripts')
    @vite(['resources/js/pages/orders/orders.js'])

    <script>
        window.orderRoutes = {
            index: '{{ route('admin.orders.index') }}',
            show: '{{ route('admin.orders.show', ':id') }}',
            destroy: '{{ route('admin.orders.destroy', ':id') }}',
            showBranch: @json($showBranch)
        };
    </script>
@endsection
