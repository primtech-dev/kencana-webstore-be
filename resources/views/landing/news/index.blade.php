@extends('landing.layout')

@section('title', 'Berita Terkini - Fasilitas Dana MTF')

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
                  "item": "{{ url()->current() }}"
                }
              ]
            },
            {
              "@@type": "CollectionPage",
              "url": "{{ url()->current() }}",
              "name": "Berita",
              "description": "Berita dan update terbaru Mandiri Tunas Finance."
            }
          ]
        }
    </script>
@endsection

@section('meta')
    <meta name="description" content="Berita dan update terbaru dari Fasilitas Dana MTF">

    <meta property="og:type" content="website">
    <meta property="og:title" content="Berita Terkini - Fasilitas Dana MTF">
    <meta property="og:description" content="Berita dan update terbaru dari Fasilitas Dana MTF">
    <meta property="og:image" content="{{ asset('images/logo-sm.png') }}">
    <meta property="og:url" content="{{ url()->current() }}">
@endsection

@section('content')
    {{-- Page Header --}}
    <section class="news-page-header">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 mx-auto text-center">
                    <h1 class="page-title">Berita Terkini</h1>
                    <p class="page-subtitle">Informasi dan update terbaru dari Fasilitas Dana MTF</p>
                </div>
            </div>
        </div>
    </section>

    {{-- Search Section --}}
    <section class="news-filter-section">
        <div class="container">
            <div class="row justify-content-center">
                {{-- Search Bar --}}
                <div class="col-lg-12">
                    <form action="{{ route('news.index') }}" method="GET" class="search-form">
                        <div class="input-group">
                            <input type="text"
                                   class="form-control"
                                   name="search"
                                   placeholder="Cari berita..."
                                   value="{{ $search ?? '' }}">
                            <button class="btn btn-primary" type="submit">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                                    <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001c.03.04.062.078.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1.007 1.007 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0z"/>
                                </svg>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Active Search Info --}}
            @if($search)
                <div class="active-filters mt-3">
                    <div class="d-flex justify-content-center align-items-center gap-2">
                        <span class="filter-label">Pencarian:</span>
                        <span class="filter-tag">
                            "{{ $search }}"
                            <a href="{{ route('news.index') }}" class="remove-filter">×</a>
                        </span>
                    </div>
                </div>
            @endif
        </div>
    </section>

    {{-- News Grid Section --}}
    <section class="news-listing-section">
        <div class="container">
            @if($news->count() > 0)
                {{-- Results Count --}}
                <div class="results-count mb-4">
                    <p>
                        Menampilkan
                        <strong>{{ $news->firstItem() }}</strong> - <strong>{{ $news->lastItem() }}</strong>
                        dari <strong>{{ $news->total() }}</strong> berita
                    </p>
                </div>

                {{-- News Grid --}}
                <div class="row">
                    @foreach($news as $item)
                        <div class="col-md-6 col-lg-4 mb-4">
                            <x-frontend.news-card :item="$item" size="md" />
                        </div>
                    @endforeach
                </div>

                {{-- Pagination --}}
                @if($news->hasPages())
                    <div class="pagination-wrapper mt-5">
                        <nav aria-label="News pagination">
                            <ul class="pagination justify-content-center">
                                {{-- Previous Page Link --}}
                                @if ($news->onFirstPage())
                                    <li class="page-item disabled">
                                        <span class="page-link">‹</span>
                                    </li>
                                @else
                                    <li class="page-item">
                                        <a class="page-link" href="{{ $news->previousPageUrl() }}" rel="prev">‹</a>
                                    </li>
                                @endif

                                {{-- Pagination Elements --}}
                                @foreach ($news->getUrlRange(1, $news->lastPage()) as $page => $url)
                                    @if ($page == $news->currentPage())
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
                                @if ($news->hasMorePages())
                                    <li class="page-item">
                                        <a class="page-link" href="{{ $news->nextPageUrl() }}" rel="next">›</a>
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
            @else
                {{-- No Results --}}
                <div class="no-results text-center py-5">
                    <svg xmlns="http://www.w3.org/2000/svg" width="80" height="80" fill="currentColor" class="text-muted mb-3" viewBox="0 0 16 16">
                        <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/>
                        <path d="M4.285 9.567a.5.5 0 0 1 .683.183A3.498 3.498 0 0 0 8 11.5a3.498 3.498 0 0 0 3.032-1.75.5.5 0 1 1 .866.5A4.498 4.498 0 0 1 8 12.5a4.498 4.498 0 0 1-3.898-2.25.5.5 0 0 1 .183-.683zM7 6.5C7 7.328 6.552 8 6 8s-1-.672-1-1.5S5.448 5 6 5s1 .672 1 1.5zm4 0c0 .828-.448 1.5-1 1.5s-1-.672-1-1.5S9.448 5 10 5s1 .672 1 1.5z"/>
                    </svg>
                    <h3>Berita Tidak Ditemukan</h3>
                    <p class="text-muted">
                        @if($search)
                            Maaf, tidak ada berita yang sesuai dengan pencarian "{{ $search }}".
                        @else
                            Belum ada berita yang tersedia saat ini.
                        @endif
                    </p>
                    <a href="{{ route('news.index') }}" class="btn btn-primary mt-3">Lihat Semua Berita</a>
                </div>
            @endif
        </div>
    </section>
@endsection
