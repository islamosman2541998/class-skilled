@extends('layouts.site')

@php
    $crumbs = [];
    foreach ($page->ancestors() as $ancestor) {
        $crumbs[$ancestor->title] = $ancestor->localizedUrl();
    }
    $crumbs[$page->title] = null;
@endphp

@section('content')
    @include('site.partials.page-header', ['title' => $page->title, 'lead' => $page->excerpt, 'crumbs' => $crumbs])

    <div class="section">
        <div class="container-site space-y-12 lg:space-y-16">
            @if ($hero)
                <div data-reveal="fade-up">
                    @switch($hero['type'])
                        @case('image')
                            @if ($hero['image'])
                                <x-site.picture :asset="$hero['image']" :mobile="$hero['mobile']" sizes="(min-width: 1264px) 1200px, 100vw" :eager="true" fallback="xl" class="max-h-[560px] w-full rounded-[14px] object-cover" />
                            @endif
                            @break
                        @case('video')
                        @case('embed')
                            @include('site.partials.video-player', ['video' => $hero['video'] ?? $hero['embed']])
                            @break
                        @case('pdf')
                            @php($pdf = $hero['pdf'])
                            <div class="flex flex-col gap-6 rounded-[20px] border border-line bg-paper p-6 sm:flex-row sm:items-center lg:p-8">
                                @if ($pdf->poster)
                                    <x-site.picture :asset="$pdf->poster" sizes="200px" class="w-40 shrink-0 rounded-[10px] shadow-md" />
                                @else
                                    <span class="grid h-48 w-36 shrink-0 place-items-center rounded-[10px] bg-white font-heading text-[22px] font-extrabold text-navy shadow-md" aria-hidden="true">{{ $pdf->extensionLabel() }}</span>
                                @endif
                                <div>
                                    <p class="t-card text-navy">{{ $pdf->titleText() }}</p>
                                    <p class="t-small mt-1 text-muted"><bdi dir="ltr">{{ $pdf->extensionLabel() }} · {{ $pdf->humanSize() }}</bdi></p>
                                    <div class="mt-5 flex flex-wrap gap-3">
                                        <a href="{{ $pdf->url() }}" target="_blank" rel="noopener" class="btn btn-primary">{{ __('site.open') }}</a>
                                        <a href="{{ $pdf->url() }}" download class="btn btn-secondary">{{ __('site.download') }}</a>
                                    </div>
                                </div>
                            </div>
                            @break
                    @endswitch
                </div>
            @endif

            @if ($blocks !== [])
                @include('site.partials.blocks', ['blocks' => $blocks])
            @endif

            @foreach ($attachments as $group)
                <section @if ($group['title'] !== '') aria-labelledby="att-{{ $group['group'] }}" @endif>
                    @if ($group['title'] !== '')
                        <h2 id="att-{{ $group['group'] }}" class="t-section mb-6">{{ $group['title'] }}</h2>
                    @endif
                    @switch($group['group'])
                        @case('files')
                            @include('site.partials.file-list', ['files' => $group['assets']])
                            @break
                        @case('videos')
                            @include('site.partials.video-grid', ['videos' => $group['assets']])
                            @break
                        @case('images')
                            @include('site.partials.image-grid', ['images' => $group['assets'], 'columns' => max(2, min(4, $group['columns'])), 'limit' => null, 'masonry' => $group['display'] === 'masonry'])
                            @break
                    @endswitch
                </section>
            @endforeach
        </div>
    </div>
@endsection
