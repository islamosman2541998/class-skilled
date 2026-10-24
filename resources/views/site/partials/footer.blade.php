@php
    $resolver = app(\App\Services\Menus\MenuResolver::class);
    $columns = collect([\App\Enums\MenuLocation::Footer1, \App\Enums\MenuLocation::Footer2, \App\Enums\MenuLocation::Footer3])
        ->map(fn ($location) => ['title' => $resolver->title($location), 'items' => array_values(array_filter(
            collect($menus[$location->value] ?? [])->flatMap(fn ($n) => $n->children !== [] ? $n->children : [$n])->all(),
            fn ($n) => $n->url
        ))])
        ->filter(fn ($column) => $column['items'] !== []);
    $social = $site->social();
    $mobile = $site->mobilePhone();
    $whatsapp = $site->whatsappUrl();
@endphp
<footer class="site-footer on-dark">
    {{-- Desktop columns follow the number of footer menus that have links (no empty column). --}}
    <div @class([
        'container-site grid grid-cols-2 gap-x-6 gap-y-8 py-10 lg:gap-10 lg:py-12',
        'lg:grid-cols-[1.2fr_1.2fr]' => $columns->count() === 0,
        'lg:grid-cols-[1.2fr_1.3fr_1.2fr]' => $columns->count() === 1,
        'lg:grid-cols-[1.2fr_0.8fr_1.3fr_1.2fr]' => $columns->count() === 2,
        'lg:grid-cols-[1.3fr_repeat(3,1fr)_1.3fr]' => $columns->count() >= 3,
    ])>
        <div class="col-span-2 max-w-sm lg:col-span-1">
            <a href="{{ $site->homeUrl() }}" class="inline-flex items-center gap-3" aria-label="{{ $site->name() }}">
                <img src="{{ asset('images/brand/mark-white-sm.webp') }}" alt="" width="58" height="40" class="h-12 w-auto lg:h-14" loading="lazy" decoding="async">
                <span class="leading-tight">
                    <span class="block font-heading text-[19px] font-extrabold text-white lg:text-[21px]">{{ $site->name() }}</span>
                    @if ($site->tagline() !== '')
                        <span class="mt-0.5 block text-[13px] font-medium text-white/70">{{ $site->tagline() }}</span>
                    @endif
                </span>
            </a>
            @if ($site->footerAbout() !== '')
                <p class="mt-4 text-[14px] leading-relaxed">{{ $site->footerAbout() }}</p>
            @endif
            @if ($social !== [])
                <div class="mt-4">
                    <p class="text-[13px] font-bold text-white">{{ __('site.follow_us') }}</p>
                    <ul class="mt-3 flex flex-wrap gap-2.5">
                        @foreach ($social as $network => $url)
                            <li><a href="{{ $url }}" target="_blank" rel="noopener me" class="footer-social" aria-label="{{ __('site.social_'.$network) }}" title="{{ __('site.social_'.$network) }}"><x-site.social-icon :network="$network" class="size-[18px]" /></a></li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>

        @foreach ($columns as $column)
            <nav aria-label="{{ $column['title'] ?: __('site.footer_navigation') }}">
                @if ($column['title'] !== '')
                    <h2 class="font-heading text-[15px] font-bold text-white">{{ $column['title'] }}</h2>
                @endif
                <ul class="mt-3 space-y-0.5 text-[14px]">
                    @foreach ($column['items'] as $node)
                        <li><a href="{{ $node->url }}" class="inline-flex min-h-8 items-center" @if ($node->newTab) target="_blank" rel="noopener" @endif>{{ $node->label }}</a></li>
                    @endforeach
                </ul>
            </nav>
        @endforeach

        <div class="col-span-2 lg:col-span-1">
            <h2 class="font-heading text-[15px] font-bold text-white">{{ __('site.contact_info') }}</h2>
            <ul class="mt-3 space-y-2.5 text-[14px]">
                @if ($mobile)
                    <li><a href="{{ $mobile['href'] }}" class="inline-flex min-h-8 items-center gap-2" aria-label="{{ __('site.call') }} {{ $mobile['label'] }}"><x-heroicon-m-device-phone-mobile class="size-4 text-sky" aria-hidden="true" /><bdi dir="ltr">{{ $mobile['label'] }}</bdi></a></li>
                @endif
                @if ($whatsapp && $site->whatsappNumber() !== '')
                    <li><a href="{{ $whatsapp }}" target="_blank" rel="noopener" class="inline-flex min-h-8 items-center gap-2" aria-label="{{ __('site.whatsapp') }} {{ $site->whatsappNumber() }}"><x-site.social-icon network="whatsapp" class="size-4 text-sky" /><bdi dir="ltr">{{ $site->whatsappNumber() }}</bdi></a></li>
                @endif
                @foreach ($site->emails() as $email)
                    <li><a href="mailto:{{ $email }}" class="inline-flex items-center gap-2"><x-heroicon-m-envelope class="size-4 text-sky" aria-hidden="true" /><bdi dir="ltr">{{ $email }}</bdi></a></li>
                @endforeach
                @if ($site->address() !== '')
                    <li class="flex gap-2"><x-heroicon-m-map-pin class="mt-1 size-4 shrink-0 text-sky" aria-hidden="true" /><span>{{ $site->address() }}</span></li>
                @endif
            </ul>
        </div>
    </div>

    <div class="border-t border-white/10">
        <div class="container-site flex flex-col gap-2 py-4 text-[13px] sm:flex-row sm:items-center sm:justify-between">
            <p>&copy; {{ now()->year }} {{ $site->legalName() ?: $site->name() }}. {{ __('site.all_rights_reserved') }}</p>
            @if (($menus['footer_legal'] ?? []) !== [])
                <ul class="flex flex-wrap gap-x-5 gap-y-1">
                    @foreach ($menus['footer_legal'] as $node)
                        @if ($node->url)
                            <li><a href="{{ $node->url }}" class="inline-flex min-h-8 items-center">{{ $node->label }}</a></li>
                        @endif
                    @endforeach
                </ul>
            @endif
        </div>
    </div>
</footer>
