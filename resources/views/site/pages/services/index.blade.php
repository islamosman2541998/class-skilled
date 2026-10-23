@extends('layouts.site')

@section('content')
    @include('site.partials.page-header', ['title' => __('services.title'), 'lead' => __('services.lead'), 'crumbs' => [__('services.title') => null]])

    <section class="section">
        <div class="container-site">
            @if ($services->isEmpty())
                <p class="t-lead">{{ __('services.empty') }}</p>
            @else
                <ul class="grid gap-6 md:grid-cols-2" data-reveal="stagger">
                    @foreach ($services as $service)
                        @php($cover = $service->firstAsset('cover'))
                        <li>
                            <article class="group relative flex h-full flex-col overflow-hidden rounded-[20px] border border-line bg-white transition hover:border-sky/60 sm:flex-row">
                                @if ($cover)
                                    <div class="overflow-hidden sm:w-[44%] sm:shrink-0">
                                        <x-site.picture :asset="$cover" sizes="(min-width: 1024px) 260px, (min-width: 640px) 40vw, 100vw" class="aspect-[4/3] size-full object-cover transition duration-300 group-hover:scale-[1.04] sm:aspect-auto" />
                                    </div>
                                @endif
                                <div class="flex flex-1 flex-col gap-2 p-6">
                                    <h2 class="t-card text-navy"><a href="{{ $service->localizedUrl() }}" class="stretched">{{ $service->title }}</a></h2>
                                    @if (filled($service->excerpt))
                                        <p class="text-[14.5px] text-muted">{{ $service->excerpt }}</p>
                                    @endif
                                    <span class="text-link mt-auto pt-3 text-[14px]" aria-hidden="true">{{ __('services.details') }}</span>
                                </div>
                            </article>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </section>
@endsection
