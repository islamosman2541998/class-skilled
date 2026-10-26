{{-- Uploaded video with the custom player (resources/js/site/video-player.js): <x-site.video :video="$asset" class="aspect-video" /> --}}
@props(['video'])
@php
    $poster = \App\Support\Lightbox::poster($video);
    $title = $video->titleText();
@endphp
<div
    x-data="videoPlayer"
    data-media
    {{ $attributes->class(['vp']) }}
    :class="{ 'is-started': started, 'is-playing': playing, 'is-idle': idle }"
    @media-stop="stop()"
    @pointermove="wake()"
    @keydown="key($event)"
>
    <video x-ref="video" class="vp__video" playsinline preload="none" @if ($poster) poster="{{ $poster }}" @endif data-duration="{{ $video->duration }}" @click="surface($event)" aria-label="{{ $title }}">
        <source src="{{ $video->url() }}" type="{{ $video->mime }}">
    </video>

    <button type="button" class="vp__start" x-show="!started" @click="toggle()" aria-label="{{ __('media.play') }}: {{ $title }}">
        <span class="vp__start-icon" aria-hidden="true"><x-heroicon-s-play class="size-7" /></span>
        @if ($video->duration)
            <span class="vp__start-time" aria-hidden="true"><bdi dir="ltr">{{ \App\Support\Lightbox::duration($video->duration) }}</bdi></span>
        @endif
    </button>

    <span class="vp__spinner" x-show="waiting && playing" x-cloak aria-hidden="true"></span>

    {{-- Media controls keep the left-to-right timeline in both languages. --}}
    <div class="vp__controls swiper-no-swiping" dir="ltr" x-show="started" x-cloak>
        <button type="button" class="vp__btn" @click="toggle()" :aria-label="playing ? @js(__('media.pause')) : @js(__('media.play'))">
            <x-heroicon-s-pause class="size-5" x-show="playing" aria-hidden="true" />
            <x-heroicon-s-play class="size-5" x-show="!playing" aria-hidden="true" />
        </button>
        <span class="vp__time" x-text="time(current)">0:00</span>
        <div class="vp__track" :style="`--buffered: ${buffered * 100}%; --played: ${percent}%`">
            <input type="range" class="vp__range" min="0" max="1000" step="1" :value="Math.round(percent * 10)" @input="seek($event.target.value)" aria-label="{{ __('media.seek') }}" :aria-valuetext="time(current) + ' / ' + time(duration)">
        </div>
        <span class="vp__time" x-text="time(duration)">0:00</span>
        <button type="button" class="vp__btn" @click="toggleMute()" :aria-label="muted ? @js(__('media.unmute')) : @js(__('media.mute'))" :aria-pressed="muted.toString()">
            <x-heroicon-s-speaker-x-mark class="size-5" x-show="muted" aria-hidden="true" />
            <x-heroicon-s-speaker-wave class="size-5" x-show="!muted" aria-hidden="true" />
        </button>
        <input type="range" class="vp__volume" min="0" max="1" step="0.05" :value="muted ? 0 : volume" @input="setVolume($event.target.value)" aria-label="{{ __('media.volume') }}" :style="`--played: ${(muted ? 0 : volume) * 100}%`">
        <button type="button" class="vp__btn" @click="toggleFullscreen()" :aria-label="fullscreen ? @js(__('media.exit_fullscreen')) : @js(__('media.fullscreen'))">
            <x-heroicon-o-arrows-pointing-in class="size-5" x-show="fullscreen" aria-hidden="true" />
            <x-heroicon-o-arrows-pointing-out class="size-5" x-show="!fullscreen" aria-hidden="true" />
        </button>
    </div>
</div>
