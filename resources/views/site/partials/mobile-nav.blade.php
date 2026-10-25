@php
    $mobile = array_values(array_filter($menus['mobile'] ?? [], fn ($n) => $n->showOn('mobile')));
    $otherLocale = app()->getLocale() === 'ar' ? 'en' : 'ar';
    $whatsapp = $site->whatsappUrl();
@endphp
<div id="mobile-nav" class="fixed inset-0 z-[60] lg:hidden" hidden data-mobile-nav>
    <div class="absolute inset-0 bg-deep/60 opacity-0 transition-opacity duration-300" data-mobile-nav-backdrop></div>
    <div
        class="absolute inset-y-0 start-0 flex w-[min(360px,88vw)] flex-col bg-white shadow-2xl transition-transform duration-300 ltr:-translate-x-full rtl:translate-x-full"
        role="dialog"
        aria-modal="true"
        aria-label="{{ __('site.menu') }}"
        data-mobile-nav-panel
    >
        <div class="flex h-[60px] shrink-0 items-center justify-between border-b border-line px-4">
            <a href="{{ $site->homeUrl() }}" class="flex items-center gap-2">
                @include('site.partials.brand-mark', ['light' => false, 'class' => ''])
                <span class="font-heading text-[15px] font-extrabold text-navy">{{ $site->name() }}</span>
            </a>
            <button type="button" class="inline-grid size-11 place-items-center rounded-full text-navy" aria-label="{{ __('site.close_menu') }}" data-mobile-nav-close>
                <x-heroicon-o-x-mark class="size-6" aria-hidden="true" />
            </button>
        </div>

        <nav class="flex-1 overflow-y-auto overscroll-contain px-3 py-3" aria-label="{{ __('site.main_navigation') }}">
            <ul>
                @foreach ($mobile as $i => $node)
                    <li class="border-b border-line last:border-0">
                        @if ($node->hasChildren())
                            @php($viewAll = $node->viewAll ?? ($node->url ? ['label' => __('site.view_all'), 'url' => $node->url] : null))
                            <button type="button" class="flex min-h-[52px] w-full items-center justify-between gap-3 px-2 text-start text-base font-semibold text-ink" aria-expanded="false" aria-controls="mnav-sub-{{ $i }}" data-accordion>
                                <span @class(['text-navy' => $node->active])>{{ $node->label }}</span>
                                <x-heroicon-m-chevron-down class="size-5 text-muted transition-transform" aria-hidden="true" />
                            </button>
                            <ul id="mnav-sub-{{ $i }}" class="mnav-sub" hidden>
                                @foreach ($node->children as $child)
                                    @continue(! $child->url || ! $child->showOn('mobile'))
                                    <li>
                                        <a href="{{ $child->url }}" class="mnav-sub-link" @if ($child->active) aria-current="page" @endif @if ($child->newTab) target="_blank" rel="noopener" @endif>{{ $child->label }}</a>
                                    </li>
                                @endforeach
                                @if ($viewAll)
                                    <li>
                                        <a href="{{ $viewAll['url'] }}" class="mnav-sub-all">
                                            {{ $viewAll['label'] }}
                                            <x-heroicon-m-chevron-right class="size-4" aria-hidden="true" />
                                        </a>
                                    </li>
                                @endif
                            </ul>
                        @elseif ($node->url)
                            <a href="{{ $node->url }}" class="flex min-h-[52px] items-center px-2 text-base font-semibold {{ $node->active ? 'text-navy' : 'text-ink' }}" @if ($node->active) aria-current="page" @endif @if ($node->newTab) target="_blank" rel="noopener" @endif>{{ $node->label }}</a>
                        @else
                            <span class="flex min-h-[52px] items-center px-2 text-[13px] font-bold text-muted">{{ $node->label }}</span>
                        @endif
                    </li>
                @endforeach
            </ul>
        </nav>

        <div class="shrink-0 space-y-3 border-t border-line p-4 pb-[max(1rem,env(safe-area-inset-bottom))]">
            <a href="{{ $site->quoteUrl() }}" class="btn btn-primary w-full">{{ __('site.request_quote') }}</a>
            <div class="grid grid-cols-2 gap-3">
                @if ($whatsapp)
                    <a href="{{ $whatsapp }}" target="_blank" rel="noopener" class="btn btn-secondary">{{ __('site.whatsapp') }}</a>
                @endif
                <a href="{{ $seo->alternateUrl($otherLocale) }}" hreflang="{{ $otherLocale }}" lang="{{ $otherLocale }}" class="btn btn-secondary" aria-label="{{ __('site.switch_language_label') }}" data-lang-switch>{{ __('site.switch_language') }}</a>
            </div>
        </div>
    </div>
</div>
