@php
    $index = $index ?? 0;
    $v = $variant ?? (object)[];
    $rand = $v->id ?? 'new_'.$index;

    // Normalize images into array of image arrays (each must have url,id)
    $v_images = [];
    if (isset($v->images)) {
        // If it's Eloquent Collection
        if ($v->images instanceof \Illuminate\Support\Collection) {
            $v_images = $v->images->toArray();
        } elseif (is_array($v->images)) {
            $v_images = $v->images;
        } elseif (is_object($v->images)) {
            // single object -> cast to array
            $v_images = [(array) $v->images];
        }
    }
@endphp

<div class="variant-row" data-row-id="{{ $rand }}">
    <input type="hidden" name="variants[{{ $index }}][id]" value="{{ $v->id ?? '' }}" />
    <div class="row gy-2">
        <div class="col-md-4">
            <label class="form-label">Nama Varian</label>
            <input type="text" name="variants[{{ $index }}][variant_name]" class="form-control"
                   value="{{ old("variants.$index.variant_name", $v->variant_name ?? '') }}" />
        </div>

        <div class="col-md-2">
            <label class="form-label">SKU</label>
            <input type="text" name="variants[{{ $index }}][sku]" class="form-control"
                   value="{{ old("variants.$index.sku", $v->sku ?? '') }}" />
        </div>

        <div class="col-md-2">
            <label class="form-label">Harga (sen)</label>
            <input type="number" name="variants[{{ $index }}][price_cents]" class="form-control"
                   value="{{ old("variants.$index.price_cents", isset($v->price_cents) ? $v->price_cents : 0) }}" />
        </div>

        <div class="col-md-2">
            <label class="form-label">Retail (sen)</label>
            <input type="number" name="variants[{{ $index }}][retail_price_cents]" class="form-control"
                   value="{{ old("variants.$index.retail_price_cents", $v->retail_price_cents ?? '') }}" />
        </div>

        <div class="col-md-2">
            <label class="form-label">Cost (sen)</label>
            <input type="number" name="variants[{{ $index }}][cost_cents]" class="form-control"
                   value="{{ old("variants.$index.cost_cents", $v->cost_cents ?? '') }}" />
        </div>

        <div class="col-md-2">
            <label class="form-label">Panjang (cm)</label>
            <input type="text" name="variants[{{ $index }}][length]" class="form-control"
                   value="{{ old("variants.$index.length", $v->length ?? '') }}" />
        </div>

        <div class="col-md-2">
            <label class="form-label">Lebar (cm)</label>
            <input type="text" name="variants[{{ $index }}][width]" class="form-control"
                   value="{{ old("variants.$index.width", $v->width ?? '') }}" />
        </div>

        <div class="col-md-2">
            <label class="form-label">Tinggi (cm)</label>
            <input type="text" name="variants[{{ $index }}][height]" class="form-control"
                   value="{{ old("variants.$index.height", $v->height ?? '') }}" />
        </div>

        <div class="col-md-3">
            <label class="form-label">Aktif</label>
            <select name="variants[{{ $index }}][is_active]" class="form-select">
                <option value="1" {{ (old("variants.$index.is_active", $v->is_active ?? 1) ? 'selected':'') }}>Ya</option>
                <option value="0" {{ (!old("variants.$index.is_active", $v->is_active ?? 1) ? 'selected':'') }}>Tidak</option>
            </select>
        </div>

        <div class="col-md-3">
            <label class="form-label">Boleh Dijual</label>
            <select name="variants[{{ $index }}][is_sellable]" class="form-select">
                <option value="1" {{ (old("variants.$index.is_sellable", $v->is_sellable ?? 1) ? 'selected':'') }}>Ya</option>
                <option value="0" {{ (!old("variants.$index.is_sellable", $v->is_sellable ?? 1) ? 'selected':'') }}>Tidak</option>
            </select>
        </div>

        <div class="col-md-6">
            <label class="form-label">Gambar Varian (boleh multiple)</label>
            <input type="file" name="variants[{{ $index }}][images][]" accept="image/*" multiple class="form-control" />
            @if(!empty($v_images) && count($v_images))
                <div class="mt-2 d-flex gap-2 flex-wrap">
                    @foreach($v_images as $img)
                        @php
                            // each $img may be array or object - normalize url/id
                            $imgUrl = is_array($img) ? ($img['url'] ?? null) : ($img->url ?? null);
                            $imgId  = is_array($img) ? ($img['id'] ?? null) : ($img->id ?? null);
                        @endphp
                        @if($imgUrl)
                            <div class="position-relative">
                                <img src="{{ asset('storage/'.$imgUrl) }}" class="image-thumb" alt="">
                                @if($imgId)
                                    <button type="button" class="btn btn-sm btn-danger js-delete-image" data-id="{{ $imgId }}" style="position:absolute; right:5px; top:5px;"><i class="ti ti-trash"></i></button>
                                @endif
                            </div>
                        @endif
                    @endforeach
                </div>
            @endif
        </div>

        <div class="col-12 text-end">
            <button type="button" class="btn btn-sm btn-outline-danger btn-remove-variant">Hapus Varian</button>
        </div>
    </div>
    <hr/>
</div>
