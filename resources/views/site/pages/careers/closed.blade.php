@extends('layouts.site')

@section('content')
    <section class="section">
        <div class="container-site max-w-2xl text-center">
            <h1 class="t-page">{{ __('careers.closed_title') }}</h1>
            <p class="t-lead mx-auto mt-3">{{ __('careers.closed_text') }}</p>
            <p class="mt-2 font-bold text-navy">{{ $job->title }}</p>
            <a href="{{ \App\Support\LocalizedRoute::url('careers.index') }}" class="btn btn-primary mt-8">{{ __('careers.view_open_jobs') }}</a>
        </div>
    </section>
@endsection
