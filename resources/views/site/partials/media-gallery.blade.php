{{-- Media gallery: a stage slider (images, uploaded videos with the custom player, YouTube/Vimeo) + a thumbnail
     strip whose active thumb fills with the autoplay timer. Autoplay (held while a video plays), arrows,
     swipe/drag, keyboard; an image opens the lightbox. $items (assets), $label (region name). --}}
@php
    $items = \App\Support\Lightbox::showable(collect($items));
    $count = $items->count();
@endphp
@if ($count > 0)
    <div x-data="lightbox">
        <div x-data="mediaGallery({ delay: 6000 })" class="media-gallery" role="region" aria-roledescription="carousel" aria-label="{{ $label }}">
            <div class="mg-stage on-dark">
                <div class="swiper" x-ref="swiper">
                    <div class="swiper-wrapper">
                        @foreach ($items as $i => $item)
                            <div @class(['swiper-slide', 'is-image' => $item->isImage()])>
                                @if ($item->isImage())
                                    <button type="button" class="mg-image" @click="open({{ $i }})" aria-haspopup="dialog" aria-label="{{ __('media.view_full') }}: {{ $item->altText() }}">
                                        <x-site.picture :asset="$item" sizes="(min-width: 1024px) 800px, 100vw" :eager="$i === 0" fallback="xl" class="size-full object-cover" />
                                        <span class="mg-expand glass-btn" aria-hidden="true"><x-heroicon-o-arrows-pointing-out class="size-5" /></span>
                                    </button>
                                    @if (filled($item->captionText()))
                                        <p class="mg-caption">{{ $item->captionText() }}</p>
                                    @endif
                                @elseif ($item->kind === \App\Enums\AssetKind::Video)
                                    <x-site.video :video="$item" class="size-full" />
                                @else
                                    @php($poster = \App\Support\Lightbox::poster($item))
                                    <div x-data="liteEmbed(@js($item->embedUrl()))" data-media @media-stop="playing = false" class="vp">
                                        <template x-if="playing">
                                            <iframe :src="src" class="absolute inset-0 size-full" allow="autoplay; encrypted-media; picture-in-picture; fullscreen" allowfullscreen title="{{ $item->titleText() }}"></iframe>
                                        </template>
                                        <button type="button" class="vp__start" x-show="!playing" @click="playing = true; $dispatch('media-play')" aria-label="{{ __('media.play') }}: {{ $item->titleText() }}">
                                            @if ($poster)
                                                <img src="{{ $poster }}" alt="" class="absolute inset-0 size-full object-cover" loading="lazy" decoding="async">
                                            @endif
                                            <span class="vp__start-icon" aria-hidden="true"><x-heroicon-s-play class="size-7" /></span>
                                        </button>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>

                @if ($count > 1)
                    <button type="button" class="glass-btn mg-arrow mg-arrow--prev" x-ref="prev" aria-label="{{ __('slider.previous') }}"><x-heroicon-m-arrow-left class="flip size-5" aria-hidden="true" /></button>
                    <button type="button" class="glass-btn mg-arrow mg-arrow--next" x-ref="next" aria-label="{{ __('slider.next') }}"><x-heroicon-m-arrow-right class="flip size-5" aria-hidden="true" /></button>
                    <div class="mg-top">
                        <span class="mg-counter" aria-hidden="true"><bdi dir="ltr"><span x-text="active + 1">1</span> / {{ $count }}</bdi></span>
                        <button type="button" class="glass-btn mg-toggle" x-show="swiper?.autoplay?.enabled !== false" @click="toggle()" :aria-label="playing ? @js(__('slider.pause')) : @js(__('slider.play'))" :aria-pressed="(!playing).toString()">
                            <x-heroicon-s-pause class="size-4" x-show="playing" aria-hidden="true" />
                            <x-heroicon-s-play class="size-4" x-show="!playing" aria-hidden="true" />
                        </button>
                    </div>
                @endif
            </div>

            @if ($count > 1)
                <div class="swiper mg-thumbs" x-ref="thumbs" aria-hidden="true">
                    <div class="swiper-wrapper">
                        @foreach ($items as $i => $item)
                            <div class="swiper-slide">
                                @if ($item->isImage())
                                    <x-site.picture :asset="$item" sizes="128px" fallback="sm" class="mg-thumb__img" />
                                @else
                                    @php($poster = \App\Support\Lightbox::poster($item))
                                    <span class="mg-thumb__img block bg-deep">
                                        @if ($poster)
                                            <img src="{{ $poster }}" alt="" class="size-full object-cover" loading="lazy" decoding="async">
                                        @endif
                                    </span>
                                    <span class="mg-thumb__video" aria-hidden="true">
                                        <x-heroicon-s-play class="size-3" />
                                        @if ($item->duration)
                                            <bdi dir="ltr">{{ \App\Support\Lightbox::duration($item->duration) }}</bdi>
                                        @endif
                                    </span>
                                @endif
                                <span class="mg-thumb__timer" aria-hidden="true"></span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
        @include('site.partials.lightbox', ['items' => \App\Support\Lightbox::items($items)])
    </div>
@endif
