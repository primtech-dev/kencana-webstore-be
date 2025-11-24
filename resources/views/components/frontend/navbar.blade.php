<nav class="navbar navbar-expand-lg sticky-top" id="mainNavbar">
    <div class="container">
        {{-- Brand/Logo --}}
        <a class="navbar-brand" href="{{ route('landing.index') }}">
            <img src="{{ asset('images/logo-sm.png') }}" alt="logo-mtf">
        </a>

        {{-- Mobile Toggle Button - TANPA data-bs attributes --}}
        <button class="navbar-toggler collapsed"
                type="button"
                aria-controls="navbarNav"
                aria-expanded="false"
                aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        {{-- Navigation Menu --}}
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('landing.index') ? 'active' : '' }}"
                       href="{{ route('landing.index') }}">
                        Beranda
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('landing.about-us') ? 'active' : '' }}"
                       href="{{ route('landing.about-us') }}">
                        Tentang Kami
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('product') ? 'active' : '' }}"
                       href="{{ route('landing.product') }}">
                        Produk & Layanan
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link"
                       href="{{ route('landing.index') }}#pengajuan-online">
                        Pengajuan Online
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('news.*') ? 'active' : '' }}"
                       href="{{ route('news.index') }}">
                        Berita
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('contact') ? 'active' : '' }}"
                       href="{{ route('landing.contact') }}">
                        Kontak
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>
