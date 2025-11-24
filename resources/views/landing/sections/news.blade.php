<section class="news-section" id="berita">
    <div class="container">
        {{-- Header with View All Button --}}
        <div class="news-header-wrapper">
            <div class="news-header">
                <h2 class="section-title mb-2">Berita Terkini</h2>
                <p class="section-subtitle mb-0">Informasi dan update terbaru dari kami</p>
            </div>
            {{-- View All Button - Desktop (di kanan atas) --}}
            <div class="news-header-action d-none d-md-block">
                <a href="{{ route('news.index') }}" class="btn btn-outline-primary btn-view-all">
                    Lihat Semua
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="ms-1" viewBox="0 0 16 16">
                        <path fill-rule="evenodd" d="M1 8a.5.5 0 0 1 .5-.5h11.793l-3.147-3.146a.5.5 0 0 1 .708-.708l4 4a.5.5 0 0 1 0 .708l-4 4a.5.5 0 0 1-.708-.708L13.293 8.5H1.5A.5.5 0 0 1 1 8z"/>
                    </svg>
                </a>
            </div>
        </div>

        {{-- News Grid --}}
        <div class="row">
            @foreach($news as $index => $item)
                <div class="col-md-4 mb-4">
                    <x-frontend.news-card :item="$item" size="md" />
                </div>
            @endforeach
        </div>

        {{-- View All Button - Mobile (di bawah) --}}
        <div class="text-center mt-4 d-md-none">
            <a href="{{ route('news.index') }}" class="btn btn-outline-primary btn-view-all">
                Lihat Semua
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="ms-1" viewBox="0 0 16 16">
                    <path fill-rule="evenodd" d="M1 8a.5.5 0 0 1 .5-.5h11.793l-3.147-3.146a.5.5 0 0 1 .708-.708l4 4a.5.5 0 0 1 0 .708l-4 4a.5.5 0 0 1-.708-.708L13.293 8.5H1.5A.5.5 0 0 1 1 8z"/>
                </svg>
            </a>
        </div>
    </div>
</section>
