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
            <div class="flex h-[72px] items-center justify-between gap-3 border-b border-white/10 px-5">
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
            <header class="a-topbar">
                <button type="button" class="a-icon-btn lg:hidden" @click="drawer = true" aria-label="{{ __('admin.topbar.open_menu') }}">
                    <x-heroicon-o-bars-3 class="h-5 w-5" />
                </button>

                <button type="button" @click="$dispatch('open-command-palette')" class="a-search-pill" aria-keyshortcuts="Control+K">
                    <x-heroicon-o-magnifying-glass class="h-[18px] w-[18px] shrink-0 text-[var(--c-navy)]" />
                    <span class="min-w-0 flex-1 truncate">{{ __('admin.topbar.search') }}</span>
                    <span class="hidden items-center gap-1 sm:flex" dir="ltr" aria-hidden="true">
                        <kbd class="a-kbd">Ctrl</kbd><kbd class="a-kbd">K</kbd>
                    </span>
                </button>

                <div class="ms-auto flex items-center gap-2 sm:gap-3">
                    <a href="{{ url('/') }}" target="_blank" rel="noopener" class="a-topbar-link hidden md:inline-flex">
                        <x-heroicon-o-globe-alt class="h-[18px] w-[18px]" />
                        <span>{{ __('admin.topbar.view_site') }}</span>
                        <x-heroicon-m-arrow-up-right class="h-3.5 w-3.5 opacity-60 rtl:-scale-x-100" />
                    </a>

                    <form method="POST" action="{{ route('admin.locale', $locale === 'ar' ? 'en' : 'ar') }}" class="hidden sm:block">
                        @csrf
                        <button type="submit" class="a-lang-switch" aria-label="{{ __('admin.topbar.switch_language') }}" title="{{ __('admin.topbar.switch_language') }}">
                            <span @class(['is-active' => $locale === 'ar'])>ع</span>
                            <span @class(['is-active' => $locale === 'en'])>EN</span>
                        </button>
                    </form>

                    <livewire:admin.notifications-menu />

                    <span class="hidden h-8 w-px bg-a-border sm:block" aria-hidden="true"></span>

                    <div x-data="{ open: false }" class="relative" @click.outside="open = false" @keydown.escape="open = false">
                        <button type="button" @click="open = !open" :aria-expanded="open" aria-haspopup="menu" class="a-user-chip">
                            <span class="a-avatar">{{ $user->initials() }}</span>
                            <span class="hidden text-start leading-tight sm:block">
                                <span class="block max-w-[160px] truncate text-[14px] font-bold text-[var(--c-deep)]">{{ $user->name }}</span>
                                <span class="block text-[12px] text-[var(--a-muted)]">{{ __('admin.roles.'.($user->getRoleNames()->first() ?? 'editor')) }}</span>
                            </span>
                            <x-heroicon-m-chevron-down class="hidden h-4 w-4 text-[var(--a-muted)] transition-transform sm:block" x-bind:class="open && 'rotate-180'" />
                        </button>
                        <div x-cloak x-show="open" x-transition.origin.top class="a-popover end-0 top-[52px] w-64" role="menu">
                            <div class="flex items-center gap-3 border-b border-a-border px-3 pt-2 pb-3">
                                <span class="a-avatar">{{ $user->initials() }}</span>
                                <span class="min-w-0 leading-tight">
                                    <span class="block truncate text-[14px] font-bold text-[var(--c-deep)]">{{ $user->name }}</span>
                                    <span class="block truncate text-[12.5px] text-[var(--a-muted)]" dir="ltr">{{ $user->email }}</span>
                                </span>
                            </div>
                            <div class="pt-1.5">
                                <a href="{{ route('admin.account') }}" wire:navigate class="a-menu-item" role="menuitem">
                                    <x-heroicon-o-user-circle class="h-[18px] w-[18px] text-[var(--a-muted)]" />{{ __('admin.topbar.my_account') }}
                                </a>
                                <a href="{{ url('/') }}" target="_blank" rel="noopener" class="a-menu-item md:hidden" role="menuitem">
                                    <x-heroicon-o-globe-alt class="h-[18px] w-[18px] text-[var(--a-muted)]" />{{ __('admin.topbar.view_site') }}
                                </a>
                                <form method="POST" action="{{ route('admin.locale', $locale === 'ar' ? 'en' : 'ar') }}" class="sm:hidden">
                                    @csrf
                                    <button type="submit" class="a-menu-item" role="menuitem">
                                        <x-heroicon-o-language class="h-[18px] w-[18px] text-[var(--a-muted)]" />{{ __('admin.topbar.switch_language') }}
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('logout') }}" class="mt-1 border-t border-a-border pt-1">
                                    @csrf
                                    <button type="submit" class="a-menu-item text-[var(--a-danger)]" role="menuitem">
                                        <x-heroicon-o-arrow-right-start-on-rectangle class="h-[18px] w-[18px] rtl:-scale-x-100" />{{ __('admin.topbar.logout') }}
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

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
