{{-- One video: uploaded file in the custom player, or a lite YouTube/Vimeo embed (iframe after click). --}}
@php($poster = \App\Support\Lightbox::poster($video))
@if ($video->kind === \App\Enums\AssetKind::Embed && $video->embedUrl())
    <div x-data="liteEmbed(@js($video->embedUrl()))" class="relative aspect-video overflow-hidden rounded-[14px] bg-deep">
        <template x-if="playing">
            <iframe :src="src" class="absolute inset-0 size-full" allow="autoplay; encrypted-media; picture-in-picture; fullscreen" allowfullscreen title="{{ $video->titleText() }}"></iframe>
        </template>
        <button type="button" x-show="!playing" @click="playing = true" class="group absolute inset-0 grid place-items-center" aria-label="{{ __('site.play_video') }}: {{ $video->titleText() }}">
            @if ($poster)
                <img src="{{ $poster }}" alt="" class="absolute inset-0 size-full object-cover opacity-80" loading="lazy" decoding="async">
            @endif
            <span class="relative grid size-16 place-items-center rounded-full bg-sky text-deep shadow-lg transition group-hover:scale-105">
                <x-heroicon-s-play class="size-7 rtl:-scale-x-100" aria-hidden="true" />
            </span>
        </button>
    </div>
@elseif ($video->kind === \App\Enums\AssetKind::Video && $video->url())
    <x-site.video :video="$video" class="aspect-video w-full rounded-[14px]" />
@endif
