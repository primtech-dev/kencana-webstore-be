@extends('layouts.vertical', ['title' => 'Preview Import Stok Odoo'])

@php
    use App\Support\Qty;
    $changes = array_values(array_filter($rows, fn ($r) => $r['status'] === 'OK'));
    $shown = array_slice($changes, 0, $previewLimit);
@endphp

@section('content')
    @include('layouts.shared.page-title', [
        'title' => 'Preview Import Stok Odoo',
        'subTitle' => 'Periksa perubahan stok sebelum disimpan',
        'breadcrumbs' => [
            ['name' => 'Persediaan', 'url' => route('stock_receipts.index')],
            ['name' => 'Import Update Stok', 'url' => route('stock_imports.form')],
            ['name' => 'Preview']
        ]
    ])

    <div class="card">
        <div class="card-body">
            <p class="text-muted mb-3">File: <strong>{{ $fileName }}</strong> &middot;
                {{ $zeroMissing ? 'Produk yang tidak ada di file akan dinolkan' : 'Produk yang tidak ada di file dibiarkan' }}</p>

            <div class="d-flex flex-wrap gap-2 mb-3">
                <span class="badge bg-secondary">Baris di file: {{ number_format($summary['total'], 0, ',', '.') }}</span>
                <span class="badge bg-success">Stok diperbarui: {{ number_format($summary['ok'], 0, ',', '.') }}</span>
                <span class="badge bg-warning text-dark">Dinolkan: {{ number_format($summary['zeroed'], 0, ',', '.') }}</span>
                <span class="badge bg-light text-dark border">Tanpa perubahan: {{ number_format($summary['unchanged'], 0, ',', '.') }}</span>
                <span class="badge bg-light text-dark border">Lokasi non-Stock diabaikan: {{ number_format($summary['non_stock'], 0, ',', '.') }}</span>
                <span class="badge bg-light text-dark border">Dilewati (tidak cocok): {{ number_format($summary['skipped'], 0, ',', '.') }}</span>
                @if($summary['duplicates'])
                    <span class="badge bg-light text-dark border">Digabung (produk sama): {{ number_format($summary['duplicates'], 0, ',', '.') }}</span>
                @endif
            </div>

            @if(!empty($fatalErrors))
                <div class="alert alert-danger">
                    <strong>Import diblokir:</strong>
                    <ul class="mb-0">
                        @foreach($fatalErrors as $err)<li>{{ $err }}</li>@endforeach
                    </ul>
                </div>
            @endif

            @if(!empty($branches))
                <h6>Ringkasan per cabang</h6>
                <div class="table-responsive mb-3">
                    <table class="table table-sm table-bordered align-middle">
                        <thead class="table-light">
                        <tr><th>Cabang</th><th class="text-end">Produk cocok</th><th class="text-end">Diperbarui</th><th class="text-end">Dinolkan</th><th class="text-end">Tanpa perubahan</th></tr>
                        </thead>
                        <tbody>
                        @foreach($branches as $b)
                            <tr>
                                <td>{{ $b['name'] }} <span class="text-muted">({{ $b['code'] }})</span></td>
                                <td class="text-end">{{ number_format($b['matched'], 0, ',', '.') }}</td>
                                <td class="text-end">{{ number_format($b['ok'], 0, ',', '.') }}</td>
                                <td class="text-end">{{ number_format($b['zeroed'], 0, ',', '.') }}</td>
                                <td class="text-end">{{ number_format($b['unchanged'], 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            @endif

            @foreach($warnings as $w)
                <details class="mb-2">
                    <summary class="text-{{ $w['level'] === 'danger' ? 'danger' : 'warning-emphasis' }}">
                        <strong>{{ $w['title'] }}:</strong> {{ number_format($w['count'], 0, ',', '.') }}
                    </summary>
                    <ul class="small mb-1 mt-1">
                        @foreach($w['items'] as $item)<li>{{ $item }}</li>@endforeach
                        @if($w['count'] > count($w['items']))
                            <li class="text-muted">&hellip; dan {{ number_format($w['count'] - count($w['items']), 0, ',', '.') }} lainnya</li>
                        @endif
                    </ul>
                </details>
            @endforeach

            @if($changes)
                <h6 class="mt-3">Perubahan stok
                    @if(count($changes) > $previewLimit)
                        <span class="text-muted small">(menampilkan {{ number_format($previewLimit, 0, ',', '.') }} dari {{ number_format(count($changes), 0, ',', '.') }}; seluruhnya tetap diproses)</span>
                    @endif
                </h6>
                <div class="table-responsive">
                    <table class="table table-bordered table-sm align-middle">
                        <thead class="table-light">
                        <tr>
                            <th>Cabang</th><th>SKU</th><th>Produk</th>
                            <th class="text-end">Stok Sekarang</th><th class="text-end">Stok Baru</th><th class="text-end">Selisih</th><th>Catatan</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($shown as $r)
                            <tr class="{{ $r['missing'] ? 'table-warning' : '' }}">
                                <td>{{ $r['branch_code'] }}</td>
                                <td>{{ $r['sku'] }}</td>
                                <td>{{ $r['product'] }} <span class="text-muted">{{ $r['unit'] }}</span></td>
                                <td class="text-end">{{ Qty::fmt($r['current']) }}</td>
                                <td class="text-end">{{ Qty::fmt($r['target']) }}</td>
                                <td class="text-end">
                                    <span class="{{ Qty::cmp($r['delta'], 0) > 0 ? 'text-success' : 'text-danger' }}">
                                        {{ Qty::cmp($r['delta'], 0) > 0 ? '+' : '' }}{{ Qty::fmt($r['delta']) }}
                                    </span>
                                </td>
                                <td class="small text-muted">{{ implode('; ', $r['messages']) }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            @endif

            <div class="d-flex gap-2 mt-3">
                <form method="POST" action="{{ route('stock_imports.process') }}">
                    @csrf
                    <input type="hidden" name="token" value="{{ $token }}">
                    <button class="btn btn-primary" @if(!empty($fatalErrors)) disabled @endif>Konfirmasi &amp; Simpan</button>
                </form>
                <a href="{{ route('stock_imports.form') }}" class="btn btn-outline-secondary">Unggah Ulang</a>
            </div>
        </div>
    </div>
@endsection
