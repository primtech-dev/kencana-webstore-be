@extends('landing.layout')

@section('title', 'Tentang Kami - Fasilitas Dana MTF')

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
                  "name": "Tentang Kami",
                  "item": "{{ url()->current() }}"
                }
              ]
            },
            {
              "@@type": "AboutPage",
              "url": "{{ url()->current() }}",
              "name": "Tentang Kami",
              "description": "Mandiri Tunas Finance (MTF) adalah perusahaan pembiayaan terkemuka di Indonesia yang menyediakan solusi keuangan terbaik dan terpercaya bagi pelanggan.",
              "publisher": {
                "@@type": "Organization",
                "name": "Mandiri Tunas Finance",
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
    <meta name="description" content="Mandiri Tunas Finance (MTF) adalah perusahaan pembiayaan terkemuka di Indonesia yang menyediakan solusi keuangan terbaik dan terpercaya bagi pelanggan.">

    <meta property="og:type" content="website">
    <meta property="og:title" content="Tentang Kami - Fasilitas Dana MTF">
    <meta property="og:description" content="Mandiri Tunas Finance (MTF) adalah perusahaan pembiayaan terkemuka di Indonesia yang menyediakan solusi keuangan terbaik dan terpercaya bagi pelanggan.">
    <meta property="og:image" content="{{ asset('images/logo-sm.png') }}">
    <meta property="og:url" content="{{ url()->current() }}">
@endsection

@section('content')
    {{-- Hero Section --}}
    <section class="about-hero">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <h1 class="about-hero-title" data-aos="fade-up">Tentang Fasilitas Dana MTF</h1>
                    <p class="about-hero-subtitle" data-aos="fade-up" data-aos-delay="100">
                        Mandiri Tunas Finance (MTF) adalah perusahaan pembiayaan terkemuka di Indonesia yang
                        menyediakan solusi keuangan terbaik dan terpercaya bagi pelanggan.
                    </p>
                </div>
                <div class="col-lg-6" data-aos="fade-left" data-aos-delay="300">
                    <div class="about-hero-image">
                        <img src="{{ asset('images/about-hero.png') }}" alt="About MTF">
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Vision & Mission --}}
    <section class="vision-mission-section">
        <div class="container">
            <div class="row">
                {{-- Vision --}}
                <div class="col-lg-6 mb-4" data-aos="fade-up">
                    <div class="vm-card vision-card">
                        <div class="vm-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" fill="currentColor" viewBox="0 0 16 16">
                                <path d="M16 8s-3-5.5-8-5.5S0 8 0 8s3 5.5 8 5.5S16 8 16 8zM1.173 8a13.133 13.133 0 0 1 1.66-2.043C4.12 4.668 5.88 3.5 8 3.5c2.12 0 3.879 1.168 5.168 2.457A13.133 13.133 0 0 1 14.828 8c-.058.087-.122.183-.195.288-.335.48-.83 1.12-1.465 1.755C11.879 11.332 10.119 12.5 8 12.5c-2.12 0-3.879-1.168-5.168-2.457A13.134 13.134 0 0 1 1.172 8z"/>
                                <path d="M8 5.5a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5zM4.5 8a3.5 3.5 0 1 1 7 0 3.5 3.5 0 0 1-7 0z"/>
                            </svg>
                        </div>
                        <h3 class="vm-title">Visi Kami</h3>
                        <div class="vm-quote">
                            <p>
                                "Menjadi perusahaan pembiayaan terkemuka di Indonesia dengan memberikan
                                solusi keuangan yang terbaik dan terpercaya bagi pelanggan."
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Mission --}}
                <div class="col-lg-6 mb-4" data-aos="fade-up" data-aos-delay="100">
                    <div class="vm-card mission-card">
                        <div class="vm-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" fill="currentColor" viewBox="0 0 16 16">
                                <path d="M9.669.864 8 0 6.331.864l-1.858.282-.842 1.68-1.337 1.32L2.6 6l-.306 1.854 1.337 1.32.842 1.68 1.858.282L8 12l1.669-.864 1.858-.282.842-1.68 1.337-1.32L13.4 6l.306-1.854-1.337-1.32-.842-1.68L9.669.864zm1.196 1.193.684 1.365 1.086 1.072L12.387 6l.248 1.506-1.086 1.072-.684 1.365-1.51.229L8 10.874l-1.355-.702-1.51-.229-.684-1.365-1.086-1.072L3.614 6l-.25-1.506 1.087-1.072.684-1.365 1.51-.229L8 1.126l1.356.702 1.509.229z"/>
                                <path d="M4 11.794V16l4-1 4 1v-4.206l-2.018.306L8 13.126 6.018 12.1 4 11.794z"/>
                            </svg>
                        </div>
                        <h3 class="vm-title">Misi Kami</h3>
                        <ul class="mission-list">
                            <li>Menyediakan solusi pembiayaan yang inovatif, kompetitif, dan sesuai kebutuhan pelanggan</li>
                            <li>Memberikan pelayanan terbaik untuk mencapai kepuasan pelanggan</li>
                            <li>Mengembangkan SDM yang profesional, berintegritas, dan berorientasi pada kinerja</li>
                            <li>Meningkatkan nilai bagi pemegang saham melalui pertumbuhan bisnis yang berkelanjutan</li>
                            <li>Menjalankan bisnis dengan tata kelola perusahaan yang baik (GCG)</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Our Advantages --}}
    <section class="advantages-section">
        <div class="container">
            <h2 class="section-title" data-aos="fade-up">Keunggulan Kami</h2>
            <p class="section-subtitle" data-aos="fade-up" data-aos-delay="100">
                Mengapa memilih Fasilitas Dana MTF sebagai partner pembiayaan Anda
            </p>

            <div class="row">
                <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="100">
                    <div class="advantage-card">
                        <div class="advantage-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" fill="currentColor" viewBox="0 0 16 16">
                                <path d="M8.186 1.113a.5.5 0 0 0-.372 0L1.846 3.5l2.404.961L10.404 2l-2.218-.887zm3.564 1.426L5.596 5 8 5.961 14.154 3.5l-2.404-.961zm3.25 1.7-6.5 2.6v7.922l6.5-2.6V4.24zM7.5 14.762V6.838L1 4.239v7.923l6.5 2.6zM7.443.184a1.5 1.5 0 0 1 1.114 0l7.129 2.852A.5.5 0 0 1 16 3.5v8.662a1 1 0 0 1-.629.928l-7.185 2.874a.5.5 0 0 1-.372 0L.63 13.09a1 1 0 0 1-.63-.928V3.5a.5.5 0 0 1 .314-.464L7.443.184z"/>
                            </svg>
                        </div>
                        <h4>Dukungan Institusi Besar</h4>
                        <p>Anak perusahaan dari Bank Mandiri dan PT Tunas Ridean Tbk dengan backing keuangan dan jaringan yang luas.</p>
                    </div>
                </div>

                <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="200">
                    <div class="advantage-card">
                        <div class="advantage-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" fill="currentColor" viewBox="0 0 16 16">
                                <path d="M0 4a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V4zm2-1a1 1 0 0 0-1 1v.217l7 4.2 7-4.2V4a1 1 0 0 0-1-1H2zm13 2.383-4.708 2.825L15 11.105V5.383zm-.034 6.876-5.64-3.471L8 9.583l-1.326-.795-5.64 3.47A1 1 0 0 0 2 13h12a1 1 0 0 0 .966-.741zM1 11.105l4.708-2.897L1 5.383v5.722z"/>
                            </svg>
                        </div>
                        <h4>Jaringan Cabang Luas</h4>
                        <p>Memiliki banyak cabang dan titik layanan di berbagai wilayah Indonesia untuk kemudahan akses.</p>
                    </div>
                </div>

                <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="300">
                    <div class="advantage-card">
                        <div class="advantage-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" fill="currentColor" viewBox="0 0 16 16">
                                <path d="M1 2.5A1.5 1.5 0 0 1 2.5 1h3A1.5 1.5 0 0 1 7 2.5v3A1.5 1.5 0 0 1 5.5 7h-3A1.5 1.5 0 0 1 1 5.5v-3zM2.5 2a.5.5 0 0 0-.5.5v3a.5.5 0 0 0 .5.5h3a.5.5 0 0 0 .5-.5v-3a.5.5 0 0 0-.5-.5h-3zm6.5.5A1.5 1.5 0 0 1 10.5 1h3A1.5 1.5 0 0 1 15 2.5v3A1.5 1.5 0 0 1 13.5 7h-3A1.5 1.5 0 0 1 9 5.5v-3zm1.5-.5a.5.5 0 0 0-.5.5v3a.5.5 0 0 0 .5.5h3a.5.5 0 0 0 .5-.5v-3a.5.5 0 0 0-.5-.5h-3zM1 10.5A1.5 1.5 0 0 1 2.5 9h3A1.5 1.5 0 0 1 7 10.5v3A1.5 1.5 0 0 1 5.5 15h-3A1.5 1.5 0 0 1 1 13.5v-3zm1.5-.5a.5.5 0 0 0-.5.5v3a.5.5 0 0 0 .5.5h3a.5.5 0 0 0 .5-.5v-3a.5.5 0 0 0-.5-.5h-3zm6.5.5A1.5 1.5 0 0 1 10.5 9h3a1.5 1.5 0 0 1 1.5 1.5v3a1.5 1.5 0 0 1-1.5 1.5h-3A1.5 1.5 0 0 1 9 13.5v-3zm1.5-.5a.5.5 0 0 0-.5.5v3a.5.5 0 0 0 .5.5h3a.5.5 0 0 0 .5-.5v-3a.5.5 0 0 0-.5-.5h-3z"/>
                            </svg>
                        </div>
                        <h4>Produk Beragam & Fleksibel</h4>
                        <p>Pembiayaan mobil baru, multiguna, kendaraan niaga, fleet, alat berat, dan banyak lagi.</p>
                    </div>
                </div>

                <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="100">
                    <div class="advantage-card">
                        <div class="advantage-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" fill="currentColor" viewBox="0 0 16 16">
                                <path d="M11 1a2 2 0 0 1 2 2v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h6zM5 2a1 1 0 0 0-1 1v4a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V3a1 1 0 0 0-1-1H5zM1 13.5A1.5 1.5 0 0 1 2.5 12h11a1.5 1.5 0 0 1 1.5 1.5v1a1.5 1.5 0 0 1-1.5 1.5h-11A1.5 1.5 0 0 1 1 14.5v-1zm1.5-.5a.5.5 0 0 0-.5.5v1a.5.5 0 0 0 .5.5h11a.5.5 0 0 0 .5-.5v-1a.5.5 0 0 0-.5-.5h-11z"/>
                            </svg>
                        </div>
                        <h4>Inovasi Digital</h4>
                        <p>Aplikasi "Livin' Auto Loan" untuk pengajuan kredit kendaraan secara online dengan proses yang cepat dan mudah.</p>
                    </div>
                </div>

                <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="200">
                    <div class="advantage-card">
                        <div class="advantage-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" fill="currentColor" viewBox="0 0 16 16">
                                <path d="M8.864.046C7.908-.193 7.02.53 6.956 1.466c-.072 1.051-.23 2.016-.428 2.59-.125.36-.479 1.013-1.04 1.639-.557.623-1.282 1.178-2.131 1.41C2.685 7.288 2 7.87 2 8.72v4.001c0 .845.682 1.464 1.448 1.545 1.07.114 1.564.415 2.068.723l.048.03c.272.165.578.348.97.484.397.136.861.217 1.466.217h3.5c.937 0 1.599-.477 1.934-1.064a1.86 1.86 0 0 0 .254-.912c0-.152-.023-.312-.077-.464.201-.263.38-.578.488-.901.11-.33.172-.762.004-1.149.069-.13.12-.269.159-.403.077-.27.113-.568.113-.857 0-.288-.036-.585-.113-.856a2.144 2.144 0 0 0-.138-.362 1.9 1.9 0 0 0 .234-1.734c-.206-.592-.682-1.1-1.2-1.272-.847-.282-1.803-.276-2.516-.211a9.84 9.84 0 0 0-.443.05 9.365 9.365 0 0 0-.062-4.509A1.38 1.38 0 0 0 9.125.111L8.864.046zM11.5 14.721H8c-.51 0-.863-.069-1.14-.164-.281-.097-.506-.228-.776-.393l-.04-.024c-.555-.339-1.198-.731-2.49-.868-.333-.036-.554-.29-.554-.55V8.72c0-.254.226-.543.62-.65 1.095-.3 1.977-.996 2.614-1.708.635-.71 1.064-1.475 1.238-1.978.243-.7.407-1.768.482-2.85.025-.362.36-.594.667-.518l.262.066c.16.04.258.143.288.255a8.34 8.34 0 0 1-.145 4.725.5.5 0 0 0 .595.644l.003-.001.014-.003.058-.014a8.908 8.908 0 0 1 1.036-.157c.663-.06 1.457-.054 2.11.164.175.058.45.3.57.65.107.308.087.67-.266 1.022l-.353.353.353.354c.043.043.105.141.154.315.048.167.075.37.075.581 0 .212-.027.414-.075.582-.05.174-.111.272-.154.315l-.353.353.353.354c.047.047.109.177.005.488a2.224 2.224 0 0 1-.505.805l-.353.353.353.354c.006.005.041.05.041.17a.866.866 0 0 1-.121.416c-.165.288-.503.56-1.066.56z"/>
                            </svg>
                        </div>
                        <h4>Kinerja & Rating Terpercaya</h4>
                        <p>Peringkat yang baik dari lembaga pemeringkat dan penghargaan "The Excellent Performance Multifinance Company".</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="culture-section">
        <div class="container">
            <h2 class="section-title" data-aos="fade-up">Budaya Kerja Kami</h2>
            <p class="section-subtitle" data-aos="fade-up" data-aos-delay="100">
                Nilai-nilai yang menjadi fondasi dalam setiap langkah kami
            </p>

            <div class="culture-wrapper">
                <div class="culture-item" data-aos="fade-up" data-aos-delay="100">
                    <div class="culture-icon">
                        <div class="culture-icon-circle">
                            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="currentColor" viewBox="0 0 16 16">
                                <path d="M15.854.146a.5.5 0 0 1 .11.54l-5.819 14.547a.75.75 0 0 1-1.329.124l-3.178-4.995L.643 7.184a.75.75 0 0 1 .124-1.33L15.314.037a.5.5 0 0 1 .54.11ZM6.636 10.07l2.761 4.338L14.13 2.576 6.636 10.07Zm6.787-8.201L1.591 6.602l4.339 2.76 7.494-7.493Z"/>
                            </svg>
                        </div>
                    </div>
                    <h3>Kepercayaan</h3>
                    <div class="culture-values">
                        <span class="value-badge">Berintegritas</span>
                        <span class="value-badge">Jujur</span>
                        <span class="value-badge">Bertanggung Jawab</span>
                        <span class="value-badge">Berkomitmen</span>
                    </div>
                    <p>Disiplin dan bertanggung jawab, menjunjung tinggi prinsip kebenaran dalam berpikir, bertindak, dan berperilaku sesuai dengan yang dijanjikan.</p>
                </div>

                <div class="culture-item" data-aos="fade-up" data-aos-delay="200">
                    <div class="culture-icon">
                        <div class="culture-icon-circle">
                            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="currentColor" viewBox="0 0 16 16">
                                <path d="M8 3.5a.5.5 0 0 0-1 0V9a.5.5 0 0 0 .252.434l3.5 2a.5.5 0 0 0 .496-.868L8 8.71V3.5z"/>
                                <path d="M8 16A8 8 0 1 0 8 0a8 8 0 0 0 0 16zm7-8A7 7 0 1 1 1 8a7 7 0 0 1 14 0z"/>
                            </svg>
                        </div>
                    </div>
                    <h3>Kewirausahaan</h3>
                    <div class="culture-values">
                        <span class="value-badge">Rasa Memiliki</span>
                        <span class="value-badge">Tumbuh Sehat</span>
                        <span class="value-badge">Fokus Pelanggan</span>
                    </div>
                    <p>Tumbuh secara konsisten dengan cara benar dari awal, didasari rasa memiliki, menciptakan nilai tambah dan layanan terbaik.</p>
                </div>

                <div class="culture-item" data-aos="fade-up" data-aos-delay="300">
                    <div class="culture-icon">
                        <div class="culture-icon-circle">
                            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="currentColor" viewBox="0 0 16 16">
                                <path d="M9.669.864 8 0 6.331.864l-1.858.282-.842 1.68-1.337 1.32L2.6 6l-.306 1.854 1.337 1.32.842 1.68 1.858.282L8 12l1.669-.864 1.858-.282.842-1.68 1.337-1.32L13.4 6l.306-1.854-1.337-1.32-.842-1.68L9.669.864zm1.196 1.193.684 1.365 1.086 1.072L12.387 6l.248 1.506-1.086 1.072-.684 1.365-1.51.229L8 10.874l-1.355-.702-1.51-.229-.684-1.365-1.086-1.072L3.614 6l-.25-1.506 1.087-1.072.684-1.365 1.51-.229L8 1.126l1.356.702 1.509.229z"/>
                                <path d="M4 11.794V16l4-1 4 1v-4.206l-2.018.306L8 13.126 6.018 12.1 4 11.794z"/>
                            </svg>
                        </div>
                    </div>
                    <h3>Inovatif</h3>
                    <div class="culture-values">
                        <span class="value-badge">Adaptif</span>
                        <span class="value-badge">Terus Belajar</span>
                        <span class="value-badge">Kreatif</span>
                    </div>
                    <p>Terbuka terhadap perubahan dengan menciptakan ide melalui belajar terus menerus untuk menghasilkan solusi kreatif.</p>
                </div>

                <div class="culture-item" data-aos="fade-up" data-aos-delay="400">
                    <div class="culture-icon">
                        <div class="culture-icon-circle">
                            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="currentColor" viewBox="0 0 16 16">
                                <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/>
                                <path d="M4.285 9.567a.5.5 0 0 1 .683.183A3.498 3.498 0 0 0 8 11.5a3.498 3.498 0 0 0 3.032-1.75.5.5 0 1 1 .866.5A4.498 4.498 0 0 1 8 12.5a4.498 4.498 0 0 1-3.898-2.25.5.5 0 0 1 .183-.683zM7 6.5C7 7.328 6.552 8 6 8s-1-.672-1-1.5S5.448 5 6 5s1 .672 1 1.5zm4 0c0 .828-.448 1.5-1 1.5s-1-.672-1-1.5S9.448 5 10 5s1 .672 1 1.5z"/>
                            </svg>
                        </div>
                    </div>
                    <h3>Kegembiraan</h3>
                    <div class="culture-values">
                        <span class="value-badge">Antusias</span>
                        <span class="value-badge">Bersinergi</span>
                        <span class="value-badge">Pantang Menyerah</span>
                    </div>
                    <p>Suasana kerja menyenangkan yang dibangun dengan kebersamaan, rasa bangga serta semangat pantang menyerah.</p>
                </div>
            </div>
        </div>
    </section>

    {{-- CTA Section --}}
    <section class="about-cta-section">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-10">
                    <div class="cta-content text-center" data-aos="zoom-in">
                        <h2>Siap Mewujudkan Impian Anda?</h2>
                        <p>Bergabunglah dengan ribuan pelanggan yang telah mempercayai kami sebagai partner pembiayaan mereka.</p>

                        <div class="cta-buttons">
                            <a href="{{ route('landing.index') }}#pengajuan-online" class="btn btn-primary btn-lg">
                                Ajukan Sekarang
                            </a>
                            <a href="{{ route('landing.index') }}#kontak" class="btn btn-outline-primary btn-lg">
                                Hubungi Kami
                            </a>
                        </div>

                        <div class="cta-features">
                            <div class="feature-item">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 16 16">
                                    <path d="M10.97 4.97a.75.75 0 0 1 1.07 1.05l-3.99 4.99a.75.75 0 0 1-1.08.02L4.324 8.384a.75.75 0 1 1 1.06-1.06l2.094 2.093 3.473-4.425a.267.267 0 0 1 .02-.022z"/>
                                </svg>
                                <span>Proses Cepat</span>
                            </div>
                            <div class="feature-item">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 16 16">
                                    <path d="M10.97 4.97a.75.75 0 0 1 1.07 1.05l-3.99 4.99a.75.75 0 0 1-1.08.02L4.324 8.384a.75.75 0 1 1 1.06-1.06l2.094 2.093 3.473-4.425a.267.267 0 0 1 .02-.022z"/>
                                </svg>
                                <span>Terpercaya</span>
                            </div>
                            <div class="feature-item">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 16 16">
                                    <path d="M10.97 4.97a.75.75 0 0 1 1.07 1.05l-3.99 4.99a.75.75 0 0 1-1.08.02L4.324 8.384a.75.75 0 1 1 1.06-1.06l2.094 2.093 3.473-4.425a.267.267 0 0 1 .02-.022z"/>
                                </svg>
                                <span>Bunga Kompetitif</span>
                            </div>
                        </div>
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
