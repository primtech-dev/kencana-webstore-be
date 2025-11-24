@extends('landing.layout')

@section('title', 'Produk & Layanan - Cash Aja MTF')

@section('jsonld')
    <script type="application/ld+json">
        {
          "@@context": "https://schema.org",
          "@@graph": [
            {
              "@@type": "BreadcrumbList",
              "itemListElement": [
                {
                  "@@type": "ListItem",
                  "position": 1,
                  "name": "Beranda",
                  "item": "{{ url('/') }}"
                },
                {
                  "@@type": "ListItem",
                  "position": 2,
                  "name": "Produk & Layanan",
                  "item": "{{ url()->current() }}"
                }
              ]
            },

            {
              "@@type": "CollectionPage",
              "url": "{{ url()->current() }}",
              "name": "Produk & Layanan Mandiri Tunas Finance",
              "description": "Lihat berbagai produk pembiayaan kendaraan dan layanan dari Mandiri Tunas Finance.",
              "hasPart": [
                        @foreach($products as $product)
                            {
                              "@@type": "Product",
                              "name": {!! json_encode($product->name) !!},
                              "image": "{{ asset('storage/' . $product->image_path) }}",
                              "description": {!! json_encode(Str::limit(strip_tags($product->description), 160)) !!},
                              "url": "{{ route('landing.product-show', $product->slug) }}",

                              "offers": {
                                "@@type": "Offer",
                                "availability": "https://schema.org/InStock",
                                "price": "0",
                                "priceCurrency": "IDR",
                                "url": "{{ route('landing.product-show', $product->slug) }}"
                              },

                              "aggregateRating": {
                                "@@type": "AggregateRating",
                                "ratingValue": "5",
                                "ratingCount": "120"
                              }
                            }
                            @if(!$loop->last),@endif
                        @endforeach
                        ]
                      }
                    ]
                  }
    </script>
@endsection


@section('meta')
    <meta name="description" content="Mandiri Tunas Finance (MTF) menyediakan berbagai produk pembiayaan kendaraan dan solusi keuangan terpercaya di Indonesia.">

    {{-- Open Graph --}}
    <meta property="og:type" content="website">
    <meta property="og:title" content="Produk & Layanan - Cash Aja MTF">
    <meta property="og:description" content="Lihat berbagai produk pembiayaan kendaraan dan layanan lainnya dari Mandiri Tunas Finance (MTF).">
    <meta property="og:image" content="{{ asset('images/logo-sm.png') }}">
    <meta property="og:url" content="{{ url()->current() }}">
@endsection

@section('content')
    {{-- Products Hero --}}
    <section class="products-hero">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8 text-center">
                    <h1 class="products-hero-title" data-aos="fade-up">Produk & Layanan</h1>
                    <p class="products-hero-subtitle" data-aos="fade-up" data-aos-delay="100">
                        Beragam solusi pembiayaan yang dirancang khusus untuk memenuhi kebutuhan Anda
                    </p>
                </div>
            </div>
        </div>
    </section>

    {{-- Products List --}}
    <section class="products-section">
        <div class="container">
            @if($products->count() > 0)
                <div class="products-grid">
                    @foreach($products as $index => $product)
                        <div class="product-card" data-aos="fade-up" data-aos-delay="{{ $index * 100 }}">
                            <div class="product-image">
                                <img src="{{ asset('storage/' . $product->image_path) }}" alt="{{ $product->alt_text }}">
                            </div>
                            <div class="product-content">
                                <h3 class="product-title">{{ $product->name }}</h3>
                                <div class="product-footer">
                                    <a href="{{ route('landing.product-show', $product->slug) }}" class="btn btn-outline-primary">
                                        Selengkapnya
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="ms-1" viewBox="0 0 16 16">
                                            <path fill-rule="evenodd" d="M1 8a.5.5 0 0 1 .5-.5h11.793l-3.147-3.146a.5.5 0 0 1 .708-.708l4 4a.5.5 0 0 1 0 .708l-4 4a.5.5 0 0 1-.708-.708L13.293 8.5H1.5A.5.5 0 0 1 1 8z"/>
                                        </svg>
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                {{-- No Products --}}
                <div class="no-products text-center py-5">
                    <svg xmlns="http://www.w3.org/2000/svg" width="80" height="80" fill="currentColor" class="text-muted mb-3" viewBox="0 0 16 16">
                        <path d="M1 2.5A1.5 1.5 0 0 1 2.5 1h3A1.5 1.5 0 0 1 7 2.5v3A1.5 1.5 0 0 1 5.5 7h-3A1.5 1.5 0 0 1 1 5.5v-3zM2.5 2a.5.5 0 0 0-.5.5v3a.5.5 0 0 0 .5.5h3a.5.5 0 0 0 .5-.5v-3a.5.5 0 0 0-.5-.5h-3zm6.5.5A1.5 1.5 0 0 1 10.5 1h3A1.5 1.5 0 0 1 15 2.5v3A1.5 1.5 0 0 1 13.5 7h-3A1.5 1.5 0 0 1 9 5.5v-3zm1.5-.5a.5.5 0 0 0-.5.5v3a.5.5 0 0 0 .5.5h3a.5.5 0 0 0 .5-.5v-3a.5.5 0 0 0-.5-.5h-3zM1 10.5A1.5 1.5 0 0 1 2.5 9h3A1.5 1.5 0 0 1 7 10.5v3A1.5 1.5 0 0 1 5.5 15h-3A1.5 1.5 0 0 1 1 13.5v-3zm1.5-.5a.5.5 0 0 0-.5.5v3a.5.5 0 0 0 .5.5h3a.5.5 0 0 0 .5-.5v-3a.5.5 0 0 0-.5-.5h-3zm6.5.5A1.5 1.5 0 0 1 10.5 9h3a1.5 1.5 0 0 1 1.5 1.5v3a1.5 1.5 0 0 1-1.5 1.5h-3A1.5 1.5 0 0 1 9 13.5v-3zm1.5-.5a.5.5 0 0 0-.5.5v3a.5.5 0 0 0 .5.5h3a.5.5 0 0 0 .5-.5v-3a.5.5 0 0 0-.5-.5h-3z"/>
                    </svg>
                    <h3>Belum Ada Produk</h3>
                    <p class="text-muted">
                        Produk dan layanan akan segera hadir.
                    </p>
                </div>
            @endif
        </div>
    </section>

    {{-- CTA Section --}}
    <section class="products-cta-section">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8 text-center" data-aos="zoom-in">
                    <h2>Tertarik dengan Produk Kami?</h2>
                    <p>Hubungi kami sekarang untuk mendapatkan informasi lebih lanjut dan penawaran terbaik</p>
                    <div class="cta-buttons">
                        <a href="https://wa.me/6285784242462" target="_blank" class="btn btn-whatsapp btn-lg">
                            <i class="fab fa-whatsapp me-2"></i>
                            Chat WhatsApp
                        </a>
                        <a href="{{ route('landing.index') }}#pengajuan-online" class="btn btn-primary btn-lg">
                            Ajukan Sekarang
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        AOS.init({
            duration: 800,
            easing: 'ease-in-out',
            once: true,
            offset: 100
        });
    </script>
@endpush

@push('styles')
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
@endpush
