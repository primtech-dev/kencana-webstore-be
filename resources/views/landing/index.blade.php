@extends('landing.layout')

@section('title', 'Fasilitas Dana MTF')

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
          "name": "FAQ",
          "item": "{{ url()->current() }}"
        }
      ]
    },
    {
      "@@type": "WebSite",
      "url": "{{ url('/') }}",
      "name": "Fasilitas Dana MTF",
      "description": "Mandiri Tunas Finance (MTF) adalah perusahaan pembiayaan terkemuka di Indonesia."
    },
    {
      "@@type": "FAQPage",
      "mainEntity": [
        @foreach($faqs as $faq)
            {
              "@@type": "Question",
              "name": {!! json_encode($faq['question']) !!},
          "acceptedAnswer": {
            "@@type": "Answer",
            "text": {!! json_encode($faq['answer']) !!}
            }
          }@if(!$loop->last),@endif
        @endforeach
        ]
      }
    ]
  }
    </script>
@endsection

@section('meta')
    <meta name="description" content="Mandiri Tunas Finance (MTF) adalah perusahaan pembiayaan (leasing) di Indonesia yang menyediakan layanan pembiayaan kendaraan bermotor dan produk keuangan lainnya.">

    <meta property="og:type" content="website">
    <meta property="og:title" content="Fasilitas Dana MTF">
    <meta property="og:description" content="Mandiri Tunas Finance (MTF) adalah perusahaan pembiayaan (leasing) di Indonesia yang menyediakan layanan pembiayaan kendaraan bermotor dan produk keuangan lainnya.">
    <meta property="og:image" content="{{ asset('images/logo-sm.png') }}">
    <meta property="og:url" content="{{ url()->current() }}">
@endsection

@section('content')
    @include('landing.sections.hero', ['hero' => $hero])

    @include('landing.sections.simulation')

    @include('landing.sections.faq', ['faqs' => $faqs])

    @include('landing.sections.testimonial', ['testimonials' => $testimonials])

    @include('landing.sections.application')

    @include('landing.sections.news', ['news' => $news])
@endsection
