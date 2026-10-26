@extends('layouts.site')

@php
    $cover = $service->firstAsset('cover');
    $gallery = $service->assetsIn('gallery');
    $locale = app()->getLocale();
    $scope = \App\Support\Translate::list($service->scope);
    $stages = \App\Support\Translate::list($service->stages);
    $whatsapp = $site->whatsappUrl();
    $faqItems = $faqs->map(fn ($faq) => ['q' => (string) $faq->getTranslation('question', $locale), 'a' => (string) $faq->getTranslation('answer', $locale)])
        ->filter(fn ($item) => $item['q'] !== '')->values()->all();
@endphp

@section('content')
    @include('site.partials.page-header', [
        'title' => $service->title,
        'lead' => $service->excerpt,
        'crumbs' => [__('services.title') => \App\Support\LocalizedRoute::url('services.index'), $service->title => null],
    ])

    <div class="section">
        <div class="container-site grid gap-10 lg:grid-cols-[minmax(0,1fr)_340px] lg:gap-14">
            <div class="min-w-0 space-y-12">
                @if ($cover)
                    <div data-reveal="image">
                        <x-site.picture :asset="$cover" sizes="(min-width: 1024px) 800px, 100vw" :eager="true" class="aspect-[16/9] w-full rounded-[14px] object-cover" />
                    </div>
                @endif

                @if (filled($service->summary))
                    <p class="t-lead !max-w-none text-ink">{{ $service->summary }}</p>
                @endif

                @if ($blocks !== [])
                    @include('site.partials.blocks', ['blocks' => $blocks])
                @endif

                @if ($scope !== [])
                    <section aria-labelledby="scope-title">
                        <h2 id="scope-title" class="t-section">{{ __('services.scope') }}</h2>
                        <ul class="mt-5 grid gap-3 sm:grid-cols-2">
                            @foreach ($scope as $line)
                                <li class="flex gap-3 rounded-[12px] border border-line bg-white p-4"><span class="mt-2.5 size-1.5 shrink-0 rounded-full bg-sky" aria-hidden="true"></span><span>{{ $line }}</span></li>
                            @endforeach
                        </ul>
                    </section>
                @endif

                @if ($stages !== [])
                    <section aria-labelledby="stages-title">
                        <h2 id="stages-title" class="t-section">{{ __('services.stages') }}</h2>
                        <ol class="mt-5 space-y-4" data-reveal="stagger">
                            @foreach ($stages as $stage)
                                <li class="flex items-start gap-4">
                                    <span class="grid size-9 shrink-0 place-items-center rounded-full bg-sky font-heading text-[14px] font-extrabold text-deep">{{ $loop->iteration }}</span>
                                    <p class="pt-1.5">{{ $stage }}</p>
                                </li>
                            @endforeach
                        </ol>
                    </section>
                @endif

                @if ($gallery->isNotEmpty())
                    <section aria-labelledby="gallery-title">
                        <h2 id="gallery-title" class="t-section mb-6">{{ __('services.gallery') }}</h2>
                        @include('site.partials.media-gallery', ['items' => $gallery, 'label' => __('services.gallery')])
                    </section>
                @endif

                @if ($faqItems !== [])
                    <section aria-labelledby="faq-title">
                        <h2 id="faq-title" class="t-section mb-6">{{ __('services.faq') }}</h2>
                        @include('site.partials.faq-list', ['items' => $faqItems])
                    </section>
                @endif
            </div>

            <aside class="space-y-6 lg:sticky lg:top-24 lg:self-start">
                <div class="on-dark rounded-[20px] bg-deep p-6 text-[#C9D3E3]">
                    <h2 class="t-card text-white">{{ __('services.request_this') }}</h2>
                    <p class="mt-2 text-[14.5px]">{{ __('services.request_this_text') }}</p>
                    <div class="mt-5 grid gap-3">
                        <a href="{{ $site->quoteUrl() }}?service={{ $service->id }}" class="btn btn-primary">{{ __('site.request_quote') }}</a>
                        @if ($whatsapp)
                            <a href="{{ $whatsapp }}" target="_blank" rel="noopener" class="btn btn-ghost-light">{{ __('site.whatsapp_us') }}</a>
                        @endif
                    </div>
                </div>

                @if ($others->count() > 1)
                    <nav class="rounded-[20px] border border-line bg-white p-5" aria-labelledby="others-title">
                        <h2 id="others-title" class="t-card text-navy">{{ __('services.other_services') }}</h2>
                        <ul class="mt-3 space-y-1">
                            @foreach ($others as $other)
                                <li>
                                    <a href="{{ $other->localizedUrl() }}" @class([
                                        'flex min-h-11 items-center rounded-[10px] px-3 text-[14.5px] transition',
                                        'bg-paper font-bold text-navy shadow-[inset_3px_0_0_var(--c-sky)] rtl:shadow-[inset_-3px_0_0_var(--c-sky)]' => $other->is($service),
                                        'text-ink hover:bg-paper' => ! $other->is($service),
                                    ]) @if ($other->is($service)) aria-current="page" @endif>{{ $other->title }}</a>
                                </li>
                            @endforeach
                        </ul>
                    </nav>
                @endif
            </aside>
        </div>
    </div>

    @if ($projects->isNotEmpty())
        @include('site.partials.related-projects', ['items' => $projects, 'title' => __('services.related_projects'), 'link' => ['label' => __('site.view_all'), 'url' => \App\Support\LocalizedRoute::url('projects.index')]])
    @endif
@endsection
