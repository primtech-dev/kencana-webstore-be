@extends('layouts.vertical', ['title' => 'Detail Produk'])

@section('styles')
    <style>
        .product-gallery { display:flex; gap:0.75rem; flex-wrap:wrap; }
        .product-gallery img { width:150px; height:150px; object-fit:cover; border-radius:6px; border:1px solid #e9ecef; }
        .badge-cat { background:#f1f5f9; color:#111827; border-radius:6px; padding:0.35rem 0.6rem; margin-right:6px; display:inline-block; }
        .meta { font-size:0.95rem; color:#6b7280; }
        .image-thumb { width:80px; height:80px; object-fit:cover; border-radius:6px; border:1px solid #e9ecef; }
    </style>
@endsection

@section('content')
    @include('layouts.shared.page-title', [
        'title' => 'Detail Produk',
        'subTitle' => 'Lihat detail lengkap produk',
        'breadcrumbs' => [
            ['name' => 'Produk', 'url' => route('products.index')],
            ['name' => $product->name]
        ]
    ])

    @php
        use Illuminate\Support\Facades\Storage;

        // safe-get main image
        $mainImage = null;
        if (isset($product->images) && $product->images->count()) {
            $mainImage = $product->images->firstWhere('is_main', true) ?? $product->images->first();
        }
        $mainUrl = null;
        if ($mainImage && !empty($mainImage->url) && Storage::disk('public')->exists($mainImage->url)) {
            $mainUrl = Storage::disk('public')->url($mainImage->url);
        }
    @endphp

    <div class="row">
        <div class="col-lg-8">
            <div class="card mb-3">
                <div class="card-body d-flex gap-3">
                    <div style="min-width:160px;">
                        <img src="{{ $mainUrl ?? asset('images/placeholder-300x300.png') }}"
                             alt="{{ $product->name }}"
                             style="width:260px;height:260px;object-fit:cover;border-radius:6px;">
                    </div>

                    <div class="flex-fill">
                        <h3 class="mb-1">{{ $product->name }}</h3>
                        <div class="meta mb-2">SKU: <strong>{{ $product->sku ?? '-' }}</strong> — Status: <strong>{{ $product->is_active ? 'Aktif' : 'Non-aktif' }}</strong></div>

                        <div class="mb-2">
                            <strong>Kategori:</strong>
                            @if($product->categories && $product->categories->count())
                                @foreach($product->categories as $c)
                                    <span class="badge-cat">{{ $c->name }}</span>
                                @endforeach
                            @else
                                <span class="meta">— Tidak dikategorikan —</span>
                            @endif
                        </div>

                        <div class="mb-3">
                            <strong>Deskripsi singkat</strong>
                            <p class="small text-muted">{{ $product->short_description ?? '-' }}</p>
                        </div>

                        <div class="mb-3">
                            <strong>Deskripsi lengkap</strong>
                            <div class="small text-muted">{!! nl2br(e($product->description ?? '-')) !!}</div>
                        </div>

                        <div class="d-flex gap-2">
                            @can('products.update')
                                <a href="{{ route('products.edit', $product->id) }}" class="btn btn-primary">
                                    <i data-lucide="edit" class="me-1"></i> Edit
                                </a>
                            @endcan

                            @can('products.delete')
                                <form action="{{ route('products.destroy', $product->id) }}" method="POST" onsubmit="return confirm('Hapus produk ini?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-outline-danger">Hapus</button>
                                </form>
                            @endcan
                        </div>

                    </div>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-header"><h5 class="card-title mb-0">Galeri Gambar</h5></div>
                <div class="card-body">
                    <div class="product-gallery" id="productGallery" data-product-id="{{ $product->id }}">
                        @forelse($product->images->sortBy('position') as $img)
                            @php
                                $url = null;
                                if (!empty($img->url) && Storage::disk('public')->exists($img->url)) {
                                    $url = Storage::disk('public')->url($img->url);
                                }
                            @endphp

                            <div class="gallery-item card p-2" data-id="{{ $img->id }}" style="width:160px;">
                                <div class="drag-handle" style="cursor:grab; position:absolute; right:6px; top:6px;">
                                    <i data-lucide="move" class="text-muted"></i>
                                </div>
                                @if($url)
                                    <div style="min-height:110px;" class="d-flex align-items-center justify-content-center">
                                        <img src="{{ $url }}" class="w-100" style="height:110px;object-fit:cover;border-radius:6px;">
                                    </div>
                                @else
                                    <div class="image-thumb d-flex align-items-center justify-content-center bg-light text-muted">Tidak ditemukan</div>
                                @endif

                                <div class="mt-2 d-flex justify-content-between align-items-center">
                                    <small class="text-muted">Pos: <span class="img-pos">{{ $img->position }}</span></small>

                                    @if($img->is_main)
                                        <span class="badge bg-success">Utama</span>
                                    @else
                                        <button type="button" class="btn btn-sm btn-outline-primary js-set-main" data-id="{{ $img->id }}">Set Utama</button>
                                    @endif
                                </div>

                                <div class="mt-2 d-flex gap-1">
                                    <button type="button" class="btn btn-sm btn-outline-danger js-delete-image" data-id="{{ $img->id }}"><i class="ti ti-trash"></i></button>
                                </div>
                            </div>
                        @empty
                            <div class="meta">Belum ada gambar.</div>
                        @endforelse
                    </div>

                    <!-- hint -->
                    <p class="small text-muted mt-2">Tarik dan lepaskan gambar untuk mengubah urutan. Klik "Set Utama" untuk menandai gambar utama.</p>
                </div>
            </div>

            <div class="card">
                <div class="card-header"><h5 class="card-title mb-0">Variants</h5></div>
                <div class="card-body">
                    @if($product->variants && $product->variants->count())
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Nama Varian</th>
                                    <th>SKU</th>
                                    <th>Harga</th>
                                    <th>Retail</th>
                                    <th>Cost</th>
                                    <th>Dimensi (L×W×H cm)</th>
                                    <th>Gambar</th>
                                    <th>Status</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($product->variants as $i => $v)
                                    @php
                                        // format harga ke format Indonesia (tanpa desimal)
                                        $fmt = function($cents) {
                                            if ($cents === null) return '-';
                                            $value = $cents/100; // assume cents -> unit
                                            return 'Rp ' . number_format($value, 0, ',', '.');
                                        };

                                        // variant images (first 3 thumbnails)
                                        $v_images = $v->images ?? collect();
                                    @endphp
                                    <tr>
                                        <td>{{ $i+1 }}</td>
                                        <td>{{ $v->variant_name }}</td>
                                        <td>{{ $v->sku }}</td>
                                        <td>{{ $fmt($v->price_cents) }}</td>
                                        <td>{{ $fmt($v->retail_price_cents) }}</td>
                                        <td>{{ $fmt($v->cost_cents) }}</td>
                                        <td>{{ $v->length ?? '-' }} × {{ $v->width ?? '-' }} × {{ $v->height ?? '-' }}</td>

                                        <td>
                                            @if($v_images && count($v_images))
                                                <div class="d-flex gap-1">
                                                    @foreach($v_images->take(3) as $vi)
                                                        @php
                                                            $vurl = null;
                                                            if (!empty($vi['url'] ?? $vi->url ?? null)) {
                                                                $path = is_array($vi) ? ($vi['url'] ?? null) : ($vi->url ?? null);
                                                                if ($path && Storage::disk('public')->exists($path)) $vurl = Storage::disk('public')->url($path);
                                                            }
                                                        @endphp
                                                        @if($vurl)
                                                            <img src="{{ $vurl }}" class="image-thumb" alt="varian image">
                                                        @else
                                                            <div class="image-thumb d-flex align-items-center justify-content-center bg-light text-muted">-</div>
                                                        @endif
                                                    @endforeach
                                                </div>
                                            @else
                                                <span class="meta">—</span>
                                            @endif
                                        </td>

                                        <td>{{ $v->is_active ? 'Aktif' : 'Non-aktif' }}</td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="meta">Belum ada varian untuk produk ini.</p>
                    @endif
                </div>
            </div>

        </div>

        <div class="col-lg-4">
            <div class="card mb-3">
                <div class="card-header"><h6 class="mb-0">Meta</h6></div>
                <div class="card-body">
                    <p class="mb-1"><strong>Dibuat</strong></p>
                    <p class="meta">{{ $product->created_at ? $product->created_at->format('d M Y H:i') : '-' }}</p>

                    <p class="mb-1"><strong>Terakhir diupdate</strong></p>
                    <p class="meta">{{ $product->updated_at ? $product->updated_at->format('d M Y H:i') : '-' }}</p>

                    <p class="mb-1"><strong>Berat</strong></p>
                    <p class="meta">{{ $product->weight_gram ? $product->weight_gram . ' gr' : '-' }}</p>

                    <p class="mb-1"><strong>Atribut (JSON)</strong></p>
                    <pre class="small text-muted" style="white-space:pre-wrap;">{!! json_encode($product->attributes ?? [], JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE) !!}</pre>
                </div>
            </div>

            <!-- quick actions -->
            <div class="card">
                <div class="card-body">
                    @can('products.update')
                        <a href="{{ route('products.edit', $product->id) }}" class="btn btn-block btn-outline-primary w-100 mb-2">Edit Produk</a>
                    @endcan
                    <a href="{{ route('products.index') }}" class="btn btn-block btn-outline-secondary w-100">Kembali ke daftar</a>
                </div>
            </div>
        </div>
    </div>

    @section('scripts')
        <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
        @vite(['resources/js/pages/products/images-order.js'])
    @endsection

@endsection
