<!-- Sidenav Menu Start -->
<div class="sidenav-menu">
    <div class="scrollbar" data-simplebar>

        <!-- User -->
        <div class="sidenav-user text-nowrap border border-dashed rounded-3">
            <a href="#!" class="sidenav-user-name d-flex align-items-center">
                <img src="https://ui-avatars.com/api/?background=007fff&color=fff&name={{ urlencode(auth()->user()->name) }}"
                     class="rounded-circle me-2 d-flex" width="32" alt="avatar">
                <span>
                    <h5 class="my-0 fw-semibold">
                        {{ Auth::user()->name ?? 'Tamu' }}
                    </h5>
                    <h6 class="my-0 text-muted">
                        {{ Str::of(Auth::user()->getRoleNames()->first() ?? 'Tidak Ada Peran')->replace('-', ' ')->title() }}
                    </h6>
                </span>
            </a>
        </div>

        <!-- Sidenav Menu -->
        <ul class="side-nav">

            <li class="side-nav-title mt-2">Manajemen Konten</li>

            <li class="side-nav-item">
                <a href="{{ route('articles.index') }}" class="side-nav-link">
                    <span class="menu-icon"><i data-lucide="newspaper"></i></span>
                    <span class="menu-text">Artikel</span>
                </a>
            </li>

            <li class="side-nav-item">
                <a href="{{ route('tags.index') }}" class="side-nav-link">
                    <span class="menu-icon"><i data-lucide="tags"></i></span>
                    <span class="menu-text">Tag</span>
                </a>
            </li>

            <li class="side-nav-item">
                <a href="{{ route('faqs.index') }}" class="side-nav-link">
                    <span class="menu-icon"><i data-lucide="circle-help"></i></span>
                    <span class="menu-text">FAQ</span>
                </a>
            </li>

            <li class="side-nav-item">
                <a href="{{ route('testimonials.index') }}" class="side-nav-link">
                    <span class="menu-icon"><i data-lucide="quote"></i></span>
                    <span class="menu-text">Testimoni</span>
                </a>
            </li>

            <li class="side-nav-item">
                <a href="{{ route('products.index') }}" class="side-nav-link">
                    <span class="menu-icon"><i data-lucide="package"></i></span>
                    <span class="menu-text">Produk</span>
                </a>
            </li>

            <li class="side-nav-title mt-2">Pelanggan</li>

            <li class="side-nav-item">
                <a href="{{ route('customers.index') }}" class="side-nav-link">
                    <span class="menu-icon"><i data-lucide="users"></i></span>
                    <span class="menu-text">Pelanggan</span>
                </a>
            </li>

            <li class="side-nav-item">
                <a href="{{ route('submissions.index') }}" class="side-nav-link">
                    <span class="menu-icon"><i data-lucide="inbox"></i></span>
                    <span class="menu-text">Pengajuan</span>
                </a>
            </li>

            <li class="side-nav-title mt-2">Simulasi Kredit</li>

            <li class="side-nav-item">
                <a href="{{ route('credit-simulation.index') }}" class="side-nav-link">
                    <span class="menu-icon"><i data-lucide="calculator"></i></span>
                    <span class="menu-text">Simulasi Kredit</span>
                </a>
            </li>

            <li class="side-nav-title mt-2">Pengguna & Akses</li>

            <li class="side-nav-item">
                <a href="{{ route('users.index') }}" class="side-nav-link">
                    <span class="menu-icon"><i data-lucide="user-cog"></i></span>
                    <span class="menu-text">Pengguna</span>
                </a>
            </li>

        </ul>
    </div>
</div>
<!-- Sidenav Menu End -->
