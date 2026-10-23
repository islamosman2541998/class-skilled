{{-- Grid of video posters with a play overlay; opens the lightbox player. --}}
@php($videos = \App\Support\Lightbox::playable(collect($videos)))
@if ($videos->isNotEmpty())
    <div x-data="lightbox">
        <ul class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($videos as $i => $video)
                @php($poster = \App\Support\Lightbox::poster($video))
                <li>
                    <button type="button" class="group relative block aspect-video w-full overflow-hidden rounded-[14px] bg-deep" @click="open({{ $i }})" aria-haspopup="dialog" aria-label="{{ __('site.play_video') }}: {{ $video->titleText() }}">
                        @if ($poster)
                            <img src="{{ $poster }}" alt="" class="absolute inset-0 size-full object-cover opacity-85 transition duration-300 group-hover:scale-[1.04]" loading="lazy" decoding="async">
                        @endif
                        <span class="absolute inset-0 grid place-items-center">
                            <span class="grid size-14 place-items-center rounded-full bg-sky text-deep shadow-lg"><x-heroicon-s-play class="size-6 rtl:-scale-x-100" aria-hidden="true" /></span>
                        </span>
                    </button>
                    <p class="t-small mt-2 font-semibold text-ink">{{ $video->titleText() }}</p>
                </li>
            @endforeach
        </ul>
        @include('site.partials.lightbox', ['items' => \App\Support\Lightbox::items($videos)])
    </div>
@endif
