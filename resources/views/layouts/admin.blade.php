@php
    $locale = app()->getLocale();
    $user = auth()->user();
    $navigation = app(\App\Services\AdminNavigation::class)->tree($user);
@endphp
<!DOCTYPE html>
<html lang="{{ $locale }}" dir="{{ $locale === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex, nofollow">
    <title>{{ isset($title) && $title ? $title.' — ' : '' }}{{ __('admin.panel_name') }}</title>
    <link rel="icon" href="{{ asset('images/brand/mark-sm.png') }}">
    @vite(['resources/css/admin.css', 'resources/js/admin/app.js'])
    @livewireStyles
    @stack('head')
    <script>
        window.csLang = {{ Illuminate\Support\Js::from(['confirm' => __('admin.common.confirm'), 'cancel' => __('admin.common.cancel'), 'leave' => __('admin.common.leave'), 'copied' => __('admin.common.copied')]) }};
    </script>
</head>
<body class="admin">
    <a href="#main" class="sr-only focus:not-sr-only focus:fixed focus:top-2 focus:start-2 focus:z-[60] focus:rounded-lg focus:bg-white focus:px-4 focus:py-2">{{ __('site.skip_to_content') }}</a>

    <div x-data="{ drawer: false }" @keydown.escape.window="drawer = false" class="min-h-screen lg:flex">
        {{-- Sidebar (start side): off-canvas below 1024px --}}
        <div x-cloak x-show="drawer" x-transition.opacity class="fixed inset-0 z-40 bg-[rgb(27_42_71/.5)] lg:hidden" @click="drawer = false"></div>
        <aside
            class="fixed inset-y-0 start-0 z-50 flex w-[268px] flex-col bg-a-sidebar text-white transition-transform duration-200 ltr:-translate-x-full rtl:translate-x-full lg:sticky lg:top-0 lg:h-screen lg:translate-x-0! lg:shrink-0"
            :class="drawer ? 'translate-x-0!' : ''"
            aria-label="{{ __('admin.panel_name') }}"
        >
            <div class="flex h-[84px] items-center justify-between gap-3 border-b border-white/10 px-5">
                <a href="{{ route('admin.dashboard') }}" wire:navigate class="flex items-center gap-3">
                    <img src="{{ asset('images/brand/mark-white-sm.png') }}" alt="" class="h-11 w-11 object-contain">
                    <span class="leading-tight">
                        <span class="block font-[family-name:var(--font-head)] text-[16px] font-extrabold">{{ __('admin.brand') }}</span>
                        <span class="block text-[12.5px] text-white/60">{{ __('admin.panel_name') }}</span>
                    </span>
                </a>
                <button type="button" class="rounded-lg p-2 text-white/70 hover:bg-white/10 lg:hidden" @click="drawer = false" aria-label="{{ __('admin.topbar.close_menu') }}">
                    <x-heroicon-o-x-mark class="h-5 w-5" />
                </button>
            </div>

            <nav class="flex-1 overflow-y-auto py-4" aria-label="{{ __('admin.panel_name') }}">
                <ul class="space-y-1">
                    @foreach ($navigation as $entry)
                        @if (isset($entry['group']))
                            <li x-data="navGroup('{{ $entry['group'] }}', {{ $entry['open'] ? 'true' : 'false' }})" class="pt-3">
                                <button type="button" @click="toggle()" :aria-expanded="open" class="flex w-full items-center justify-between px-7 py-1.5 text-[12.5px] font-semibold text-white/50 hover:text-white/80">
                                    <span>{{ $entry['label'] }}</span>
                                    <x-heroicon-m-chevron-down class="h-4 w-4 transition-transform" x-bind:class="open ? '' : 'ltr:-rotate-90 rtl:rotate-90'" />
                                </button>
                                <ul x-show="open" x-collapse.duration.150ms class="mt-1 space-y-0.5">
                                    @foreach ($entry['items'] as $item)
                                        <li>
                                            <a href="{{ $item['url'] }}" wire:navigate class="a-nav-item" @if ($item['active']) aria-current="page" @endif>
                                                <span>{{ $item['label'] }}</span>
                                                @if ($item['badge'] > 0)
                                                    <span class="a-nav-count">{{ $item['badge'] }}</span>
                                                @endif
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            </li>
                        @else
                            <li>
                                <a href="{{ $entry['url'] }}" wire:navigate class="a-nav-item" @if ($entry['active']) aria-current="page" @endif>
                                    <span>{{ $entry['label'] }}</span>
                                </a>
                            </li>
                        @endif
                    @endforeach
                </ul>
            </nav>

            <div class="flex items-center justify-between border-t border-white/10 px-6 py-4 text-[12.5px] text-white/50">
                <span>{{ __('admin.version', ['version' => config('admin.version')]) }}</span>
                <a href="{{ url('/') }}" target="_blank" rel="noopener" class="hover:text-white">{{ __('admin.topbar.view_site') }}</a>
            </div>
        </aside>

        <div class="flex min-w-0 flex-1 flex-col">
            {{-- Top bar --}}
            <header class="sticky top-0 z-30 flex h-16 items-center gap-3 border-b border-a-border bg-white px-4 lg:px-8">
                <button type="button" class="rounded-lg p-2 hover:bg-a-bg lg:hidden" @click="drawer = true" aria-label="{{ __('admin.topbar.open_menu') }}">
                    <x-heroicon-o-bars-3 class="h-6 w-6" />
                </button>

                <button type="button" @click="$dispatch('open-command-palette')" class="flex h-10 min-w-0 flex-1 items-center justify-between gap-3 rounded-xl border border-a-border bg-a-bg px-4 text-start text-[13.5px] text-[#8a95a8] lg:max-w-md">
                    <span class="flex min-w-0 items-center gap-2">
                        <x-heroicon-o-magnifying-glass class="h-4 w-4 shrink-0" />
                        <span class="truncate">{{ __('admin.topbar.search') }}</span>
                    </span>
                    <kbd class="hidden rounded-md border border-a-border bg-white px-1.5 text-[11px] sm:inline" dir="ltr">Ctrl K</kbd>
                </button>

                <div class="ms-auto flex items-center gap-1 sm:gap-4">
                    <a href="{{ url('/') }}" target="_blank" rel="noopener" class="hidden text-[13.5px] font-semibold underline underline-offset-4 md:inline">{{ __('admin.topbar.view_site') }}</a>

                    <form method="POST" action="{{ route('admin.locale', $locale === 'ar' ? 'en' : 'ar') }}" class="hidden sm:block">
                        @csrf
                        <button type="submit" class="text-[13.5px] font-semibold underline underline-offset-4" lang="{{ $locale === 'ar' ? 'en' : 'ar' }}">{{ __('admin.topbar.switch_language') }}</button>
                    </form>

                    <livewire:admin.notifications-menu />

                    <div x-data="{ open: false }" class="relative border-s border-a-border ps-3 sm:ps-4" @click.outside="open = false">
                        <button type="button" @click="open = !open" :aria-expanded="open" class="flex items-center gap-3">
                            <span class="hidden text-end leading-tight sm:block">
                                <span class="block text-[14px] font-bold text-[var(--c-deep)]">{{ $user->name }}</span>
                                <span class="block text-[12px] text-[var(--a-muted)]">{{ __('admin.roles.'.($user->getRoleNames()->first() ?? 'editor')) }}</span>
                            </span>
                            <span class="flex h-10 w-10 items-center justify-center rounded-full bg-[var(--c-navy)] text-[14px] font-bold text-white">{{ $user->initials() }}</span>
                        </button>
                        <div x-cloak x-show="open" x-transition class="a-popover end-0 top-12">
                            <a href="{{ route('admin.account') }}" wire:navigate class="a-menu-item">{{ __('admin.topbar.my_account') }}</a>
                            <a href="{{ route('admin.account') }}#two-factor" wire:navigate class="a-menu-item">{{ __('admin.topbar.two_factor') }}</a>
                            <form method="POST" action="{{ route('admin.locale', $locale === 'ar' ? 'en' : 'ar') }}" class="sm:hidden">
                                @csrf
                                <button type="submit" class="a-menu-item">{{ __('admin.topbar.switch_language') }}</button>
                            </form>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="a-menu-item text-[var(--a-danger)]">{{ __('admin.topbar.logout') }}</button>
                            </form>
                        </div>
                    </div>
                </div>
            </header>

            @if ($user->force_two_factor && ! $user->hasTwoFactorEnabled() && ! request()->routeIs('admin.account'))
                <div class="border-b border-amber-200 bg-amber-50 px-4 py-2.5 text-[13.5px] text-amber-900 lg:px-8">
                    {{ __('admin.account.forced_banner') }}
                    <a href="{{ route('admin.account') }}#two-factor" wire:navigate class="ms-2 font-bold underline">{{ __('admin.account.setup_now') }}</a>
                </div>
            @elseif ($user->hasRole('admin') && ! $user->hasTwoFactorEnabled() && request()->routeIs('admin.dashboard'))
                <div class="border-b border-sky-200 bg-sky-50 px-4 py-2.5 text-[13.5px] text-[var(--c-deep)] lg:px-8">
                    {{ __('admin.account.admin_banner') }}
                    <a href="{{ route('admin.account') }}#two-factor" wire:navigate class="ms-2 font-bold underline">{{ __('admin.account.setup_now') }}</a>
                </div>
            @endif

            <main id="main" class="flex-1 px-4 py-6 lg:px-8 lg:py-8">
                {{ $slot }}
            </main>
        </div>
    </div>

    @if (session('toast'))
        <div hidden data-cs-toast data-type="{{ session('toast.type', 'success') }}" data-message="{{ session('toast.message') }}"></div>
    @endif

    <livewire:admin.command-palette />
    <livewire:admin.asset-picker />

    @livewireScriptConfig
    @stack('scripts')
</body>
</html>
