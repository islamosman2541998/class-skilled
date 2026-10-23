@extends('layouts.site')

@section('content')
    @include('site.partials.page-header', ['title' => __('careers.title'), 'lead' => __('careers.lead'), 'crumbs' => [__('careers.title') => null]])

    <div class="section !pt-10">
        <div class="container-site grid gap-10 lg:grid-cols-[minmax(0,1fr)_340px] lg:gap-14">
            <section aria-labelledby="open-jobs">
                <h2 id="open-jobs" class="t-section">{{ __('careers.open_jobs') }}</h2>
                @if ($jobs->isEmpty())
                    <p class="mt-6 rounded-[14px] border border-line bg-paper p-8 text-center text-muted">{{ __('careers.no_jobs') }}</p>
                @else
                    <ul class="mt-6 space-y-4" data-reveal="stagger">
                        @foreach ($jobs as $job)
                            <li>
                                <article class="group relative flex flex-col gap-4 rounded-[20px] border border-line bg-white p-6 transition hover:border-sky/60 sm:flex-row sm:items-center sm:justify-between">
                                    <div>
                                        <h3 class="t-card text-navy"><a href="{{ $job->localizedUrl() }}" class="stretched">{{ $job->title }}</a></h3>
                                        <p class="t-small mt-2 flex flex-wrap gap-x-4 gap-y-1 text-muted">
                                            @if (filled($job->department))<span>{{ __('careers.department') }}: {{ $job->department }}</span>@endif
                                            <span>{{ __('enums.job_type.'.$job->job_type->value) }}</span>
                                            @if (filled($job->location))<span>{{ $job->location }}</span>@endif
                                            @if ($job->published_at)<span>{{ __('careers.posted_on', ['date' => $job->published_at->locale(app()->getLocale())->translatedFormat('j F Y')]) }}</span>@endif
                                        </p>
                                    </div>
                                    <span class="text-link shrink-0 text-[14px]" aria-hidden="true">{{ __('careers.details_apply') }}</span>
                                </article>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </section>

            <aside id="general-application" class="on-dark self-start rounded-[20px] bg-deep p-6 text-[#C9D3E3] lg:sticky lg:top-24">
                <h2 class="t-card text-white">{{ __('careers.general_title') }}</h2>
                <p class="mt-2 text-[14.5px]">{{ __('careers.general_text') }}</p>
                <a href="#general-form" class="btn btn-primary mt-5 w-full">{{ __('careers.general_button') }}</a>
            </aside>
        </div>
    </div>

    <section id="general-form" class="section section--paper" aria-labelledby="general-form-title">
        <div class="container-site max-w-3xl">
            <h2 id="general-form-title" class="t-section">{{ __('careers.general_title') }}</h2>
            <p class="t-lead mt-2">{{ __('careers.general_text') }}</p>
            @include('site.partials.job-application', ['job' => null])
        </div>
    </section>
@endsection
