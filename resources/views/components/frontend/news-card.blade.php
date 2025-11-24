@props([
    'item',
    'size' => 'md', // lg, md, sm
    'showCategory' => true,
    'showExcerpt' => true,
    'showDate' => true,
])

@php
    $sizeClasses = [
        'lg' => [
            'card' => 'news-card news-card-lg',
            'image_height' => '300px',
            'title_clamp' => '2',
            'excerpt_clamp' => '4',
        ],
        'md' => [
            'card' => 'news-card news-card-md',
            'image_height' => '200px',
            'title_clamp' => '2',
            'excerpt_clamp' => '3',
        ],
        'sm' => [
            'card' => 'news-card news-card-sm',
            'image_height' => '150px',
            'title_clamp' => '2',
            'excerpt_clamp' => '2',
        ],
    ];

    $config = $sizeClasses[$size] ?? $sizeClasses['md'];
@endphp

<div class="card {{ $config['card'] }}" {{ $attributes }}>
    {{-- Image --}}
    <div class="news-card-image-wrapper" style="height: {{ $config['image_height'] }}">
        @if(isset($item['image']) && $item['image'])
            <img src="{{ $item['image'] }}"
                 class="card-img-top"
                 alt="{{ $item['title'] }}">
        @else
            <img src="{{ asset('storage/' . $item['image_path']) }}"
                 class="card-img-top"
                 alt="{{ $item['image_alt_text'] }}">
        @endif
    </div>

    {{-- Body --}}
    <div class="card-body">
{{--        @if($showCategory && isset($item['category']))--}}
{{--            <span class="news-category">{{ $item['category'] }}</span>--}}
{{--        @endif--}}

        <h5 class="card-title mt-2" style="-webkit-line-clamp: {{ $config['title_clamp'] }}">
            {{ $item['title'] }}
        </h5>

        @if($showExcerpt && isset($item['excerpt']))
            <p class="card-text text-muted" style="-webkit-line-clamp: {{ $config['excerpt_clamp'] }}">
                {{ $item['excerpt'] }}
            </p>
        @endif

        <div class="news-card-footer d-flex justify-content-between align-items-center">
            @if($showDate && isset($item['created_at']))
                <small class="text-muted">
                    {{ \Carbon\Carbon::parse($item['created_at'])->translatedFormat('l ,d F Y') }}
                </small>
            @endif

            <a href="{{ route('news.show', $item['seo_url']) }}" class="btn btn-sm btn-primary">
                Baca Selengkapnya
            </a>
        </div>
    </div>
</div>
