@extends('landing.layout')

@section('title', 'Hubungi Kami - Fasilitas Dana MTF')

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
                  "name": "Kontak",
                  "item": "{{ url()->current() }}"
                }
              ]
            },
            {
              "@@type": "ContactPage",
              "url": "{{ url()->current() }}",
              "name": "Kontak Mandiri Tunas Finance Jember",
              "description": "Hubungi Mandiri Tunas Finance Jember untuk informasi pembiayaan, layanan pelanggan, dan konsultasi langsung."
            }
          ]
        }
    </script>
@endsection


@section('meta')
    {{-- Meta Description --}}
    <meta name="description" content="Hubungi Fasilitas Dana MTF untuk konsultasi pembiayaan kendaraan, layanan pelanggan, atau informasi produk Mandiri Tunas Finance. Kami siap membantu Anda melalui WhatsApp, telepon, dan kunjungan langsung ke kantor.">

    {{-- OG Tags --}}
    <meta property="og:type" content="website">
    <meta property="og:title" content="Hubungi Kami - Fasilitas Dana MTF">
    <meta property="og:description" content="Butuh bantuan? Hubungi Fasilitas Dana MTF untuk informasi pembiayaan kendaraan, layanan pelanggan, atau konsultasi langsung. Kami siap membantu Anda.">
    <meta property="og:image" content="{{ asset('images/logo-sm.png') }}">
    <meta property="og:url" content="{{ url()->current() }}">
@endsection

@section('content')
    {{-- Contact Hero --}}
    <section class="contact-hero">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8 text-center">
                    <h1 class="contact-hero-title" data-aos="fade-up">Hubungi Kami</h1>
                    <p class="contact-hero-subtitle" data-aos="fade-up" data-aos-delay="100">
                        Kami siap membantu Anda mewujudkan impian dengan solusi pembiayaan terbaik.
                        Jangan ragu untuk menghubungi kami.
                    </p>
                </div>
            </div>
        </div>
    </section>

    {{-- Contact Info Section --}}
    <section class="contact-info-section">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-10">
                    <div class="contact-info-wrapper">
                        <div class="row">
                            {{-- Left Column - Contact Details --}}
                            <div class="col-lg-6 mb-4" data-aos="fade-right">
                                <h2 class="contact-info-title">Informasi Kontak</h2>
                                <p class="contact-info-subtitle">
                                    Hubungi kami melalui berbagai saluran komunikasi yang tersedia
                                </p>

                                <div class="contact-info-items">
                                    {{-- Address --}}
                                    <div class="contact-info-item">
                                        <div class="contact-icon">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 16 16">
                                                <path d="M8 16s6-5.686 6-10A6 6 0 0 0 2 6c0 4.314 6 10 6 10zm0-7a3 3 0 1 1 0-6 3 3 0 0 1 0 6z"/>
                                            </svg>
                                        </div>
                                        <div class="contact-details">
                                            <h4>Alamat Kantor</h4>
                                            <p>Ruko Gajah Mada Square, Jl. Gajah Mada No.187<br>
                                                Kaliwates Kidul, Kaliwates<br>
                                                Kec. Kaliwates, Kabupaten Jember<br>
                                                Jawa Timur 68133</p>
                                        </div>
                                    </div>

                                    {{-- WhatsApp --}}
                                    <div class="contact-info-item">
                                        <div class="contact-icon">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 16 16">
                                                <path d="M13.601 2.326A7.854 7.854 0 0 0 7.994 0C3.627 0 .068 3.558.064 7.926c0 1.399.366 2.76 1.057 3.965L0 16l4.204-1.102a7.933 7.933 0 0 0 3.79.965h.004c4.368 0 7.926-3.558 7.93-7.93A7.898 7.898 0 0 0 13.6 2.326zM7.994 14.521a6.573 6.573 0 0 1-3.356-.92l-.24-.144-2.494.654.666-2.433-.156-.251a6.56 6.56 0 0 1-1.007-3.505c0-3.626 2.957-6.584 6.591-6.584a6.56 6.56 0 0 1 4.66 1.931 6.557 6.557 0 0 1 1.928 4.66c-.004 3.639-2.961 6.592-6.592 6.592zm3.615-4.934c-.197-.099-1.17-.578-1.353-.646-.182-.065-.315-.099-.445.099-.133.197-.513.646-.627.775-.114.133-.232.148-.43.05-.197-.1-.836-.308-1.592-.985-.59-.525-.985-1.175-1.103-1.372-.114-.198-.011-.304.088-.403.087-.088.197-.232.296-.346.1-.114.133-.198.198-.33.065-.134.034-.248-.015-.347-.05-.099-.445-1.076-.612-1.47-.16-.389-.323-.335-.445-.34-.114-.007-.247-.007-.38-.007a.729.729 0 0 0-.529.247c-.182.198-.691.677-.691 1.654 0 .977.71 1.916.81 2.049.098.133 1.394 2.132 3.383 2.992.47.205.84.326 1.129.418.475.152.904.129 1.246.08.38-.058 1.171-.48 1.338-.943.164-.464.164-.86.114-.943-.049-.084-.182-.133-.38-.232z"/>
                                            </svg>
                                        </div>
                                        <div class="contact-details">
                                            <h4>WhatsApp</h4>
                                            <p>
                                                <a href="https://wa.me/6285784242462" target="_blank" class="contact-link">
                                                    +62 857 8424 2462
                                                </a>
                                            </p>
                                        </div>
                                    </div>

                                    {{-- Phone --}}
                                    <div class="contact-info-item">
                                        <div class="contact-icon">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 16 16">
                                                <path d="M3.654 1.328a.678.678 0 0 0-1.015-.063L1.605 2.3c-.483.484-.661 1.169-.45 1.77a17.568 17.568 0 0 0 4.168 6.608 17.569 17.569 0 0 0 6.608 4.168c.601.211 1.286.033 1.77-.45l1.034-1.034a.678.678 0 0 0-.063-1.015l-2.307-1.794a.678.678 0 0 0-.58-.122l-2.19.547a1.745 1.745 0 0 1-1.657-.459L5.482 8.062a1.745 1.745 0 0 1-.46-1.657l.548-2.19a.678.678 0 0 0-.122-.58L3.654 1.328zM1.884.511a1.745 1.745 0 0 1 2.612.163L6.29 2.98c.329.423.445.974.315 1.494l-.547 2.19a.678.678 0 0 0 .178.643l2.457 2.457a.678.678 0 0 0 .644.178l2.189-.547a1.745 1.745 0 0 1 1.494.315l2.306 1.794c.829.645.905 1.87.163 2.611l-1.034 1.034c-.74.74-1.846 1.065-2.877.702a18.634 18.634 0 0 1-7.01-4.42 18.634 18.634 0 0 1-4.42-7.009c-.362-1.03-.037-2.137.703-2.877L1.885.511z"/>
                                            </svg>
                                        </div>
                                        <div class="contact-details">
                                            <h4>Telepon</h4>
                                            <p>
                                                <a href="tel:+6285784242462" class="contact-link">
                                                    +62 857 8424 2462
                                                </a>
                                            </p>
                                        </div>
                                    </div>

                                    {{-- Operating Hours --}}
                                    <div class="contact-info-item">
                                        <div class="contact-icon">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 16 16">
                                                <path d="M8 3.5a.5.5 0 0 0-1 0V9a.5.5 0 0 0 .252.434l3.5 2a.5.5 0 0 0 .496-.868L8 8.71V3.5z"/>
                                                <path d="M8 16A8 8 0 1 0 8 0a8 8 0 0 0 0 16zm7-8A7 7 0 1 1 1 8a7 7 0 0 1 14 0z"/>
                                            </svg>
                                        </div>
                                        <div class="contact-details">
                                            <h4>Jam Operasional</h4>
                                            <p>
                                                Senin - Jumat: 08.30 - 15.30 WIB<br>
                                                Sabtu - Minggu & Hari Libur: Tutup
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Right Column - Social Media & CTA --}}
                            <div class="col-lg-6 mb-4" data-aos="fade-left">
                                <div class="contact-cta-box">
                                    <h3>Mari Terhubung!</h3>
                                    <p>Ikuti kami di media sosial untuk informasi terbaru dan penawaran menarik</p>

                                    {{-- Social Media --}}
                                    <div class="contact-social">
                                        <a href="https://www.facebook.com/MTFAutoLoan" target="_blank" class="social-link" aria-label="Facebook">
                                            <i class="fab fa-facebook-f"></i>
                                            <span>Facebook</span>
                                        </a>
                                        <a href="https://www.instagram.com/mtf_jember/" target="_blank" class="social-link" aria-label="Instagram">
                                            <i class="fab fa-instagram"></i>
                                            <span>Instagram</span>
                                        </a>
                                        <a href="https://x.com/mandiritunasfin" target="_blank" class="social-link" aria-label="X">
                                            <i>
                                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 16 16">
                                                    <path d="M12.6.75h2.454l-5.36 6.142L16 15.25h-4.937l-3.867-5.07-4.425 5.07H.316l5.733-6.57L0 .75h5.063l3.495 4.633L12.601.75Zm-.86 13.028h1.36L4.323 2.145H2.865l8.875 11.633Z"/>
                                                </svg>
                                            </i>
                                            <span>X</span>
                                        </a>
                                    </div>

                                    <div class="divider"></div>

                                    {{-- Quick Links --}}
                                    <div class="quick-contact-links">
                                        <h4>Butuh Bantuan?</h4>
                                        <a href="https://wa.me/6285784242462" target="_blank" class="btn btn-whatsapp btn-lg">
                                            <i class="fab fa-whatsapp me-2"></i>
                                            Chat WhatsApp
                                        </a>
                                        <a href="{{ route('landing.index') }}#pengajuan-online" class="btn btn-primary btn-lg">
                                            Ajukan Pembiayaan
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Map Section --}}
    <section class="contact-map-section">
        <div class="container">
            <h2 class="section-title" data-aos="fade-up">Lokasi Kami</h2>
            <p class="section-subtitle" data-aos="fade-up" data-aos-delay="100">
                Kunjungi kantor kami untuk konsultasi langsung
            </p>

            <div class="map-wrapper" data-aos="zoom-in" data-aos-delay="200">
                <iframe
                    src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3949.255457674533!2d113.68280019999999!3d-8.176999999999998!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2dd6943ca2cb57f3%3A0x1e35956e0c6b808!2sMandiri%20Tunas%20Finance%20-%20Jember!5e0!3m2!1sen!2sid!4v1763533904321!5m2!1sen!2sid"
                    width="100%"
                    height="450"
                    style="border:0;"
                    allowfullscreen=""
                    loading="lazy"
                    referrerpolicy="no-referrer-when-downgrade">
                </iframe>
            </div>
        </div>
    </section>

    {{-- Quick Actions --}}
    <section class="contact-quick-actions">
        <div class="container">
            <div class="row">
                <div class="col-lg-4 mb-4" data-aos="fade-up" data-aos-delay="100">
                    <div class="quick-action-card">
                        <div class="quick-action-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" fill="currentColor" viewBox="0 0 16 16">
                                <path d="M13.601 2.326A7.854 7.854 0 0 0 7.994 0C3.627 0 .068 3.558.064 7.926c0 1.399.366 2.76 1.057 3.965L0 16l4.204-1.102a7.933 7.933 0 0 0 3.79.965h.004c4.368 0 7.926-3.558 7.93-7.93A7.898 7.898 0 0 0 13.6 2.326zM7.994 14.521a6.573 6.573 0 0 1-3.356-.92l-.24-.144-2.494.654.666-2.433-.156-.251a6.56 6.56 0 0 1-1.007-3.505c0-3.626 2.957-6.584 6.591-6.584a6.56 6.56 0 0 1 4.66 1.931 6.557 6.557 0 0 1 1.928 4.66c-.004 3.639-2.961 6.592-6.592 6.592zm3.615-4.934c-.197-.099-1.17-.578-1.353-.646-.182-.065-.315-.099-.445.099-.133.197-.513.646-.627.775-.114.133-.232.148-.43.05-.197-.1-.836-.308-1.592-.985-.59-.525-.985-1.175-1.103-1.372-.114-.198-.011-.304.088-.403.087-.088.197-.232.296-.346.1-.114.133-.198.198-.33.065-.134.034-.248-.015-.347-.05-.099-.445-1.076-.612-1.47-.16-.389-.323-.335-.445-.34-.114-.007-.247-.007-.38-.007a.729.729 0 0 0-.529.247c-.182.198-.691.677-.691 1.654 0 .977.71 1.916.81 2.049.098.133 1.394 2.132 3.383 2.992.47.205.84.326 1.129.418.475.152.904.129 1.246.08.38-.058 1.171-.48 1.338-.943.164-.464.164-.86.114-.943-.049-.084-.182-.133-.38-.232z"/>
                            </svg>
                        </div>
                        <h3>Chat WhatsApp</h3>
                        <p>Hubungi kami langsung via WhatsApp untuk respon cepat</p>
                        <a href="https://wa.me/6285784242462" target="_blank" class="btn btn-outline-primary">
                            Mulai Chat
                        </a>
                    </div>
                </div>

                <div class="col-lg-4 mb-4" data-aos="fade-up" data-aos-delay="200">
                    <div class="quick-action-card">
                        <div class="quick-action-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" fill="currentColor" viewBox="0 0 16 16">
                                <path d="M4 1.5H3a2 2 0 0 0-2 2V14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V3.5a2 2 0 0 0-2-2h-1v1h1a1 1 0 0 1 1 1V14a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1V3.5a1 1 0 0 1 1-1h1v-1z"/>
                                <path d="M9.5 1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-3a.5.5 0 0 1-.5-.5v-1a.5.5 0 0 1 .5-.5h3zm-3-1A1.5 1.5 0 0 0 5 1.5v1A1.5 1.5 0 0 0 6.5 4h3A1.5 1.5 0 0 0 11 2.5v-1A1.5 1.5 0 0 0 9.5 0h-3z"/>
                            </svg>
                        </div>
                        <h3>Ajukan Pembiayaan</h3>
                        <p>Mulai pengajuan pembiayaan online dengan mudah</p>
                        <a href="{{ route('landing.index') }}#pengajuan-online" class="btn btn-outline-primary">
                            Ajukan Sekarang
                        </a>
                    </div>
                </div>

                <div class="col-lg-4 mb-4" data-aos="fade-up" data-aos-delay="300">
                    <div class="quick-action-card">
                        <div class="quick-action-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" fill="currentColor" viewBox="0 0 16 16">
                                <path d="M8 16s6-5.686 6-10A6 6 0 0 0 2 6c0 4.314 6 10 6 10zm0-7a3 3 0 1 1 0-6 3 3 0 0 1 0 6z"/>
                            </svg>
                        </div>
                        <h3>Kunjungi Kantor</h3>
                        <p>Datang langsung ke kantor kami untuk konsultasi terkait produk dan layanan</p>
                        <a href="https://maps.google.com/?q=Mandiri+Tunas+Finance+Jember" target="_blank" class="btn btn-outline-primary">
                            Lihat Peta
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
