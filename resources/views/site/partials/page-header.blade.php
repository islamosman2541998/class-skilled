{{-- Inner page header: breadcrumbs, title, lead on paper. $crumbs = [label => url|null] (home is prepended). --}}
@php($seo->breadcrumbs($crumbs ?? []))
@if ($preview ?? false)
    <div class="bg-[#b54708] py-2 text-center text-[13.5px] font-bold text-white" role="status">{{ __('site.preview_ribbon') }}</div>
@endif
<header class="bg-paper py-10 lg:py-14">
    <div class="container-site">
        <x-site.breadcrumbs :items="$crumbs ?? []" />
        <h1 class="t-page mt-4">{{ $title }}</h1>
        @if (filled($lead ?? null))
            <p class="t-lead mt-3">{{ $lead }}</p>
        @endif
        @isset($meta)
            <div class="t-small mt-4 flex flex-wrap items-center gap-x-4 gap-y-1 text-muted">{{ $meta }}</div>
        @endisset
    </div>
</header>
