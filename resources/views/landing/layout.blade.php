<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Fasilitas Dana MTF')</title>

    @yield('meta')

    <script type="application/ld+json">
        {
          "@@context": "https://schema.org",
          "@@type": "FinancialService",
          "name": "Mandiri Tunas Finance - Jember",
          "url": "{{ url('/') }}",
          "image": "{{ asset('images/logo.png') }}",
          "description": "Mandiri Tunas Finance (MTF) adalah perusahaan pembiayaan terkemuka di Indonesia...",
          "telephone": "+62 8578 4242 462",

          "address": {
            "@@type": "PostalAddress",
            "streetAddress": "Ruko Gajah Mada Square, Jl. Gajah Mada No.187, Kaliwates Kidul, Kaliwates",
            "addressLocality": "Kabupaten Jember",
            "addressRegion": "Jawa Timur",
            "postalCode": "68133",
            "addressCountry": "ID"
          },

          "areaServed": "Jawa Timur",

          "openingHoursSpecification": [
            {
              "@@type": "OpeningHoursSpecification",
              "dayOfWeek": ["Monday","Tuesday","Wednesday","Thursday","Friday"],
              "opens": "08:30",
              "closes": "15:30"
            },
            {
              "@@type": "OpeningHoursSpecification",
              "dayOfWeek": ["Saturday","Sunday"],
              "opens": "00:00",
              "closes": "00:00",
              "description": "Closed"
            }
          ],

          "aggregateRating": {
            "@@type": "AggregateRating",
            "ratingValue": "4.9",
            "bestRating": "5",
            "ratingCount": "185"
          },

          "review": [
            {
              "@@type": "Review",
              "author": { "@@type": "Person", "name": "Tri Fuso" },
              "datePublished": "2024-11-01",
              "reviewBody": "Pelayanan sangat baik dan fast respon.",
              "reviewRating": { "@@type": "Rating", "ratingValue": "5" }
            },
            {
              "@@type": "Review",
              "author": { "@@type": "Person", "name": "Jemy Bagus" },
              "datePublished": "2024-12-01",
              "reviewBody": "Pelayanan memuaskan dan sangat baik.",
              "reviewRating": { "@@type": "Rating", "ratingValue": "5" }
            },
            {
              "@@type": "Review",
              "author": { "@@type": "Person", "name": "Raihan Hidayat" },
              "datePublished": "2024-11-01",
              "reviewBody": "Pelayanan cepat dan ramah.",
              "reviewRating": { "@@type": "Rating", "ratingValue": "5" }
            },
            {
              "@@type": "Review",
              "author": { "@@type": "Person", "name": "Yunia Fara" },
              "datePublished": "2024-07-01",
              "reviewBody": "Pelayanan sangat baik dan fast respon.",
              "reviewRating": { "@@type": "Rating", "ratingValue": "5" }
            },
            {
              "@@type": "Review",
              "author": { "@@type": "Person", "name": "Karina Rena" },
              "datePublished": "2023-12-01",
              "reviewBody": "Pelayanan sangat baik, ramah, cepat.",
              "reviewRating": { "@@type": "Rating", "ratingValue": "5" }
            },
            {
              "@@type": "Review",
              "author": { "@@type": "Person", "name": "Agung Putra Pradana" },
              "datePublished": "2020-01-01",
              "reviewBody": "Pelayanan cepat, pengambilan BPKB mudah.",
              "reviewRating": { "@@type": "Rating", "ratingValue": "5" }
            },
            {
              "@@type": "Review",
              "author": { "@@type": "Person", "name": "Berliana Novitasari" },
              "datePublished": "2023-12-01",
              "reviewBody": "Ruangan bersih, CS ramah, proses cepat.",
              "reviewRating": { "@@type": "Rating", "ratingValue": "5" }
            },
            {
              "@@type": "Review",
              "author": { "@@type": "Person", "name": "Mita Megawati" },
              "datePublished": "2022-11-01",
              "reviewBody": "MTF membantu kebutuhan pembiayaan dengan pelayanan baik.",
              "reviewRating": { "@@type": "Rating", "ratingValue": "5" }
            },
            {
              "@@type": "Review",
              "author": { "@@type": "Person", "name": "R. Hedy UbaiDillah" },
              "datePublished": "2023-10-01",
              "reviewBody": "Kredit bunga rendah, proses cepat, marketing responsif.",
              "reviewRating": { "@@type": "Rating", "ratingValue": "5" }
            },
            {
              "@@type": "Review",
              "author": { "@@type": "Person", "name": "Nimaj Kirana" },
              "datePublished": "2022-11-01",
              "reviewBody": "Proses ambil BPKB mudah dan tidak ribet.",
              "reviewRating": { "@@type": "Rating", "ratingValue": "5" }
            },
            {
              "@@type": "Review",
              "author": { "@@type": "Person", "name": "Yosiindah Kurnia06" },
              "datePublished": "2021-08-01",
              "reviewBody": "MTF Jember siap melayani kredit dan dana tunai dengan baik.",
              "reviewRating": { "@@type": "Rating", "ratingValue": "5" }
            },
            {
              "@@type": "Review",
              "author": { "@@type": "Person", "name": "Ragil Bayu" },
              "datePublished": "2022-12-01",
              "reviewBody": "Pelayanan baik, CS ramah, solusi tepat.",
              "reviewRating": { "@@type": "Rating", "ratingValue": "5" }
            },
            {
              "@@type": "Review",
              "author": { "@@type": "Person", "name": "Nafira Ainur" },
              "datePublished": "2023-10-01",
              "reviewBody": "Pelayanan sangat baik dan staff ramah.",
              "reviewRating": { "@@type": "Rating", "ratingValue": "5" }
            },
            {
              "@@type": "Review",
              "author": { "@@type": "Person", "name": "Mega Wulandari" },
              "datePublished": "2022-03-01",
              "reviewBody": "Staf ramah, pelayanan cepat dan memuaskan.",
              "reviewRating": { "@@type": "Rating", "ratingValue": "5" }
            },
            {
              "@@type": "Review",
              "author": { "@@type": "Person", "name": "P_Dian_A UNG" },
              "datePublished": "2022-02-01",
              "reviewBody": "Pelayanan cepat, staf sangat ramah dan sabar.",
              "reviewRating": { "@@type": "Rating", "ratingValue": "5" }
            },
            {
              "@@type": "Review",
              "author": { "@@type": "Person", "name": "Fariz Streber" },
              "datePublished": "2022-01-01",
              "reviewBody": "Pelayanan memuaskan dan tempat nyaman.",
              "reviewRating": { "@@type": "Rating", "ratingValue": "5" }
            },
            {
              "@@type": "Review",
              "author": { "@@type": "Person", "name": "Adi Lukito" },
              "datePublished": "2022-01-01",
              "reviewBody": "Pelayanan ramah, pencairan dana cepat.",
              "reviewRating": { "@@type": "Rating", "ratingValue": "5" }
            },
            {
              "@@type": "Review",
              "author": { "@@type": "Person", "name": "Huda Ifanamkatu" },
              "datePublished": "2022-01-01",
              "reviewBody": "Pelayanan CS sangat memuaskan dan ramah.",
              "reviewRating": { "@@type": "Rating", "ratingValue": "5" }
            },
            {
              "@@type": "Review",
              "author": { "@@type": "Person", "name": "Anggi Furoida" },
              "datePublished": "2022-01-01",
              "reviewBody": "Proses mudah dan tidak ribet.",
              "reviewRating": { "@@type": "Rating", "ratingValue": "5" }
            }
          ]
        }
    </script>

    @yield('jsonld')

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    {{-- Vite Assets --}}
    @vite(['resources/scss/frontend.scss', 'resources/js/frontend.js'])

    {{-- Bootstrap CSS --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">

    {{-- Additional Styles --}}
    @stack('styles')
</head>
<body>
{{-- Navbar Component --}}
@include('components.frontend.navbar')

{{-- Main Content --}}
<main id="main-content">
    @yield('content')
</main>

@include('landing.sections.footer')

{{-- Bootstrap JS --}}
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

{{-- Additional Scripts --}}
@stack('scripts')
</body>
</html>
