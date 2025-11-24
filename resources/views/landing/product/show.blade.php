@extends('landing.layout')

@section('title', $product->name . ' - Cash Aja MTF')

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
                  "item": "{{ route('landing.index') }}"
                },
                {
                  "@@type": "ListItem",
                  "position": 2,
                  "name": "Produk & Layanan",
                  "item": "{{ route('landing.product') }}"
                },
                {
                  "@@type": "ListItem",
                  "position": 3,
                  "name": {!! json_encode($product->name) !!},
                  "item": "{{ url()->current() }}"
                }
              ]
            },

            {
              "@@type": "Product",
              "name": {!! json_encode($product->name) !!},
              "image": "{{ asset('storage/' . $product->image_path) }}",
              "description": {!! json_encode(strip_tags($product->content)) !!},
              "sku": "MTF-{{ $product->slug }}",
              "brand": {
                "@@type": "Brand",
                "name": "Mandiri Tunas Finance"
              },

              "offers": {
                "@@type": "Offer",
                "url": "{{ url()->current() }}",
                "priceCurrency": "IDR",
                "price": "0",
                "availability": "https://schema.org/InStock",
                "itemCondition": "https://schema.org/NewCondition"
              },

              "aggregateRating": {
                "@@type": "AggregateRating",
                "ratingValue": "5",
                "ratingCount": "120"
              },

              "publisher": {
                "@@type": "Organization",
                "name": "Cash Aja MTF",
                "logo": {
                  "@type": "ImageObject",
                  "url": "{{ asset('images/logo-sm.png') }}"
                }
              }
            }

          ]
        }
    </script>
@endsection


@section('meta')
    <meta name="description" content="{{ strip_tags($product->content) }}">

    {{-- Open Graph --}}
    <meta property="og:type" content="product">
    <meta property="og:title" content="{{ $product->name }}">
    <meta property="og:description" content="{{ strip_tags($product->content) }}">
    <meta property="og:image" content="{{ asset('storage/' . $product->image_path) }}">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="product:brand" content="Mandiri Tunas Finance">
    <meta property="product:availability" content="in stock">
    <meta property="product:condition" content="new">

    {{-- Twitter --}}
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $product->name }}">
    <meta name="twitter:description" content="{{ strip_tags($product->content) }}">
    <meta name="twitter:image" content="{{ asset('storage/' . $product->image_path) }}">
@endsection

@section('content')
    {{-- Product Header --}}
    <section class="product-header">
        <div class="container">
            <div class="row">
                <div class="col-lg-10 mx-auto">
                    {{-- Breadcrumb --}}
                    <nav aria-label="breadcrumb" class="mb-4">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item">
                                <a href="{{ route('landing.index') }}">Beranda</a>
                            </li>
                            <li class="breadcrumb-item">
                                <a href="{{ route('landing.product') }}">Produk & Layanan</a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">
                                {{ \Illuminate\Support\Str::limit($product->name, 40) }}
                            </li>
                        </ol>
                    </nav>

                    {{-- Product Title --}}
                    <h1 class="product-detail-title" data-aos="fade-up">{{ $product->name }}</h1>
                </div>
            </div>
        </div>
    </section>

    {{-- Product Content --}}
    <section class="product-content-section">
        <div class="container">
            <div class="row">
                <div class="col-lg-10 mx-auto">
                    {{-- Product Info Grid (Image + Description) --}}
                    <div class="product-info-grid mb-5">
                        {{-- Featured Image --}}
                        <div class="product-featured-image" data-aos="fade-right">
                            <img src="{{ asset('storage/' . $product->image_path) }}"
                                 alt="{{ $product->alt_text }}"
                                 onerror="this.src='https://via.placeholder.com/600x800/0047AB/FFFFFF?text={{ urlencode($product->name) }}'">
                        </div>

                        {{-- Product Description --}}
                        <div class="product-description" data-aos="fade-left">
                            <h2 class="section-subtitle mb-4">Deskripsi Produk</h2>
                            <div class="description-content">
                                {!! $product->content !!}
                            </div>
                        </div>
                    </div>

                    {{-- Terms and Conditions --}}
                    @if($product->terms_and_condition)
                        <div class="product-terms mt-5" data-aos="fade-up">
                            <h2 class="section-subtitle mb-4">Syarat & Ketentuan</h2>
                            <div class="terms-content">
                                {!! $product->terms_and_condition !!}
                            </div>
                        </div>
                    @endif

                    {{-- CTA Box --}}
                    <div class="product-cta-box mt-5" data-aos="fade-up">
                        <div class="row align-items-center">
                            <div class="col-md-7 mb-3 mb-md-0">
                                <h3>Tertarik dengan Produk Ini?</h3>
                                <p class="mb-0">Hubungi kami sekarang untuk mendapatkan informasi lebih lanjut dan penawaran terbaik</p>
                            </div>
                            <div class="col-md-5 text-md-end">
                                <a href="https://wa.me/6285784242462?text={{ urlencode('Halo, saya tertarik dengan produk ' . $product->name) }}"
                                   target="_blank"
                                   class="btn btn-whatsapp btn-lg mb-2">
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
            </div>
        </div>
    </section>

    {{-- Other Products --}}
    @if($otherProducts->count() > 0)
        <section class="other-products-section">
            <div class="container">
                <h2 class="section-title" data-aos="fade-up">Produk Lainnya</h2>
                <p class="section-subtitle" data-aos="fade-up" data-aos-delay="100">
                    Lihat juga produk dan layanan lainnya yang tersedia
                </p>

                <div class="row">
                    @foreach($otherProducts as $index => $item)
                        <div class="col-md-6 mb-4" data-aos="fade-up" data-aos-delay="{{ ($index + 1) * 100 }}">
                            <div class="product-card">
                                <div class="product-image">
                                    <img src="{{ asset('storage/' . $item->image_path) }}" alt="{{ $item->alt_text }}">
                                    <div class="product-overlay">
                                        <a href="{{ route('landing.product-show', $item->slug) }}" class="btn btn-light btn-lg">
                                            Lihat Detail
                                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" class="ms-2" viewBox="0 0 16 16">
                                                <path fill-rule="evenodd" d="M1 8a.5.5 0 0 1 .5-.5h11.793l-3.147-3.146a.5.5 0 0 1 .708-.708l4 4a.5.5 0 0 1 0 .708l-4 4a.5.5 0 0 1-.708-.708L13.293 8.5H1.5A.5.5 0 0 1 1 8z"/>
                                            </svg>
                                        </a>
                                    </div>
                                </div>
                                <div class="product-content">
                                    <h3 class="product-title">{{ $item->name }}</h3>
                                    <div class="product-footer">
                                        <a href="{{ route('landing.product-show', $item->slug) }}" class="btn btn-outline-primary">
                                            Selengkapnya
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="ms-1" viewBox="0 0 16 16">
                                                <path fill-rule="evenodd" d="M1 8a.5.5 0 0 1 .5-.5h11.793l-3.147-3.146a.5.5 0 0 1 .708-.708l4 4a.5.5 0 0 1 0 .708l-4 4a.5.5 0 0 1-.708-.708L13.293 8.5H1.5A.5.5 0 0 1 1 8z"/>
                                            </svg>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="text-center mt-4" data-aos="fade-up">
                    <a href="{{ route('landing.product') }}" class="btn btn-primary btn-lg">
                        Lihat Semua Produk
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="ms-2" viewBox="0 0 16 16">
                            <path fill-rule="evenodd" d="M1 8a.5.5 0 0 1 .5-.5h11.793l-3.147-3.146a.5.5 0 0 1 .708-.708l4 4a.5.5 0 0 1 0 .708l-4 4a.5.5 0 0 1-.708-.708L13.293 8.5H1.5A.5.5 0 0 1 1 8z"/>
                        </svg>
                    </a>
                </div>
            </div>
        </section>
    @endif
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
