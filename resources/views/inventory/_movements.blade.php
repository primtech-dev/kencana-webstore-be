{{-- Filter + tabel riwayat perubahan stok. Params: $showBranch, $branches, $reasonGroups, $variantId (opsional), $initialBranch (opsional) --}}
<div class="row g-2 mb-3" id="movementFilters">
    @if($showBranch)
        <div class="col-6 col-md-3">
            <label class="form-label small mb-1">Cabang</label>
            <select id="mv_branch" class="form-select form-select-sm">
                <option value="">Semua cabang</option>
                @foreach($branches as $b)
                    <option value="{{ $b->id }}" @selected(($initialBranch ?? 0) == $b->id)>{{ $b->name }}</option>
                @endforeach
            </select>
        </div>
    @endif
    <div class="col-6 col-md-3">
        <label class="form-label small mb-1">Jenis perubahan</label>
        <select id="mv_reason" class="form-select form-select-sm">
            <option value="">Semua</option>
            @foreach($reasonGroups as $key => $label)
                <option value="{{ $key }}">{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-6 col-md-2">
        <label class="form-label small mb-1">Dari tanggal</label>
        <input type="date" id="mv_date_from" class="form-control form-control-sm">
    </div>
    <div class="col-6 col-md-2">
        <label class="form-label small mb-1">Sampai tanggal</label>
        <input type="date" id="mv_date_to" class="form-control form-control-sm">
    </div>
    <div class="col-12 col-md-2 d-flex align-items-end">
        <button type="button" id="mv_reset" class="btn btn-sm btn-outline-secondary">Reset</button>
    </div>
</div>

<table class="table table-striped dt-responsive align-middle w-100" id="movements-table">
    <thead class="thead-sm text-uppercase fs-xxs">
    <tr>
        <th>Waktu</th>
        <th>Cabang</th>
        <th>Produk</th>
        <th class="text-end">Perubahan</th>
        <th class="text-end">Stok Setelah</th>
        <th>Jenis</th>
        <th>Referensi</th>
        <th>Oleh</th>
        <th>Keterangan</th>
    </tr>
    </thead>
    <tbody></tbody>
</table>

<script>
    window.inventoryRoutes = Object.assign(window.inventoryRoutes || {}, {
        movements: '{{ route('inventory.movements') }}',
        variantId: @json($variantId ?? null),
        showBranch: @json($showBranch),
        hideProductColumn: @json(! empty($variantId)),
    });
</script>
