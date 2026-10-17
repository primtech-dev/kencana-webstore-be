@extends('layouts.vertical', ['title' => 'Detail User'])

@section('content')
    @include('layouts.shared.page-title', [
        'title' => 'Detail User',
        'subTitle' => $user->name,
        'breadcrumbs' => [
            ['name' => 'Pengaturan', 'url' => route('users.index')],
            ['name' => 'Users', 'url' => route('users.index')],
            ['name' => 'Detail']
        ]
    ])

    <div class="card">
        <div class="card-body">
            <dl class="row mb-0">
                <dt class="col-sm-3">Nama</dt><dd class="col-sm-9">{{ $user->name }}</dd>
                <dt class="col-sm-3">Email</dt><dd class="col-sm-9">{{ $user->email }}</dd>
                <dt class="col-sm-3">Telepon</dt><dd class="col-sm-9">{{ $user->phone ?: '-' }}</dd>
                <dt class="col-sm-3">Cabang</dt><dd class="col-sm-9">{{ $user->branch->name ?? 'Pusat (semua cabang)' }}</dd>
                <dt class="col-sm-3">Role</dt>
                <dd class="col-sm-9">
                    @forelse($user->roles as $r)
                        <span class="badge bg-info me-1">{{ $r->name }}</span>
                    @empty
                        -
                    @endforelse
                </dd>
                <dt class="col-sm-3">Status</dt>
                <dd class="col-sm-9"><span class="badge bg-{{ $user->is_active ? 'success' : 'secondary' }}">{{ $user->is_active ? 'Aktif' : 'Non-aktif' }}</span></dd>
            </dl>
        </div>
    </div>
@endsection
