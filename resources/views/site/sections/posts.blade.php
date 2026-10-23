@php($light = in_array($section['background'], ['white', 'paper'], true))
<x-site.section :section="$section">
    <div class="container-site">
        <x-site.card-slider :section="$section">
            @foreach ($section['items'] as $post)
                @php($cover = $post->firstAsset('cover'))
                @php($url = $post->localizedUrl())
                <div class="swiper-slide" style="--i: {{ $loop->index }}">
                    <article @class(['g-card', $light ? 'glass-light' : 'glass-dark'])>
                        @if ($cover)
                            <div class="g-card__media aspect-[16/10]">
                                <x-site.picture :asset="$cover" sizes="(min-width: 1024px) 30vw, (min-width: 640px) 45vw, 85vw" />
                            </div>
                        @endif
                        <div class="g-card__body">
                            @if ($post->published_at)
                                <time datetime="{{ $post->published_at->toDateString() }}" class="g-card__text t-small">{{ $post->published_at->locale(app()->getLocale())->translatedFormat('j F Y') }}</time>
                            @endif
                            <h3 class="t-card g-card__title clamp-2">
                                @if ($url)
                                    <a href="{{ $url }}" class="stretched">{{ $post->title }}</a>
                                @else
                                    {{ $post->title }}
                                @endif
                            </h3>
                        </div>
                    </article>
                </div>
            @endforeach
        </x-site.card-slider>
    </div>
</x-site.section>
