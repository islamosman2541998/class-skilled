@php
    $email = $site->emails()[0] ?? null;
    $otherLocale = app()->getLocale() === 'ar' ? 'en' : 'ar';
@endphp
<div class="relative z-50 hidden h-10 bg-deep text-[13.5px] text-[#C9D3E3] lg:block">
    <div class="container-site flex h-full items-center justify-between gap-6">
        <ul class="flex items-center gap-5">
            @foreach ($site->phones() as $phone)
                <li>
                    <a href="{{ $phone['href'] }}" class="inline-flex items-center gap-1.5 hover:text-white">
                        <x-heroicon-m-phone class="size-3.5 text-sky" aria-hidden="true" />
                        <bdi dir="ltr">{{ $phone['label'] }}</bdi>
                    </a>
                </li>
            @endforeach
            @if ($email)
                <li>
                    <a href="mailto:{{ $email }}" class="inline-flex items-center gap-1.5 hover:text-white">
                        <x-heroicon-m-envelope class="size-3.5 text-sky" aria-hidden="true" />
                        <bdi dir="ltr">{{ $email }}</bdi>
                    </a>
                </li>
            @endif
        </ul>

        <div class="flex items-center gap-5">
            @if ($social = $site->social())
                <ul class="flex items-center gap-1" aria-label="{{ __('site.follow_us') }}">
                    @foreach ($social as $network => $url)
                        <li>
                            <a href="{{ $url }}" target="_blank" rel="noopener me" class="grid size-8 place-items-center rounded-full text-[#C9D3E3] transition-colors hover:bg-white/10 hover:text-sky" aria-label="{{ __('site.social_'.$network) }}" title="{{ __('site.social_'.$network) }}">
                                <x-site.social-icon :network="$network" class="size-4" />
                            </a>
                        </li>
                    @endforeach
                </ul>
            @endif
            @foreach ($menus['topbar'] ?? [] as $node)
                @if ($node->url && $node->showOn('desktop'))
                    <a href="{{ $node->url }}" class="hover:text-white" @if ($node->newTab) target="_blank" rel="noopener" @endif>{{ $node->label }}</a>
                @endif
            @endforeach
            <a href="{{ $seo->alternateUrl($otherLocale) }}" hreflang="{{ $otherLocale }}" lang="{{ $otherLocale }}" class="font-semibold text-white hover:text-sky" aria-label="{{ __('site.switch_language_label') }}" data-lang-switch>{{ __('site.switch_language') }}</a>
        </div>
    </div>
</div>
