@extends('layouts.vertical', ['title' => 'Stok ' . ($variant->sku ?? 'Varian')])

@section('styles')
    @vite(['node_modules/datatables.net-responsive-bs5/css/responsive.bootstrap5.min.css'])
@endsection

@php use App\Support\Qty; @endphp

@section('content')
    @include('layouts.shared.page-title', [
        'title' => $variant->product->name ?? $variant->variant_name,
        'subTitle' => 'SKU ' . $variant->sku . ' · ' . $variant->variant_name . ($variant->unit ? ' · satuan ' . $variant->unit->name : ''),
        'breadcrumbs' => [['name' => 'Persediaan', 'url' => route('inventory.index')], ['name' => 'Stok', 'url' => route('inventory.index')], ['name' => $variant->sku]]
    ])

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">Stok per Cabang</h5>
            <a href="{{ route('inventory.index') }}" class="btn btn-sm btn-outline-secondary">Kembali</a>
        </div>
        <div class="table-responsive">
            <table class="table table-sm align-middle mb-0">
                <thead class="table-light">
                <tr>
                    <th>Cabang</th>
                    <th class="text-end">Stok Fisik</th>
                    <th class="text-end">Reserved</th>
                    <th class="text-end">Tersedia</th>
                    <th class="text-center">Riwayat</th>
                </tr>
                </thead>
                <tbody>
                @foreach($perBranch as $r)
                    <tr>
                        <td>{{ $r['name'] }} <span class="text-muted small">({{ $r['code'] }})</span>@unless($r['active'])<span class="badge bg-secondary ms-1">nonaktif</span>@endunless</td>
                        <td class="text-end">{{ Qty::fmt($r['on_hand']) }}</td>
                        <td class="text-end">{{ Qty::fmt($r['reserved']) }}</td>
                        <td class="text-end fw-semibold {{ Qty::cmp($r['available'], 0) > 0 ? 'text-success' : 'text-danger' }}">{{ Qty::fmt($r['available']) }}</td>
                        <td class="text-center">
                            @if($showBranch)
                                <button type="button" class="btn btn-sm btn-outline-primary js-pick-branch" data-branch="{{ $r['id'] }}">Lihat</button>
                            @else
                                <span class="text-muted small">-</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
                @if($perBranch->count() > 1)
                    <tr class="table-light fw-semibold">
                        <td>Total semua cabang</td>
                        <td class="text-end">{{ Qty::fmt($total['on_hand']) }}</td>
                        <td class="text-end">{{ Qty::fmt($total['reserved']) }}</td>
                        <td class="text-end">{{ Qty::fmt($total['available']) }}</td>
                        <td></td>
                    </tr>
                @endif
                </tbody>
            </table>
        </div>
    </div>

    <div class="card">
        <div class="card-header"><h5 class="card-title mb-0">Riwayat Perubahan Stok</h5></div>
        <div class="card-body">
            @include('inventory._movements', ['variantId' => $variant->id])
        </div>
    </div>
@endsection

@section('scripts')
    @vite(['resources/js/pages/inventory/movements.js'])
@endsection
