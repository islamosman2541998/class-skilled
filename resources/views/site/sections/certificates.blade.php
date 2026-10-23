@php($light = in_array($section['background'], ['white', 'paper'], true))
<x-site.section :section="$section">
    <div class="container-site">
        <x-site.card-slider :section="$section">
            @foreach ($section['items'] as $certificate)
                @php($image = $certificate->firstAsset('image'))
                @php($file = $certificate->firstAsset('file'))
                <div class="swiper-slide">
                    <article @class(['g-card', $light ? 'glass-light' : 'glass-dark'])>
                        @if ($image)
                            <div class="g-card__media aspect-[4/3] bg-white">
                                <x-site.picture :asset="$image" sizes="(min-width: 1024px) 30vw, 85vw" class="!object-contain" />
                            </div>
                        @endif
                        <div class="g-card__body">
                            <h3 class="t-card g-card__title">{{ $certificate->name }}</h3>
                            @if (filled($certificate->authority))
                                <p class="g-card__text t-small">{{ $certificate->authority }}</p>
                            @endif
                            @if ($certificate->number)
                                <p class="g-card__text t-small"><bdi dir="ltr">{{ $certificate->number }}</bdi></p>
                            @endif
                            @if ($file?->url() || $certificate->verify_url)
                                <a href="{{ $file?->url() ?? $certificate->verify_url }}" target="_blank" rel="noopener" class="g-card__title stretched mt-auto pt-2 text-[14px] font-bold underline decoration-sky decoration-2 underline-offset-[6px]">{{ $file ? __('site.open') : __('site.details') }}</a>
                            @endif
                        </div>
                    </article>
                </div>
            @endforeach
        </x-site.card-slider>
    </div>
</x-site.section>
