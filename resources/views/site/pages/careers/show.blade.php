@extends('layouts.site')

@php($requirements = $job->requirementsList())

@section('content')
    @include('site.partials.page-header', [
        'title' => $job->title,
        'crumbs' => [__('careers.title') => \App\Support\LocalizedRoute::url('careers.index'), $job->title => null],
    ])

    <div class="section !pt-10">
        <div class="container-site grid gap-10 lg:grid-cols-[minmax(0,1fr)_340px] lg:gap-14">
            <div class="min-w-0 space-y-10">
                @if (filled($job->description))
                    <section aria-labelledby="job-description">
                        <h2 id="job-description" class="t-section">{{ __('careers.description') }}</h2>
                        {{-- Description is sanitised with Purifier on save. --}}
                        <div class="prose-site mt-4">{!! $job->description !!}</div>
                    </section>
                @endif

                @if ($requirements !== [])
                    <section aria-labelledby="job-requirements">
                        <h2 id="job-requirements" class="t-section">{{ __('careers.requirements') }}</h2>
                        <ul class="mt-4 space-y-2">
                            @foreach ($requirements as $line)
                                <li class="flex gap-3"><span class="mt-2.5 size-1.5 shrink-0 rounded-full bg-sky" aria-hidden="true"></span><span>{{ $line }}</span></li>
                            @endforeach
                        </ul>
                    </section>
                @endif

                <section id="apply" aria-labelledby="apply-title" class="rounded-[20px] border border-line bg-paper p-6 lg:p-8">
                    <h2 id="apply-title" class="t-section">{{ __('careers.apply_title') }}</h2>
                    @if (view()->exists('site.partials.job-application'))
                        @include('site.partials.job-application', ['job' => $job])
                    @endif
                </section>
            </div>

            <aside class="self-start rounded-[20px] border border-line bg-white p-6 lg:sticky lg:top-24">
                <dl class="divide-y divide-line text-[14.5px]">
                    @if (filled($job->department))
                        <div class="flex justify-between gap-4 py-3"><dt class="text-muted">{{ __('careers.department') }}</dt><dd class="font-bold">{{ $job->department }}</dd></div>
                    @endif
                    <div class="flex justify-between gap-4 py-3"><dt class="text-muted">{{ __('careers.type') }}</dt><dd class="font-bold">{{ __('enums.job_type.'.$job->job_type->value) }}</dd></div>
                    @if (filled($job->location))
                        <div class="flex justify-between gap-4 py-3"><dt class="text-muted">{{ __('careers.location') }}</dt><dd class="font-bold">{{ $job->location }}</dd></div>
                    @endif
                </dl>
                <div class="t-small mt-3 space-y-1 text-muted">
                    @if ($job->published_at)
                        <p>{{ __('careers.posted_on', ['date' => $job->published_at->locale(app()->getLocale())->translatedFormat('j F Y')]) }}</p>
                    @endif
                    @if ($job->closes_at)
                        <p class="font-bold text-navy">{{ __('careers.closes_on', ['date' => $job->closes_at->locale(app()->getLocale())->translatedFormat('j F Y')]) }}</p>
                    @endif
                </div>
                <a href="#apply" class="btn btn-primary mt-5 w-full">{{ __('careers.apply_title') }}</a>
            </aside>
        </div>
    </div>
@endsection
