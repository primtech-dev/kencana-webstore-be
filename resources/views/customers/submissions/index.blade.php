@extends('layouts.vertical', ['title' => 'Manajemen Pengajuan'])

@section('styles')
    @vite(['node_modules/datatables.net-responsive-bs5/css/responsive.bootstrap5.min.css'])
@endsection

@section('content')
    @include('layouts.shared.page-title', [
        'title' => 'Manajemen Pengajuan',
        'subTitle' => 'Kelola data pengajuan pelanggan dari website.',
        'breadcrumbs' => [
            ['name' => 'Pelanggan', 'url' => route('customers.index')],
            ['name' => 'Pengajuan']
        ]
    ])

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Daftar Pengajuan</h5>
                </div>
                <div class="card-body">
                    <table class="table table-striped dt-responsive align-middle w-100" id="submissions-table">
                        <thead class="thead-sm text-uppercase fs-xxs">
                        <tr>
                            <th width="5%">No</th>
                            <th width="15%">Nama</th>
                            <th width="12%">No. Telepon</th>
                            <th width="15%">Unit Mobil</th>
                            <th width="20%">Alamat</th>
                            <th width="18%">Pesan</th>
                            <th width="15%">Tanggal Pengajuan</th>
                        </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    @vite(['resources/js/pages/submissions/submission.js'])

    <script>
        window.submissionRoutes = {
            index: '{{ route('submissions.index') }}'
        };
    </script>
@endsection
