@props(['section', 'per' => null])
{{-- Swiper card slider (or a plain grid when the section display is "grid"). Slot = `.swiper-slide` items. --}}
@php($grid = ($section['display'] ?? 'slider') === 'grid')
@if ($grid)
    <x-site.section-head :section="$section" />
    <div {{ $attributes->class(['grid gap-5 sm:grid-cols-2 lg:grid-cols-3 lg:gap-6']) }} data-reveal="stagger">
        {{ $slot }}
    </div>
@else
    <div
        x-data="cardSlider({ autoplay: {{ $section['autoplay'] ? 'true' : 'false' }}{{ $per ? ', per: '.Js::from($per) : '' }} })"
        data-slider
        role="region"
        aria-roledescription="carousel"
        @if ($section['title'] !== '') aria-labelledby="sec-{{ $section['id'] }}-title" @endif
    >
        <x-site.section-head :section="$section" :arrows="true" />
        {{ $filters ?? '' }}
        <div class="slider-clip" data-reveal="fade-up">
            <div class="swiper card-slider" x-ref="swiper">
                <div {{ $attributes->class(['swiper-wrapper']) }}>
                    {{ $slot }}
                </div>
            </div>
        </div>
        <div class="card-progress mt-7" aria-hidden="true"><span x-bind:style="`--w: ${progress}%`"></span></div>
    </div>
@endif
