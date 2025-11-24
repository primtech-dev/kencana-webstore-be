{{-- Hero Section --}}
<section class="hero-section" id="beranda">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <h1 class="hero-title">{{ $hero['title'] }}</h1>
                <h2 class="hero-subtitle">{{ $hero['subtitle'] }}</h2>
                <p class="lead">{{ $hero['description'] }}</p>
            </div>
            <div class="col-lg-6">
                <img src="{{ asset('images/hero.png') }}"
                     alt="hero-image"
                     class="img-fluid rounded">
            </div>
        </div>
    </div>
</section>
