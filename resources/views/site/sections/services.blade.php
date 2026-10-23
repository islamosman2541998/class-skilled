<x-site.section :section="$section">
    <div class="container-site">
        <x-site.card-slider :section="$section">
            @foreach ($section['items'] as $service)
                @php($cover = $service->firstAsset('cover'))
                @php($url = $service->localizedUrl())
                <div class="swiper-slide" style="--i: {{ $loop->index }}">
                    <article @class(['g-card', $section['background'] === 'white' || $section['background'] === 'paper' ? 'glass-light' : 'glass-dark'])>
                        @if ($cover)
                            <div class="g-card__media aspect-[4/3]">
                                <x-site.picture :asset="$cover" sizes="(min-width: 1024px) 30vw, (min-width: 640px) 45vw, 85vw" />
                            </div>
                        @endif
                        <div @class(['g-card__body', 'justify-end min-h-48' => ! $cover])>
                            <h3 class="t-card g-card__title">
                                @if ($url)
                                    <a href="{{ $url }}" class="stretched">{{ $service->title }}</a>
                                @else
                                    {{ $service->title }}
                                @endif
                            </h3>
                            @if ($cover && filled($service->excerpt))
                                <p class="g-card__text clamp-2 text-[14.5px]">{{ $service->excerpt }}</p>
                            @endif
                            @if ($url)
                                <span class="g-card__title mt-auto pt-2 text-[14px] font-bold underline decoration-sky decoration-2 underline-offset-[6px]" aria-hidden="true">{{ __('site.details') }}</span>
                            @endif
                        </div>
                    </article>
                </div>
            @endforeach
        </x-site.card-slider>
    </div>
</x-site.section>
