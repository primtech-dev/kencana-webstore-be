<footer class="footer-landing" id="kontak">
    <div class="container">
        <div class="row">
            <div class="col-md-4 mb-4">
                <h5>Tentang Fasilitas Dana MTF</h5>
                <p>Mandiri Tunas Finance (MTF) adalah perusahaan pembiayaan (leasing) di Indonesia yang menyediakan layanan pembiayaan kendaraan bermotor dan produk keuangan lainnya.</p>
            </div>
            <div class="col-md-4 mb-4">
                <h5>Link Cepat</h5>
                <ul class="list-unstyled">
                    <li class="mb-2">
                        <a href="{{ route('landing.index') }}"
                           class="{{ request()->routeIs('landing.index') ? 'active' : '' }}">
                            Beranda
                        </a>
                    </li>
                    <li class="mb-2">
                        <a href="{{ route('landing.about-us') }}"
                           class="{{ request()->routeIs('landing.about-us') ? 'active' : '' }}">
                            Tentang Kita
                        </a>
                    </li>
                    <li class="mb-2">
                        <a href="{{ route('landing.product') }}"
                           class="{{ request()->routeIs('product') ? 'active' : '' }}">
                            Produk & Layanan
                        </a>
                    </li>
                    <li class="mb-2">
                        <a href="{{ route('landing.index') }}#pengajuan-online">
                            Pengajuan Online
                        </a>
                    </li>
                    <li class="mb-2">
                        <a href="{{ route('news.index') }}"
                           class="{{ request()->routeIs('news.*') ? 'active' : '' }}">
                            Berita
                        </a>
                    </li>
                    <li class="mb-2">
                        <a href="{{ route('landing.contact') }}"
                           class="{{ request()->routeIs('contact') ? 'active' : '' }}">
                            Kontak
                        </a>
                    </li>
                </ul>
            </div>
            <div class="col-md-4 mb-4">
                <h5>Hubungi Kami</h5>
                <p><strong>Alamat:</strong><br>Ruko Gajah Mada Square, Jl. Gajah Mada No.187, Kaliwates Kidul, Kaliwates, Kec. Kaliwates, Kabupaten Jember, Jawa Timur 68133</p>
                <p><strong>Whatsapp:</strong> +62 8578 4242 462</p>

                {{-- Social Media --}}
                <div class="social-media mt-3">
                    <a href="https://www.facebook.com/MTFAutoLoan" target="_blank" class="social-link me-2" aria-label="Facebook">
                        <i class="fab fa-facebook"></i>
                    </a>
                    <a href="https://www.instagram.com/mtf_jember/" target="_blank" class="social-link me-2" aria-label="Instagram">
                        <i class="fab fa-instagram"></i>
                    </a>
                    <a href="https://x.com/mandiritunasfin" target="_blank" class="social-link me-2" aria-label="Twitter">
                        <i>
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 16 16">
                                <path d="M12.6.75h2.454l-5.36 6.142L16 15.25h-4.937l-3.867-5.07-4.425 5.07H.316l5.733-6.57L0 .75h5.063l3.495 4.633L12.601.75Zm-.86 13.028h1.36L4.323 2.145H2.865l8.875 11.633Z"/>
                            </svg>
                        </i>
                    </a>
                </div>
            </div>
        </div>
        <div class="footer-bottom text-center">
            <p class="mb-0">&copy; 2025 Kreasi Kode Kraftek. All rights reserved.</p>
        </div>
    </div>
</footer>
