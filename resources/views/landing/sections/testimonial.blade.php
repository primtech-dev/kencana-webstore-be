<section class="testimonial-section" id="testimonial">
    <div class="container">
        <h2 class="section-title">Apa Kata Mereka</h2>
        <p class="section-subtitle">Kepercayaan dan kepuasan pelanggan adalah prioritas kami</p>

        {{-- Swiper Carousel --}}
        <div class="testimonial-carousel-wrapper">
            <div class="swiper testimonialSwiper">
                <div class="swiper-wrapper">
                    @foreach($testimonials as $testimonial)
                        <div class="swiper-slide">
                            <div class="testimonial-card">
                                <div class="d-flex align-items-center mb-3">
                                    <img src="{{ asset('storage/' . $testimonial->image_path) }}"
                                         alt="{{ $testimonial->name }}"
                                         class="testimonial-photo me-3"
                                         onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode($testimonial->name) }}&background=0047AB&color=fff&size=80'">
                                    <div>
                                        <h5 class="mb-0">{{ $testimonial->name }}</h5>
                                        <small class="text-muted d-block">{{ $testimonial->job }}</small>
                                        <div class="star-rating mt-2">
                                            @for($i = 0; $i < $testimonial->rating; $i++)
                                                ★
                                            @endfor
                                        </div>
                                    </div>
                                </div>
                                <p class="mb-0">{{ $testimonial->comment }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</section>

@push('styles')
    {{-- Swiper CSS --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
@endpush

@push('scripts')
    {{-- Swiper JS --}}
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const testimonialSwiper = new Swiper('.testimonialSwiper', {
                slidesPerView: 1,
                spaceBetween: 30,
                loop: true,

                // Autoplay settings
                autoplay: {
                    delay: 2500,
                    disableOnInteraction: false,
                    pauseOnMouseEnter: true,
                },

                speed: 600,

                // Responsive breakpoints
                breakpoints: {
                    768: {
                        slidesPerView: 2,
                        spaceBetween: 30,
                    },
                    992: {
                        slidesPerView: 3,
                        spaceBetween: 30,
                    },
                },

                // Allow swipe
                grabCursor: true,
                allowTouchMove: true,
            });
        });
    </script>
@endpush
