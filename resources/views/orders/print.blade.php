@php
    $money = fn ($v) => 'Rp ' . number_format((int) ($v ?? 0), 0, ',', '.');
    $deliveryLabel = ['delivery' => 'Diantar', 'pickup' => 'Ambil di toko'][$order->delivery_method] ?? ($order->delivery_method ?: '-');
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Print Order {{ $order->order_no }}</title>
    <style>
        @page { size: A4; margin: 15mm; }
        body { font-family: Arial, sans-serif; font-size: 13px; color: #000; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #000; padding: 5px 6px; text-align: left; vertical-align: top; }
        th { background: #f0f0f0; }
        .right { text-align: right; }
        .center { text-align: center; }
        .header { margin-bottom: 16px; display: flex; justify-content: space-between; gap: 24px; }
        .header p { margin: 2px 0; }
        .totals { width: 55%; margin-left: auto; }
        .totals td { border: none; padding: 3px 6px; }
        .totals tr.grand td { border-top: 2px solid #000; font-weight: bold; }
        @media print { .no-print { display: none; } }
    </style>
</head>
<body>

<h2 style="margin:0 0 4px">{{ $order->branch->name ?? 'Kencana' }}</h2>
@if($order->branch?->address)<div>{{ $order->branch->address }} {{ $order->branch->city }}</div>@endif
@if($order->branch?->phone)<div>Telp: {{ $order->branch->phone }}</div>@endif
<hr>

<div class="header">
    <div>
        <h3 style="margin:0 0 6px">Order {{ $order->order_no }}</h3>
        <p><strong>Tanggal:</strong> {{ ($order->placed_at ?? $order->created_at)?->format('d M Y H:i') }}</p>
        <p><strong>Status:</strong> {{ \App\Enums\OrderStatus::labelFor($order->status) }}</p>
        <p><strong>Pembayaran:</strong> {{ $order->paymentMethod->name ?? '-' }} ({{ ucfirst($order->payment_status ?? '-') }})</p>
        <p><strong>Pengiriman:</strong> {{ $deliveryLabel }}</p>
    </div>
    <div>
        <p><strong>Pelanggan:</strong> {{ $order->customer->full_name ?? '-' }}</p>
        <p><strong>Telepon:</strong> {{ $order->address->phone ?? $order->customer->phone ?? '-' }}</p>
        @if($order->address)
            <p><strong>Alamat:</strong><br>
                {{ $order->address->street }}<br>
                {{ $order->address->city }}, {{ $order->address->province }} {{ $order->address->postal_code }}
            </p>
        @endif
    </div>
</div>

<table>
    <thead>
    <tr>
        <th width="4%">#</th>
        <th width="14%">SKU</th>
        <th>Produk</th>
        <th width="12%" class="center">Qty</th>
        <th width="14%" class="right">Harga</th>
        <th width="16%" class="right">Total</th>
    </tr>
    </thead>
    <tbody>
    @foreach($order->items as $i => $item)
        <tr>
            <td>{{ $i + 1 }}</td>
            <td>{{ $item->sku }}</td>
            <td>{{ $item->product_name }}</td>
            <td class="center">{{ \App\Support\Qty::fmt($item->line_quantity) }} {{ $item->unit->name ?? '' }}</td>
            <td class="right">{{ $money($item->unit_price) }}</td>
            <td class="right">{{ $money($item->line_total) }}</td>
        </tr>
    @endforeach
    </tbody>
</table>

<table class="totals">
    <tr><td>Subtotal</td><td class="right">{{ $money($order->subtotal_before_discount) }}</td></tr>
    <tr><td>Diskon</td><td class="right">- {{ $money($order->discount_total) }}</td></tr>
    <tr><td>Biaya Kirim</td><td class="right">{{ $money($order->shipping_cost) }}</td></tr>
    @if((int) $order->other_charges)<tr><td>Biaya Lain</td><td class="right">{{ $money($order->other_charges) }}</td></tr>@endif
    <tr><td>Pajak</td><td class="right">{{ $money($order->tax_total) }}</td></tr>
    <tr class="grand"><td>Total</td><td class="right">{{ $money($order->total_amount) }}</td></tr>
</table>

@if($order->notes)
    <p><strong>Catatan:</strong><br>{!! nl2br(e($order->notes)) !!}</p>
@endif

<button class="no-print" onclick="window.print()">Print</button>

</body>
</html>
