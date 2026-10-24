@extends('layouts.site')

@php
    $locale = app()->getLocale();
    $scope = \App\Support\Translate::list($project->scope);
    $facts = array_filter([
        __('projects.service') => $project->services->map(fn ($s) => $s->title)->filter()->implode($locale === 'ar' ? "\u{060C} " : ', '),
        __('projects.category') => $project->category?->name,
        __('projects.location') => $project->location,
        __('projects.area') => filled($project->area_m2) ? __('projects.area_value', ['value' => str_contains((string) $project->area_m2, '.') ? rtrim(rtrim((string) $project->area_m2, '0'), '.') : $project->area_m2]) : null,
        __('projects.duration') => $project->duration,
        __('projects.status') => __('enums.project_status.'.$project->project_status->value),
        __('projects.year') => $project->year,
        __('projects.client') => $project->show_client ? $project->client_name : null,
    ], 'filled');
    $images = $gallery->filter(fn ($asset) => $asset->isImage())->values();
    $firstService = $project->services->first();
    $crumbs = [__('projects.title') => \App\Support\LocalizedRoute::url('projects.index')];
    if ($project->category && $project->category->localizedUrl()) {
        $crumbs[$project->category->name] = $project->category->localizedUrl();
    }
    $crumbs[$project->title] = null;
@endphp

@section('content')
    @include('site.partials.page-header', ['title' => $project->title, 'lead' => $project->excerpt, 'crumbs' => $crumbs])

    <div class="section !pt-10">
        <div class="container-site grid gap-10 lg:grid-cols-[minmax(0,1fr)_340px] lg:gap-14">
            <div class="min-w-0 space-y-12">
                @if ($images->isNotEmpty())
                    <div x-data="lightbox">
                        {{-- Gallery slider: arrows, swipe/drag, keyboard, autoplay; a click opens the lightbox. --}}
                        <div x-data="gallerySlider({ delay: 5000 })" class="space-y-3" role="region" aria-roledescription="carousel" aria-label="{{ __('projects.gallery') }}">
                            <div class="on-dark relative overflow-hidden rounded-[14px] bg-deep">
                                <div class="swiper gallery-main" x-ref="swiper">
                                    <div class="swiper-wrapper">
                                        @foreach ($images as $i => $image)
                                            <div class="swiper-slide">
                                                <button type="button" class="block w-full cursor-zoom-in" @click="open({{ $i }})" aria-haspopup="dialog" aria-label="{{ __('lightbox.label') }}: {{ $image->altText() }}">
                                                    <x-site.picture :asset="$image" sizes="(min-width: 1024px) 800px, 100vw" :eager="$i === 0" class="aspect-[16/10] w-full object-cover" />
                                                </button>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                                @if ($images->count() > 1)
                                    <button type="button" class="glass-btn absolute start-3 top-1/2 z-10 -translate-y-1/2 sm:start-4" x-ref="prev" aria-label="{{ __('slider.previous') }}"><x-heroicon-m-arrow-left class="flip size-5" aria-hidden="true" /></button>
                                    <button type="button" class="glass-btn absolute end-3 top-1/2 z-10 -translate-y-1/2 sm:end-4" x-ref="next" aria-label="{{ __('slider.next') }}"><x-heroicon-m-arrow-right class="flip size-5" aria-hidden="true" /></button>
                                    <p class="pointer-events-none absolute bottom-3 end-3 z-10 rounded-full bg-deep/70 px-3 py-1 text-[13px] font-semibold text-white sm:bottom-4 sm:end-4" aria-hidden="true"><bdi dir="ltr"><span x-text="active + 1">1</span> / {{ $images->count() }}</bdi></p>
                                @endif
                            </div>
                            @if ($images->count() > 1)
                                <div class="swiper gallery-thumbs" x-ref="thumbs">
                                    <div class="swiper-wrapper">
                                        @foreach ($images as $i => $image)
                                            <div class="swiper-slide">
                                                <x-site.picture :asset="$image" sizes="120px" fallback="sm" class="h-16 w-24 object-cover lg:h-20 lg:w-28" />
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                        @include('site.partials.lightbox', ['items' => \App\Support\Lightbox::items($images)])
                    </div>
                @endif

                @if ($blocks !== [])
                    <section aria-labelledby="about-project">
                        <h2 id="about-project" class="t-section mb-6">{{ __('projects.description') }}</h2>
                        @include('site.partials.blocks', ['blocks' => $blocks])
                    </section>
                @endif

                @if ($scope !== [])
                    <section aria-labelledby="scope-title">
                        <h2 id="scope-title" class="t-section">{{ __('projects.scope') }}</h2>
                        <ul class="mt-5 grid gap-3 sm:grid-cols-2">
                            @foreach ($scope as $line)
                                <li class="flex gap-3 rounded-[12px] border border-line bg-white p-4"><span class="mt-2.5 size-1.5 shrink-0 rounded-full bg-sky" aria-hidden="true"></span><span>{{ $line }}</span></li>
                            @endforeach
                        </ul>
                    </section>
                @endif

                @if ($pairs !== [])
                    <section aria-labelledby="ba-title">
                        <h2 id="ba-title" class="t-section mb-6">{{ __('projects.before_after') }}</h2>
                        <div class="grid gap-6">
                            @foreach ($pairs as $pair)
                                @include('site.partials.before-after', ['before' => $pair['before'], 'after' => $pair['after']])
                            @endforeach
                        </div>
                    </section>
                @endif

                @if (\App\Support\Lightbox::playable($videos)->isNotEmpty())
                    <section aria-labelledby="videos-title">
                        <h2 id="videos-title" class="t-section mb-6">{{ __('projects.videos') }}</h2>
                        @include('site.partials.video-grid', ['videos' => $videos])
                    </section>
                @endif
            </div>

            <aside class="space-y-6 lg:sticky lg:top-24 lg:self-start">
                @if ($facts !== [])
                    <div class="rounded-[20px] border border-line bg-white p-6">
                        <h2 class="t-card text-navy">{{ __('projects.facts') }}</h2>
                        <dl class="mt-4 divide-y divide-line">
                            @foreach ($facts as $label => $value)
                                <div class="flex justify-between gap-4 py-3 text-[14.5px]">
                                    <dt class="text-muted">{{ $label }}</dt>
                                    <dd class="text-end font-bold text-ink"><bdi>{{ $value }}</bdi></dd>
                                </div>
                            @endforeach
                        </dl>
                    </div>
                @endif
                <div class="on-dark rounded-[20px] bg-deep p-6 text-[#C9D3E3]">
                    <h2 class="t-card text-white">{{ __('projects.similar_cta') }}</h2>
                    <p class="mt-2 text-[14.5px]">{{ __('services.request_this_text') }}</p>
                    <a href="{{ $site->quoteUrl() }}{{ $firstService ? '?service='.$firstService->id : '' }}" class="btn btn-primary mt-5 w-full">{{ __('site.request_quote') }}</a>
                </div>
            </aside>
        </div>
    </div>

    @if ($related->isNotEmpty())
        @include('site.partials.related-projects', ['items' => $related, 'title' => __('projects.related')])
    @endif
@endsection
