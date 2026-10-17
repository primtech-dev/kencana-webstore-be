@extends('layouts.vertical', ['title' => 'Detail Order'])

@php
    $statusEnum = \App\Enums\OrderStatus::tryFrom((string) $order->status);
    $allowedNext = $statusEnum ? array_map(fn ($s) => $s->value, $statusEnum->allowedNext()) : [];
    $money = fn ($v) => 'Rp ' . number_format((int) ($v ?? 0), 0, ',', '.');
    $deliveryLabel = ['delivery' => 'Diantar', 'pickup' => 'Ambil di toko'][$order->delivery_method] ?? ($order->delivery_method ?: '-');
@endphp

@section('content')
    @include('layouts.shared.page-title', [
        'title' => 'Detail Order',
        'subTitle' => "Order: {$order->order_no}",
        'breadcrumbs' => [
            ['name' => 'Penjualan', 'url' => route('admin.orders.index')],
            ['name' => 'Detail Order']
        ]
    ])

    <div class="row">
        <div class="col-lg-8">
            <div class="card mb-3">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Informasi Order</h5>

                    <div>
                        @can('orders.manage')
                            <button type="button" class="btn btn-sm btn-outline-success me-1 js-change-status"
                                    data-id="{{ $order->id }}"
                                    data-no="{{ $order->order_no }}"
                                    data-status="{{ $order->status }}"
                                    data-allowed='@json($allowedNext)'>
                                <i data-lucide="box" class="me-1"></i> Ubah Status
                            </button>
                        @endcan

                        @can('orders.view')
                            <a href="{{ route('admin.orders.print', $order->id) }}" target="_blank" class="btn btn-sm btn-outline-warning me-1">
                                <i data-lucide="printer" class="me-1"></i> Print
                            </a>
                            <a href="{{ route('admin.orders.index') }}" class="btn btn-sm btn-outline-secondary">
                                <i data-lucide="arrow-left-circle" class="me-1"></i> Kembali
                            </a>
                        @endcan
                    </div>
                </div>

                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <dl class="row mb-0 small">
                                <dt class="col-5">No. Order</dt><dd class="col-7">{{ $order->order_no }}</dd>
                                <dt class="col-5">Status</dt>
                                <dd class="col-7"><span class="badge bg-{{ \App\Enums\OrderStatus::badgeFor($order->status) }}">{{ \App\Enums\OrderStatus::labelFor($order->status) }}</span></dd>
                                <dt class="col-5">Tanggal</dt><dd class="col-7">{{ ($order->placed_at ?? $order->created_at)?->format('d M Y H:i') ?? '-' }}</dd>
                                <dt class="col-5">Cabang</dt><dd class="col-7">{{ $order->branch->name ?? '-' }}</dd>
                                <dt class="col-5">Pengiriman</dt><dd class="col-7">{{ $deliveryLabel }}</dd>
                            </dl>
                        </div>
                        <div class="col-md-6">
                            <dl class="row mb-0 small">
                                <dt class="col-5">Pelanggan</dt><dd class="col-7">{{ $order->customer->full_name ?? '-' }}</dd>
                                <dt class="col-5">Telepon</dt><dd class="col-7">{{ $order->customer->phone ?? '-' }}</dd>
                                <dt class="col-5">Email</dt><dd class="col-7">{{ $order->customer->email ?? '-' }}</dd>
                                <dt class="col-5">Pembayaran</dt>
                                <dd class="col-7">{{ $order->paymentMethod->name ?? '-' }}
                                    <span class="badge bg-{{ $order->payment_status === 'paid' ? 'success' : ($order->payment_status === 'refunded' ? 'dark' : 'warning') }}">{{ ucfirst($order->payment_status ?? '-') }}</span>
                                </dd>
                            </dl>
                        </div>
                    </div>

                    <h6>Items</h6>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered mb-0">
                            <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>SKU</th>
                                <th>Produk</th>
                                <th class="text-end">Harga</th>
                                <th class="text-center">Qty</th>
                                <th class="text-end">Diskon</th>
                                <th class="text-end">Total Item</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($order->items as $idx => $item)
                                <tr>
                                    <td>{{ $idx + 1 }}</td>
                                    <td>{{ $item->sku }}</td>
                                    <td>{{ $item->product_name }}</td>
                                    <td class="text-end">{{ $money($item->unit_price) }}</td>
                                    <td class="text-center">{{ \App\Support\Qty::fmt($item->line_quantity) }} {{ $item->unit->name ?? '' }}</td>
                                    <td class="text-end">{{ $money($item->line_discount) }}</td>
                                    <td class="text-end">{{ $money($item->line_total) }}</td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="small text-muted mt-1">Total item sudah termasuk diskon dan pajak per baris.</div>

                    <div class="row mt-3">
                        <div class="col-md-6">
                            <h6>Alamat Pengiriman & Notes</h6>

                            @if($order->address)
                                @php
                                    $addr = $order->address;
                                    $lines = [];
                                    if (!empty($addr->label)) $lines[] = e($addr->label);
                                    $lines[] = e($order->customer->full_name ?? '');
                                    if (!empty($addr->phone)) $lines[] = 'Telp: ' . e($addr->phone);
                                    if (!empty($addr->street)) $lines[] = e($addr->street);
                                    $cityLine = trim(($addr->city ?? '') . ' ' . ($addr->postal_code ?? ''));
                                    if ($cityLine) $lines[] = e($cityLine);
                                    if (!empty($addr->province)) $lines[] = e($addr->province);
                                    if (!empty($addr->country)) $lines[] = e($addr->country);
                                    $lines = array_filter($lines);
                                @endphp
                                <div class="border rounded p-2 small mb-2">
                                    @foreach($lines as $ln)
                                        <div>{!! $ln !!}</div>
                                    @endforeach
                                </div>
                            @elseif(isset($order->meta['shipping_address']))
                                <div class="border rounded p-2 small mb-2">
                                    <pre class="small mb-0">{{ json_encode($order->meta['shipping_address'], JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE) }}</pre>
                                </div>
                            @else
                                <div class="text-muted mb-2"><em>{{ $order->delivery_method === 'pickup' ? 'Ambil di toko (tanpa alamat pengiriman)' : 'Tidak ada alamat pengiriman' }}</em></div>
                            @endif

                            <div>
                                <p class="mb-0"><strong>Notes:</strong></p>
                                <div class="small text-wrap">{!! nl2br(e($order->notes ?? '-')) !!}</div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <h6>Ringkasan Biaya</h6>
                            <table class="table table-sm table-borderless">
                                <tr><td>Subtotal sebelum diskon</td><td class="text-end">{{ $money($order->subtotal_before_discount) }}</td></tr>
                                <tr><td>Total Diskon</td><td class="text-end">- {{ $money($order->discount_total) }}</td></tr>
                                <tr><td>Subtotal setelah diskon</td><td class="text-end">{{ $money($order->subtotal_after_discount) }}</td></tr>
                                <tr><td>Biaya Kirim</td><td class="text-end">{{ $money($order->shipping_cost) }}</td></tr>
                                @if((int) $order->other_charges)
                                    <tr><td>Biaya Lain</td><td class="text-end">{{ $money($order->other_charges) }}</td></tr>
                                @endif
                                <tr><td>Pajak</td><td class="text-end">{{ $money($order->tax_total) }}</td></tr>
                                <tr class="fw-bold border-top"><td>Total</td><td class="text-end">{{ $money($order->total_amount) }}</td></tr>
                            </table>
                        </div>
                    </div>

                </div>
            </div>

        </div>

        <div class="col-lg-4">
            <div class="card mb-3">
                <div class="card-header"><h6 class="mb-0">Riwayat Status</h6></div>
                <ul class="list-group list-group-flush">
                    @forelse($order->statusHistories as $h)
                        <li class="list-group-item small">
                            <div>
                                @if($h->from_status)
                                    <span class="badge bg-{{ \App\Enums\OrderStatus::badgeFor($h->from_status) }}">{{ \App\Enums\OrderStatus::labelFor($h->from_status) }}</span>
                                    &rarr;
                                @endif
                                <span class="badge bg-{{ \App\Enums\OrderStatus::badgeFor($h->to_status) }}">{{ \App\Enums\OrderStatus::labelFor($h->to_status) }}</span>
                            </div>
                            <div class="text-muted">{{ $h->created_at?->format('d M Y H:i') }} &middot; {{ $h->user->name ?? 'Sistem' }}</div>
                            @if($h->note)<div>{{ $h->note }}</div>@endif
                        </li>
                    @empty
                        <li class="list-group-item small text-muted">Belum ada perubahan status dari backoffice.</li>
                    @endforelse
                </ul>
            </div>

            <div class="card">
                <div class="card-header"><h6 class="mb-0">Metadata & Audit</h6></div>
                <div class="card-body small">
                    <p class="mb-1"><strong>Public ID:</strong> {{ $order->public_id }}</p>
                    <p class="mb-1"><strong>Dibuat:</strong> {{ $order->created_at?->format('d M Y H:i') }}</p>
                    <p class="mb-1"><strong>Diperbarui:</strong> {{ $order->updated_at?->format('d M Y H:i') }}</p>
                    <p class="mb-0"><strong>Dibatalkan:</strong> {{ $order->cancelled_at?->format('d M Y H:i') ?? '-' }}</p>
                </div>
            </div>
        </div>
    </div>

    @include('orders._change_status_modal', ['order' => $order])
@endsection

@section('scripts')
    @vite(['resources/js/pages/orders/orders.js'])
@endsection
