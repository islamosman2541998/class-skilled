@props(['items' => [], 'light' => false])
{{-- items: [label => url|null]; the last item is the current page. Home is prepended. --}}
@php($trail = array_merge([__('site.home') => app(\App\Services\SiteContext::class)->homeUrl()], $items))
<nav aria-label="{{ __('site.breadcrumbs') }}" {{ $attributes->merge(['class' => 't-small']) }}>
    <ol class="flex flex-wrap items-center gap-x-2 gap-y-1 {{ $light ? 'text-white/75' : 'text-muted' }}">
        @foreach ($trail as $label => $url)
            <li class="inline-flex items-center gap-2">
                @if (! $loop->last && $url)
                    <a href="{{ $url }}" class="hover:underline {{ $light ? 'hover:text-white' : 'hover:text-navy' }}">{{ $label }}</a>
                    <x-heroicon-m-chevron-left class="size-3.5 opacity-60 ltr:rotate-180" aria-hidden="true" />
                @else
                    <span @if ($loop->last) aria-current="page" class="{{ $light ? 'text-white' : 'text-ink' }} font-semibold" @endif>{{ $label }}</span>
                @endif
            </li>
        @endforeach
    </ol>
</nav>
