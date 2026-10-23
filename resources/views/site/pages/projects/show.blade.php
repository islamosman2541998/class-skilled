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
                    <div x-data="lightbox" class="space-y-3">
                        <div class="relative overflow-hidden rounded-[14px] bg-paper" @touchstart.passive="touchX = $event.changedTouches[0].clientX" @touchend.passive="if (touchX !== null && Math.abs($event.changedTouches[0].clientX - touchX) > 50) { main = (main + (($event.changedTouches[0].clientX < touchX) !== (document.documentElement.dir === 'rtl') ? 1 : -1) + {{ $images->count() }}) % {{ $images->count() }} } touchX = null">
                            @foreach ($images as $i => $image)
                                <button type="button" class="block w-full" x-show="main === {{ $i }}" @if ($i > 0) x-cloak @endif @click="open({{ $i }})" aria-haspopup="dialog" aria-label="{{ __('lightbox.label') }}: {{ $image->altText() }}">
                                    <x-site.picture :asset="$image" sizes="(min-width: 1024px) 800px, 100vw" :eager="$i === 0" class="aspect-[16/10] w-full object-cover" />
                                </button>
                            @endforeach
                        </div>
                        @if ($images->count() > 1)
                            <ul class="flex gap-2 overflow-x-auto pb-1" aria-label="{{ __('projects.gallery') }}">
                                @foreach ($images as $i => $image)
                                    <li class="shrink-0">
                                        <button type="button" @click="main = {{ $i }}" class="block overflow-hidden rounded-[10px] ring-2 ring-transparent transition" :class="main === {{ $i }} ? '!ring-sky' : 'opacity-75 hover:opacity-100'" :aria-current="(main === {{ $i }}).toString()" aria-label="{{ \App\Support\Translate::swiper(__('slider.go_to'), ['index' => $i + 1]) }}">
                                            <x-site.picture :asset="$image" sizes="120px" fallback="sm" class="h-16 w-24 object-cover lg:h-20 lg:w-28" />
                                        </button>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
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
