@extends('landing.layout')

@section('title', $article->meta_title ?? $article->title . ' - Fasilitas Dana MTF')

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
                  "name": "Berita",
                  "item": "{{ route('news.index') }}"
                },
                {
                  "@@type": "ListItem",
                  "position": 3",
                  "name": {!! json_encode($article->title) !!},
                  "item": "{{ url()->current() }}"
                }
              ]
            },

            {
              "@@type": "NewsArticle",
              "headline": {!! json_encode($article->title) !!},
              "url": "{{ url()->current() }}",
              "image": "{{ asset('storage/' . $article->image_path) }}",
              "description": {!! json_encode($article->meta_description ?? strip_tags(Str::limit($article->content, 160))) !!},
              "datePublished": "{{ $article->created_at->toIso8601String() }}",
              "dateModified": "{{ $article->updated_at->toIso8601String() }}",

              "author": {
                "@@type": "Organization",
                "name": "Mandiri Tunas Finance - Cabang Jember",
                "url": "{{ url('/') }}",
                "image": "{{ asset('images/logo-sm.png') }}",
                "telephone": "+62 8578 4242 462",
                "description": "Mandiri Tunas Finance (MTF) adalah perusahaan pembiayaan terkemuka di Indonesia yang menyediakan solusi keuangan terbaik.",
                "address": {
                  "@@type": "PostalAddress",
                  "streetAddress": "Ruko Gajah Mada Square, Jl. Gajah Mada No.187, Kaliwates Kidul, Kaliwates",
                  "addressLocality": "Kabupaten Jember",
                  "addressRegion": "Jawa Timur",
                  "postalCode": "68133",
                  "addressCountry": "ID"
                }
              },

              "publisher": {
                "@@type": "Organization",
                "name": "Fasilitas Dana MTF",
                "logo": {
                  "@@type": "ImageObject",
                  "url": "{{ asset('images/logo-sm.png') }}"
                }
              }
            }
          ]
        }
    </script>
@endsection

@section('meta')
    <meta name="description" content="{{ $article->meta_description ?? strip_tags($article->content) }}">
    <meta name="keywords" content="{{ $article->meta_keywords ?? '' }}">

    {{-- Open Graph / Facebook --}}
    <meta property="og:type" content="article">
    <meta property="og:title" content="{{ $article->title }}">
    <meta property="og:description" content="{{ $article->meta_description ?? strip_tags($article->content) }}">
    <meta property="og:image" content="{{ asset('storage/' . $article->image_path) }}">
    <meta property="og:url" content="{{ url()->current() }}">

    {{-- Twitter --}}
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $article->title }}">
    <meta name="twitter:description" content="{{ $article->meta_description ?? strip_tags($article->content) }}">
    <meta name="twitter:image" content="{{ asset('storage/' . $article->image_path) }}">
@endsection

@section('content')
    {{-- Article Header --}}
    <section class="article-header">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-10">
                    {{-- Breadcrumb --}}
                    <nav aria-label="breadcrumb" class="mb-4">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item">
                                <a href="{{ route('landing.index') }}">Beranda</a>
                            </li>
                            <li class="breadcrumb-item">
                                <a href="{{ route('news.index') }}">Berita</a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">
                                {{ \Illuminate\Support\Str::limit($article->title, 50) }}
                            </li>
                        </ol>
                    </nav>

                    {{-- Article Title --}}
                    <h1 class="article-title">{{ $article->title }}</h1>

                    {{-- Article Meta --}}
                    <div class="article-meta">
                        <div class="meta-item">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                                <path d="M8 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6Zm2-3a2 2 0 1 1-4 0 2 2 0 0 1 4 0Zm4 8c0 1-1 1-1 1H3s-1 0-1-1 1-4 6-4 6 3 6 4Zm-1-.004c-.001-.246-.154-.986-.832-1.664C11.516 10.68 10.289 10 8 10c-2.29 0-3.516.68-4.168 1.332-.678.678-.83 1.418-.832 1.664h10Z"/>
                            </svg>
                            <span>{{ $article->author->name }}</span>
                        </div>
                        <div class="meta-item">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                                <path d="M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5zM1 4v10a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V4H1z"/>
                            </svg>
                            <span>{{ \Carbon\Carbon::parse($article->created_at)->translatedFormat('d F Y') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Article Content --}}
    <section class="article-content-section">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-10">
                    {{-- Featured Image --}}
                    <div class="article-featured-image mb-4">
                        <img src="{{ asset('storage/' . $article->image_path) }}"
                             alt="{{ $article->image_alt_text }}"
                             class="img-fluid rounded"
                             onerror="this.src='https://via.placeholder.com/1200x600/0047AB/FFFFFF?text=News+Image'">
                    </div>

                    {{-- Article Body --}}
                    <div class="article-body">
                        {!! $article->content !!}
                    </div>

                    {{-- Article Footer --}}
                    <div class="article-footer">
                        <div class="row align-items-center">
                            <div class="col-md-6">
                                <div class="article-tags">
                                    @foreach($article->tags as $tag)
                                        <a href="{{ route('news.show-tag', $tag->name) }}"><span class="tag-badge">{{ $tag->name }}</span></a>
                                    @endforeach
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="article-share text-md-end">
                                    <span class="share-label">Bagikan:</span>
                                    <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(url()->current()) }}"
                                       target="_blank"
                                       class="share-btn facebook"
                                       aria-label="Share on Facebook">
                                        <i class="fab fa-facebook-f"></i>
                                    </a>
                                    <a href="https://twitter.com/intent/tweet?url={{ urlencode(url()->current()) }}&text={{ urlencode($article->title) }}"
                                       target="_blank"
                                       class="share-btn twitter"
                                       aria-label="Share on Twitter">
                                        <i class="fab fa-twitter"></i>
                                    </a>
                                    <a href="https://www.linkedin.com/shareArticle?mini=true&url={{ urlencode(url()->current()) }}&title={{ urlencode($article->title) }}"
                                       target="_blank"
                                       class="share-btn linkedin"
                                       aria-label="Share on LinkedIn">
                                        <i class="fab fa-linkedin-in"></i>
                                    </a>
                                    <a href="https://wa.me/?text={{ urlencode($article->title . ' - ' . url()->current()) }}"
                                       target="_blank"
                                       class="share-btn whatsapp"
                                       aria-label="Share on WhatsApp">
                                        <i class="fab fa-whatsapp"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Related News --}}
    @if($relatedNews->count() > 0)
        <section class="related-news-section">
            <div class="container">
                <h2 class="section-title">Berita Terkait</h2>
                <p class="section-subtitle">Baca juga berita lainnya</p>

                <div class="row">
                    @foreach($relatedNews as $item)
                        <div class="col-md-4 mb-4">
                            <x-frontend.news-card :item="$item" size="md" />
                        </div>
                    @endforeach
                </div>

                <div class="text-center mt-4">
                    <a href="{{ route('news.index') }}" class="btn btn-outline-primary btn-lg">
                        Lihat Semua Berita
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="ms-2" viewBox="0 0 16 16">
                            <path fill-rule="evenodd" d="M1 8a.5.5 0 0 1 .5-.5h11.793l-3.147-3.146a.5.5 0 0 1 .708-.708l4 4a.5.5 0 0 1 0 .708l-4 4a.5.5 0 0 1-.708-.708L13.293 8.5H1.5A.5.5 0 0 1 1 8z"/>
                        </svg>
                    </a>
                </div>
            </div>
        </section>
    @endif
@endsection
