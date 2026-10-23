@php($light = in_array($section['background'], ['white', 'paper'], true))
<x-site.section :section="$section">
    <div class="container-site">
        <x-site.card-slider :section="$section">
            @foreach ($section['items'] as $testimonial)
                @php($photo = $testimonial->firstAsset('photo'))
                <div class="swiper-slide">
                    <figure @class(['g-card p-6 lg:p-7', $light ? 'glass-light' : 'glass-dark'])>
                        <blockquote class="g-card__text flex-1 text-[15px] leading-[1.9]">
                            <p>{{ $testimonial->body }}</p>
                        </blockquote>
                        <figcaption class="mt-6 flex items-center gap-3">
                            @if ($photo)
                                <x-site.picture :asset="$photo" sizes="48px" class="size-12 rounded-full object-cover" fallback="sm" />
                            @endif
                            <span>
                                <span class="g-card__title block font-bold">{{ $testimonial->name }}</span>
                                @if (filled($testimonial->company))
                                    <span class="g-card__text t-small block">{{ $testimonial->company }}</span>
                                @endif
                            </span>
                        </figcaption>
                    </figure>
                </div>
            @endforeach
        </x-site.card-slider>
    </div>
</x-site.section>
