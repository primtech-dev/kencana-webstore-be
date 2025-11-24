@extends('landing.layout')

@section('title', 'Tag: ' . ucfirst($tagName) . ' - Berita Fasilitas Dana MTF')

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
                  "position": 3,
                  "name": "Tag: {{ ucfirst($tagName) }}",
                  "item": "{{ url()->current() }}"
                }
              ]
            },

            {
              "@@type": "CollectionPage",
              "url": "{{ url()->current() }}",
              "name": "Tag: {{ ucfirst($tagName) }}",
              "description": "Kumpulan artikel dengan tag {{ ucfirst($tagName) }}."
            }
          ]
        }
    </script>
@endsection


@section('meta')
    <meta name="description" content="Kumpulan artikel untuk tag {{ ucfirst($tagName) }} dari Fasilitas Dana MTF. Menampilkan {{ $articles->total() }} artikel terkait pembiayaan, tips keuangan, dan informasi dari Mandiri Tunas Finance.">

    {{-- OG Tags --}}
    <meta property="og:type" content="website">
    <meta property="og:title" content="Tag: {{ ucfirst($tagName) }} - Fasilitas Dana MTF">
    <meta property="og:description" content="Telusuri artikel dengan tag {{ ucfirst($tagName) }} dari Fasilitas Dana MTF. Menampilkan {{ $articles->total() }} artikel terkait pembiayaan kendaraan dan tips keuangan.">
    <meta property="og:image" content="{{ asset('images/logo-sm.png') }}">
    <meta property="og:url" content="{{ url()->current() }}">
@endsection

@section('content')
    {{-- Tag Header --}}
    <section class="tag-header">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 mx-auto text-center">
                    {{-- Breadcrumb --}}
                    <nav aria-label="breadcrumb" class="mb-4">
                        <ol class="breadcrumb justify-content-center">
                            <li class="breadcrumb-item">
                                <a href="{{ route('landing.index') }}">Beranda</a>
                            </li>
                            <li class="breadcrumb-item">
                                <a href="{{ route('news.index') }}">Berita</a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">
                                Tag: {{ ucfirst($tagName) }}
                            </li>
                        </ol>
                    </nav>

                    <h1 class="tag-title">{{ ucfirst($tagName) }}</h1>
                    <p class="tag-subtitle">
                        Menampilkan {{ $articles->total() }} artikel dengan tag "{{ ucfirst($tagName) }}"
                    </p>
                </div>
            </div>
        </div>
    </section>

    {{-- Articles List --}}
    <section class="tag-articles-section">
        <div class="container">
            @if($articles->count() > 0)
                {{-- Results Count --}}
                <div class="results-count mb-4">
                    <p>
                        Menampilkan
                        <strong>{{ $articles->firstItem() }}</strong> - <strong>{{ $articles->lastItem() }}</strong>
                        dari <strong>{{ $articles->total() }}</strong> artikel
                    </p>
                </div>

                {{-- Articles Grid --}}
                <div class="row">
                    @foreach($articles as $article)
                        <div class="col-md-6 col-lg-4 mb-4">
                            <x-frontend.news-card :item="$article" size="md" />
                        </div>
                    @endforeach
                </div>

                {{-- Pagination --}}
                @if($articles->hasPages())
                    <div class="pagination-wrapper mt-5">
                        <nav aria-label="Tag articles pagination">
                            <ul class="pagination justify-content-center">
                                {{-- Previous Page Link --}}
                                @if ($articles->onFirstPage())
                                    <li class="page-item disabled">
                                        <span class="page-link">‹</span>
                                    </li>
                                @else
                                    <li class="page-item">
                                        <a class="page-link" href="{{ $articles->previousPageUrl() }}" rel="prev">‹</a>
                                    </li>
                                @endif

                                {{-- Pagination Elements --}}
                                @foreach ($articles->getUrlRange(1, $articles->lastPage()) as $page => $url)
                                    @if ($page == $articles->currentPage())
                                        <li class="page-item active">
                                            <span class="page-link">{{ $page }}</span>
                                        </li>
                                    @else
                                        <li class="page-item">
                                            <a class="page-link" href="{{ $url }}">{{ $page }}</a>
                                        </li>
                                    @endif
                                @endforeach

                                {{-- Next Page Link --}}
                                @if ($articles->hasMorePages())
                                    <li class="page-item">
                                        <a class="page-link" href="{{ $articles->nextPageUrl() }}" rel="next">›</a>
                                    </li>
                                @else
                                    <li class="page-item disabled">
                                        <span class="page-link">›</span>
                                    </li>
                                @endif
                            </ul>
                        </nav>
                    </div>
                @endif

                {{-- Back to All News --}}
                <div class="text-center mt-5">
                    <a href="{{ route('news.index') }}" class="btn btn-outline-primary btn-lg">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="me-2" viewBox="0 0 16 16">
                            <path fill-rule="evenodd" d="M15 8a.5.5 0 0 0-.5-.5H2.707l3.147-3.146a.5.5 0 1 0-.708-.708l-4 4a.5.5 0 0 0 0 .708l4 4a.5.5 0 0 0 .708-.708L2.707 8.5H14.5A.5.5 0 0 0 15 8z"/>
                        </svg>
                        Kembali ke Semua Berita
                    </a>
                </div>
            @else
                {{-- No Articles Found --}}
                <div class="no-results text-center py-5">
                    <svg xmlns="http://www.w3.org/2000/svg" width="80" height="80" fill="currentColor" class="text-muted mb-3" viewBox="0 0 16 16">
                        <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/>
                        <path d="M4.285 9.567a.5.5 0 0 1 .683.183A3.498 3.498 0 0 0 8 11.5a3.498 3.498 0 0 0 3.032-1.75.5.5 0 1 1 .866.5A4.498 4.498 0 0 1 8 12.5a4.498 4.498 0 0 1-3.898-2.25.5.5 0 0 1 .183-.683zM7 6.5C7 7.328 6.552 8 6 8s-1-.672-1-1.5S5.448 5 6 5s1 .672 1 1.5zm4 0c0 .828-.448 1.5-1 1.5s-1-.672-1-1.5S9.448 5 10 5s1 .672 1 1.5z"/>
                    </svg>
                    <h3>Artikel Tidak Ditemukan</h3>
                    <p class="text-muted">
                        Belum ada artikel dengan tag "{{ ucfirst($tagName) }}".
                    </p>
                    <a href="{{ route('news.index') }}" class="btn btn-primary mt-3">Lihat Semua Berita</a>
                </div>
            @endif
        </div>
    </section>
@endsection
