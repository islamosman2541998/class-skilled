@php
    $resolver = app(\App\Services\Menus\MenuResolver::class);
    $columns = collect([\App\Enums\MenuLocation::Footer1, \App\Enums\MenuLocation::Footer2, \App\Enums\MenuLocation::Footer3])
        ->map(fn ($location) => ['title' => $resolver->title($location), 'items' => array_values(array_filter($menus[$location->value] ?? [], fn ($n) => $n->url))])
        ->filter(fn ($column) => $column['items'] !== []);
    $logo = $site->logo(true);
    $social = $site->social();
@endphp
<footer class="site-footer on-dark">
    <div class="container-site grid gap-10 py-14 lg:grid-cols-[1.3fr_repeat(3,1fr)_1.3fr] lg:gap-8 lg:py-20">
        <div class="max-w-sm">
            <a href="{{ $site->homeUrl() }}" class="inline-block" aria-label="{{ $site->name() }}">
                @if ($logo && $logo->isImage())
                    <img src="{{ $logo->url('sm') }}" alt="{{ $site->name() }}" class="h-24 w-auto" loading="lazy" decoding="async" @if ($logo->width && $logo->height) width="{{ $logo->width }}" height="{{ $logo->height }}" @endif>
                @else
                    <img src="{{ asset('images/brand/logo-white.png') }}" alt="{{ $site->name() }}" width="864" height="976" class="h-24 w-auto" loading="lazy" decoding="async">
                @endif
            </a>
            @if ($site->footerAbout() !== '')
                <p class="mt-5 text-[14px] leading-relaxed">{{ $site->footerAbout() }}</p>
            @endif
            @if ($social !== [])
                <div class="mt-5">
                    <p class="text-[13px] font-bold text-white">{{ __('site.follow_us') }}</p>
                    <ul class="mt-2 flex flex-wrap gap-x-4 gap-y-2 text-[13.5px]">
                        @foreach ($social as $network => $url)
                            <li><a href="{{ $url }}" target="_blank" rel="noopener me" class="inline-flex min-h-8 items-center underline-offset-4 hover:underline">{{ __('site.social_'.$network) }}</a></li>
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
                <ul class="mt-4 space-y-1 text-[14px]">
                    @foreach ($column['items'] as $node)
                        <li><a href="{{ $node->url }}" class="inline-flex min-h-8 items-center" @if ($node->newTab) target="_blank" rel="noopener" @endif>{{ $node->label }}</a></li>
                    @endforeach
                </ul>
            </nav>
        @endforeach

        <div>
            <h2 class="font-heading text-[15px] font-bold text-white">{{ __('site.contact_info') }}</h2>
            <ul class="mt-4 space-y-3 text-[14px]">
                @foreach ($site->phones() as $phone)
                    <li><a href="{{ $phone['href'] }}" class="inline-flex items-center gap-2"><x-heroicon-m-phone class="size-4 text-sky" aria-hidden="true" /><bdi dir="ltr">{{ $phone['label'] }}</bdi></a></li>
                @endforeach
                @foreach ($site->emails() as $email)
                    <li><a href="mailto:{{ $email }}" class="inline-flex items-center gap-2"><x-heroicon-m-envelope class="size-4 text-sky" aria-hidden="true" /><bdi dir="ltr">{{ $email }}</bdi></a></li>
                @endforeach
                @if ($site->address() !== '')
                    <li class="flex gap-2"><x-heroicon-m-map-pin class="mt-1 size-4 shrink-0 text-sky" aria-hidden="true" /><span>{{ $site->address() }}</span></li>
                @endif
                @foreach ($site->workingHours() as $row)
                    <li class="flex gap-2"><x-heroicon-m-clock class="mt-1 size-4 shrink-0 text-sky" aria-hidden="true" /><span>{{ $row['days'] }}: {{ $row['hours'] }}</span></li>
                @endforeach
            </ul>
        </div>
    </div>

    @php($registration = $site->registration())
    @if ($registration !== [])
        <div class="border-t border-white/10">
            <dl class="container-site flex flex-wrap gap-x-8 gap-y-2 py-5 text-[13px]">
                @foreach ($registration as $label => $value)
                    <div class="flex gap-1.5"><dt class="text-white/60">{{ $label }}:</dt><dd><bdi>{{ $value }}</bdi></dd></div>
                @endforeach
            </dl>
        </div>
    @endif

    <div class="border-t border-white/10">
        <div class="container-site flex flex-col gap-3 py-5 text-[13px] sm:flex-row sm:items-center sm:justify-between">
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
