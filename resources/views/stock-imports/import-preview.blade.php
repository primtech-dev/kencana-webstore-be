@extends('layouts.vertical', ['title' => 'Preview Import Update Stok'])

@section('content')
    @include('layouts.shared.page-title', [
        'title' => 'Preview Import Update Stok',
        'subTitle' => 'Periksa perubahan stok sebelum disimpan',
        'breadcrumbs' => [
            ['name' => 'Persediaan', 'url' => route('stock_receipts.index')],
            ['name' => 'Import Update Stok', 'url' => route('stock_imports.form')],
            ['name' => 'Preview']
        ]
    ])

    <div class="card">
        <div class="card-body">
            <p class="text-muted mb-3">File: <strong>{{ $fileName }}</strong></p>

            <div class="d-flex flex-wrap gap-2 mb-3">
                <span class="badge bg-secondary">Total baris: {{ $summary['total'] }}</span>
                <span class="badge bg-success">Akan diubah: {{ $summary['ok'] }}</span>
                <span class="badge bg-light text-dark border">Tanpa perubahan: {{ $summary['unchanged'] }}</span>
                <span class="badge bg-danger">Error: {{ $summary['error'] }}</span>
            </div>

            @if(!empty($fatalErrors))
                <div class="alert alert-danger">
                    <strong>Import diblokir:</strong>
                    <ul class="mb-0">
                        @foreach($fatalErrors as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if(!empty($rows))
                <div class="table-responsive">
                    <table class="table table-bordered table-sm align-middle">
                        <thead class="table-light">
                        <tr>
                            <th>Baris</th>
                            <th>Cabang</th>
                            <th>SKU</th>
                            <th>Produk</th>
                            <th class="text-center">Mode</th>
                            <th class="text-end">Input</th>
                            <th class="text-end">Stok Sekarang</th>
                            <th class="text-end">Stok Baru</th>
                            <th class="text-end">Selisih</th>
                            <th>Status</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($rows as $r)
                            <tr class="{{ $r['status'] === 'ERROR' ? 'table-danger' : '' }}">
                                <td>{{ $r['row'] }}</td>
                                <td>{{ $r['branch_code'] }}</td>
                                <td>{{ $r['sku'] }}</td>
                                <td>{{ $r['product'] }} <span class="text-muted">{{ $r['unit'] }}</span></td>
                                <td class="text-center">{{ $r['mode'] }}</td>
                                <td class="text-end">{{ $r['input'] !== null ? \App\Support\Qty::fmt($r['input']) : '-' }}</td>
                                <td class="text-end">{{ $r['target'] !== null ? \App\Support\Qty::fmt($r['current']) : '-' }}</td>
                                <td class="text-end">{{ $r['target'] !== null ? \App\Support\Qty::fmt($r['target']) : '-' }}</td>
                                <td class="text-end">
                                    @if($r['delta'] !== null)
                                        <span class="{{ \App\Support\Qty::cmp($r['delta'], 0) > 0 ? 'text-success' : (\App\Support\Qty::cmp($r['delta'], 0) < 0 ? 'text-danger' : 'text-muted') }}">
                                            {{ \App\Support\Qty::cmp($r['delta'], 0) > 0 ? '+' : '' }}{{ \App\Support\Qty::fmt($r['delta']) }}
                                        </span>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>
                                    @if($r['status'] === 'ERROR')
                                        <span class="badge bg-danger">ERROR</span>
                                        <div class="small text-danger">{{ implode('; ', $r['messages']) }}</div>
                                    @elseif($r['status'] === 'UNCHANGED')
                                        <span class="badge bg-light text-dark border">Tanpa perubahan</span>
                                    @else
                                        <span class="badge bg-success">OK</span>
                                    @endif
                                </td>
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
