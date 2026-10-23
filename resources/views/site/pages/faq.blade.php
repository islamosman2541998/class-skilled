@extends('layouts.site')

@php
    $locale = app()->getLocale();
    $items = $faqs->map(fn ($faq) => [
        'key' => $faq->id,
        'q' => (string) $faq->getTranslation('question', $locale),
        'a' => (string) $faq->getTranslation('answer', $locale),
    ])->all();
@endphp

@section('content')
    @include('site.partials.page-header', ['title' => __('faq.title'), 'lead' => __('faq.lead'), 'crumbs' => [__('faq.title') => null]])

    <section class="section !pt-10">
        <div class="container-site max-w-3xl" x-data="{ q: '', matches: {{ count($items) }}, filter() { const term = this.q.trim().toLowerCase(); let n = 0; this.$refs.list.querySelectorAll('[data-faq]').forEach((el) => { const hit = !term || el.textContent.toLowerCase().includes(term); el.hidden = !hit; if (hit) n++; }); this.matches = n; } }">
            @if ($items === [])
                <p class="text-muted">{{ __('faq.no_results') }}</p>
            @else
                <label class="relative block">
                    <span class="sr-only">{{ __('faq.search_placeholder') }}</span>
                    <x-heroicon-m-magnifying-glass class="pointer-events-none absolute start-4 top-1/2 size-5 -translate-y-1/2 text-muted" aria-hidden="true" />
                    <input type="search" x-model="q" @input.debounce.150ms="filter()" placeholder="{{ __('faq.search_placeholder') }}" class="h-12 w-full rounded-[12px] border border-line bg-white ps-11 pe-4 focus:border-sky focus:outline-none">
                </label>
                <div class="mt-6" x-ref="list">
                    @include('site.partials.faq-list', ['items' => $items])
                </div>
                <p class="mt-6 text-center text-muted" x-show="matches === 0" x-cloak role="status">{{ __('faq.no_results') }}</p>
            @endif

            <div class="mt-12 flex flex-col items-start justify-between gap-4 rounded-[20px] bg-paper p-6 sm:flex-row sm:items-center">
                <p class="t-card text-navy">{{ __('faq.still_have_questions') }}</p>
                <a href="{{ \App\Support\LocalizedRoute::url('contact') }}" class="btn btn-primary">{{ __('faq.contact_us') }}</a>
            </div>
        </div>
    </section>
@endsection
