@php
    $slides = $section['slides'];
    $many = count($slides) > 1;
    $isTop = $first ?? false;
    $lead = $slides[0];
@endphp
@if ($lead['image'] && $lead['image']->file())
    @push('preload')
        <link rel="preload" as="image" type="image/webp" imagesrcset="{{ (new \App\View\Components\Site\Picture($lead['image']))->srcset($lead['image']) }}" imagesizes="100vw" fetchpriority="high" @if ($lead['mobile_image']) media="(min-width: 640px)" @endif>
    @endpush
@endif
<section
    class="hero on-dark"
    aria-roledescription="carousel"
    aria-label="{{ $section['title'] !== '' ? $section['title'] : __('slider.region') }}"
    @if ($many)
        x-data="heroSlider({ delay: {{ $section['autoplay_seconds'] * 1000 }} })"
        data-slider
    @endif
    data-section="hero"
>
    <div class="swiper h-full" x-ref="swiper">
        <div class="swiper-wrapper">
            @foreach ($slides as $i => $slide)
                <div @class(['swiper-slide hero-slide', 'is-active' => $i === 0]) style="--overlay: {{ $slide['overlay'] }}" role="group" aria-roledescription="slide" aria-label="{{ \App\Support\Translate::swiper(__('slider.slide_of'), ['index' => $i + 1, 'slidesLength' => count($slides)]) }}">
                    <div class="hero-slide__media">
                        @if ($slide['video'] && $slide['video']->file())
                            <video autoplay muted loop playsinline preload="{{ $i === 0 ? 'auto' : 'none' }}" @if ($slide['image']) poster="{{ $slide['image']->url('xl') }}" @endif aria-hidden="true">
                                <source src="{{ $slide['video']->url() }}" type="{{ $slide['video']->mime }}">
                            </video>
                        @elseif ($slide['image'])
                            <x-site.picture :asset="$slide['image']" :mobile="$slide['mobile_image']" sizes="100vw" :eager="$i === 0" fallback="xl" />
                        @endif
                    </div>
                    <div class="hero-slide__overlay"></div>

                    <div class="container-site relative z-[2] flex h-full items-end pb-36 lg:items-center lg:pt-24 lg:pb-16">
                        @if ($slide['title'] !== '' || $slide['text'] !== '')
                            <div class="hero-panel glass-dark">
                                @if ($slide['caption'] !== '')
                                    <p class="hero-line mb-3 inline-flex rounded-full bg-white/12 px-3 py-1 text-[13px] font-semibold text-white ring-1 ring-white/20" style="--i: 0">{{ $slide['caption'] }}</p>
                                @endif
                                @if ($slide['title'] !== '')
                                    @if ($i === 0 && $isTop)
                                        <h1 class="hero-line t-hero text-white" style="--i: 1">{{ $slide['title'] }}</h1>
                                    @else
                                        <h2 class="hero-line t-hero text-white" style="--i: 1">{{ $slide['title'] }}</h2>
                                    @endif
                                @endif
                                @if ($slide['text'] !== '')
                                    <p class="hero-line t-lead mt-3" style="--i: 2">{{ $slide['text'] }}</p>
                                @endif
                                @if ($slide['primary'] || $slide['secondary'])
                                    <div class="hero-line mt-6 flex flex-wrap gap-3" style="--i: 3">
                                        @if ($slide['primary'])
                                            <a href="{{ $slide['primary']['url'] }}" class="btn btn-primary" @if ($slide['primary']['target_blank']) target="_blank" rel="noopener" @endif @if ($i > 0) tabindex="-1" data-slide-focus @endif>{{ $slide['primary']['label'] }}</a>
                                        @endif
                                        @if ($slide['secondary'])
                                            <a href="{{ $slide['secondary']['url'] }}" class="btn btn-ghost-light" @if ($slide['secondary']['target_blank'] || str_starts_with($slide['secondary']['url'], 'https://wa.me')) target="_blank" rel="noopener" @endif @if ($i > 0) tabindex="-1" data-slide-focus @endif>{{ $slide['secondary']['label'] }}</a>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    @if ($many)
        <div class="hero-controls">
            <div class="container-site flex items-center justify-between gap-4">
                @if ($section['show_progress'])
                    <div class="progress-bars" role="tablist" aria-label="{{ __('slider.region') }}">
                        @foreach ($slides as $i => $slide)
                            <button type="button" role="tab" class="bar" :class="{ 'is-active': active === {{ $i }}, 'is-done': active > {{ $i }} }" @click="go({{ $i }})" :aria-selected="(active === {{ $i }}).toString()" aria-label="{{ \App\Support\Translate::swiper(__('slider.go_to'), ['index' => $i + 1]) }}" @if ($i === 0) style="--progress: 0" @endif></button>
                        @endforeach
                    </div>
                @else
                    <span></span>
                @endif
                <div class="flex items-center gap-2">
                    <button type="button" class="glass-btn" @click="toggle()" :aria-label="playing ? @js(__('slider.pause')) : @js(__('slider.play'))" aria-label="{{ __('slider.pause') }}">
                        <x-heroicon-m-pause class="size-5" x-show="playing" aria-hidden="true" />
                        <x-heroicon-m-play class="size-5" x-show="!playing" x-cloak aria-hidden="true" />
                    </button>
                    @if ($section['show_arrows'])
                        <button type="button" class="glass-btn" x-ref="prev" aria-label="{{ __('slider.previous') }}"><x-heroicon-m-arrow-left class="flip size-5" aria-hidden="true" /></button>
                        <button type="button" class="glass-btn" x-ref="next" aria-label="{{ __('slider.next') }}"><x-heroicon-m-arrow-right class="flip size-5" aria-hidden="true" /></button>
                    @endif
                </div>
            </div>
        </div>
    @endif
</section>
