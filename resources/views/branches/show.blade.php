@extends('layouts.vertical', ['title' => 'Detail Cabang'])

@section('content')
    @include('layouts.shared.page-title', [
        'title' => 'Detail Cabang',
        'subTitle' => $branch->name,
        'breadcrumbs' => [
            ['name' => 'Pengaturan', 'url' => route('branches.index')],
            ['name' => 'Cabang', 'url' => route('branches.index')],
            ['name' => 'Detail']
        ]
    ])

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Informasi Cabang</h5>
                    @can('branches.update')
                        <a href="{{ route('branches.edit', $branch->id) }}" class="btn btn-sm btn-primary">Edit</a>
                    @endcan
                </div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-4">Kode</dt><dd class="col-sm-8">{{ $branch->code }}</dd>
                        <dt class="col-sm-4">Nama</dt><dd class="col-sm-8">{{ $branch->name }}</dd>
                        <dt class="col-sm-4">Status</dt>
                        <dd class="col-sm-8">
                            <span class="badge bg-{{ $branch->is_active ? 'success' : 'secondary' }}">{{ $branch->is_active ? 'Aktif' : 'Non-aktif' }}</span>
                        </dd>
                        <dt class="col-sm-4">Alamat</dt><dd class="col-sm-8">{{ $branch->address ?: '-' }}</dd>
                        <dt class="col-sm-4">Kota / Provinsi</dt><dd class="col-sm-8">{{ $branch->city ?: '-' }} / {{ $branch->province ?: '-' }}</dd>
                        <dt class="col-sm-4">Telepon</dt><dd class="col-sm-8">{{ $branch->phone ?: '-' }}</dd>
                        <dt class="col-sm-4">Koordinat</dt>
                        <dd class="col-sm-8">{{ $branch->latitude !== null ? $branch->latitude.', '.$branch->longitude : '-' }}</dd>
                        <dt class="col-sm-4">Manager</dt>
                        <dd class="col-sm-8">{{ $branch->manager?->name ?? '-' }}</dd>
                    </dl>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card mb-3">
                <div class="card-header"><h5 class="card-title mb-0">Ringkasan</h5></div>
                <ul class="list-group list-group-flush">
                    <li class="list-group-item d-flex justify-content-between"><span>User</span><strong>{{ $branch->users_count }}</strong></li>
                    <li class="list-group-item d-flex justify-content-between"><span>Order</span><strong>{{ $branch->orders_count }}</strong></li>
                    <li class="list-group-item d-flex justify-content-between"><span>Penerimaan Stok</span><strong>{{ $branch->stock_receipts_count }}</strong></li>
                </ul>
            </div>

            <div class="card">
                <div class="card-header"><h5 class="card-title mb-0">User Cabang</h5></div>
                <ul class="list-group list-group-flush">
                    @forelse($users as $u)
                        <li class="list-group-item d-flex justify-content-between">
                            <span>{{ $u->name }}<br><small class="text-muted">{{ $u->email }}</small></span>
                            <span class="badge bg-{{ $u->is_active ? 'success' : 'secondary' }} align-self-center">{{ $u->is_active ? 'Aktif' : 'Non-aktif' }}</span>
                        </li>
                    @empty
                        <li class="list-group-item text-muted">Belum ada user.</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
@endsection
