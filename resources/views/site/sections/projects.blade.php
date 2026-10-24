@php
    $light = in_array($section['background'], ['white', 'paper'], true);
    $categories = $section['show_filters'] ? $section['items']->pluck('category')->filter()->unique('id')->values() : collect();
@endphp
<x-site.section :section="$section">
    <div class="container-site">
        <x-site.card-slider :section="$section">
            @foreach ($section['items'] as $project)
                @php($cover = $project->firstAsset('cover'))
                @php($url = $project->localizedUrl())
                @php($meta = collect([$project->category?->name, $project->location])->filter()->implode(app()->getLocale() === 'ar' ? "\u{060C} " : ', '))
                <div class="swiper-slide" data-filter-key="{{ $project->category_id ?? 0 }}" style="--i: {{ $loop->index }}">
                    <article @class(['g-card', $light ? 'glass-light' : 'glass-dark'])>
                        @if ($cover)
                            <div class="g-card__media aspect-[4/3]">
                                <x-site.picture :asset="$cover" sizes="(min-width: 1024px) 30vw, (min-width: 640px) 45vw, 85vw" />
                            </div>
                        @endif
                        <div @class(['g-card__body', 'justify-end min-h-48' => ! $cover])>
                            <h3 class="t-card g-card__title">
                                @if ($url)
                                    <a href="{{ $url }}" class="stretched">{{ $project->title }}</a>
                                @else
                                    {{ $project->title }}
                                @endif
                            </h3>
                            @if ($meta !== '')
                                <p class="g-card__text t-small">{{ $meta }}</p>
                            @endif
                        </div>
                    </article>
                </div>
            @endforeach

            @if ($categories->count() > 1)
                <x-slot:filters>
                    <div class="-mt-2 mb-6 flex flex-wrap gap-2" role="group" aria-label="{{ __('projects.category') }}" data-reveal="fade">
                        <button type="button" class="chip shrink-0" :aria-pressed="(filterKey === null).toString()" aria-pressed="true" @click="filter(null)">{{ __('projects.all_categories') }}</button>
                        @foreach ($categories as $category)
                            <button type="button" class="chip shrink-0" :aria-pressed="(filterKey === '{{ $category->id }}').toString()" aria-pressed="false" @click="filter('{{ $category->id }}')">{{ $category->name }}</button>
                        @endforeach
                    </div>
                </x-slot:filters>
            @endif
        </x-site.card-slider>
    </div>
</x-site.section>
