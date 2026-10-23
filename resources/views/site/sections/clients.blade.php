@php($light = in_array($section['background'], ['white', 'paper'], true))
<x-site.section :section="$section">
    <div class="container-site">
        <x-site.card-slider :section="$section" :per="[2.3, 3.3, 5, 6]">
            @foreach ($section['items'] as $client)
                @php($logo = $client->firstAsset('logo'))
                <div class="swiper-slide">
                    <div @class(['client-logo g-card grid h-28 place-items-center p-5', $light ? 'glass-light' : 'glass-dark'])>
                        @if ($logo)
                            <x-site.picture :asset="$logo" sizes="160px" :alt="$client->name" class="max-h-14 w-auto object-contain" fallback="sm" />
                        @else
                            <span class="g-card__title text-center font-bold">{{ $client->name }}</span>
                        @endif
                        @if ($client->url)
                            <a href="{{ $client->url }}" target="_blank" rel="noopener" class="stretched"><span class="sr-only">{{ $client->name }}</span></a>
                        @endif
                    </div>
                </div>
            @endforeach
        </x-site.card-slider>
    </div>
</x-site.section>
