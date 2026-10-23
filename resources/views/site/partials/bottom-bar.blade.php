@php
    $phoneUrl = $site->primaryPhoneUrl();
    $whatsapp = $site->whatsappUrl();
@endphp
<nav class="bottom-bar lg:hidden" aria-label="{{ __('site.quick_actions') }}" data-bottom-bar>
    @if ($phoneUrl)
        <a href="{{ $phoneUrl }}"><x-heroicon-m-phone class="size-4" aria-hidden="true" />{{ __('site.call') }}</a>
    @endif
    @if ($whatsapp)
        <a href="{{ $whatsapp }}" target="_blank" rel="noopener"><x-heroicon-m-chat-bubble-oval-left class="size-4" aria-hidden="true" />{{ __('site.whatsapp') }}</a>
    @endif
    <a href="{{ $site->quoteUrl() }}" class="is-primary">{{ __('site.quote_short') }}</a>
</nav>

@if ($whatsapp)
    <a href="{{ $whatsapp }}" target="_blank" rel="noopener" class="fixed end-6 bottom-6 z-30 hidden items-center gap-2 rounded-full bg-deep px-5 py-3 text-[14px] font-bold text-white shadow-lg ring-1 ring-white/15 transition-colors hover:bg-navy lg:inline-flex" aria-label="{{ __('site.whatsapp_us') }}">
        <x-heroicon-m-chat-bubble-oval-left class="size-4 text-sky" aria-hidden="true" />{{ __('site.whatsapp') }}
    </a>
@endif
