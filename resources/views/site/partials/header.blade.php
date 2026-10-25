@php($header = array_values(array_filter($menus['header'] ?? [], fn ($n) => $n->showOn('desktop'))))
<header
    id="site-header"
    @class(['site-header', 'is-overlay' => $overlay])
    data-header
>
    <div class="container-site flex h-full items-center justify-between gap-4">
        <a href="{{ $site->homeUrl() }}" class="flex shrink-0 items-center gap-2.5" aria-label="{{ $site->name() }} — {{ __('site.home') }}">
            @include('site.partials.brand-mark', ['light' => false, 'class' => 'logo-dark'])
            @if ($overlay)
                @include('site.partials.brand-mark', ['light' => true, 'class' => 'logo-light'])
            @endif
            <span class="leading-tight">
                <span class="block font-heading text-[15px] font-extrabold lg:text-base">{{ $site->name() }}</span>
                @if ($site->tagline() !== '')
                    <span class="block text-[12px] font-medium opacity-75">{{ $site->tagline() }}</span>
                @endif
            </span>
        </a>

        <nav class="hidden lg:block" aria-label="{{ __('site.main_navigation') }}">
            <ul class="flex items-center gap-0.5">
                @foreach ($header as $node)
                    <li class="relative" @if ($node->hasChildren()) x-data="navDropdown" @keydown.escape.window="close(true)" @mouseenter="enter()" @mouseleave="leave()" @focusout="onFocusOut($event)" @endif>
                        @if ($node->hasChildren())
                            @php($viewAll = $node->viewAll ?? ($node->url ? ['label' => __('site.view_all'), 'url' => $node->url] : null))
                            <button type="button" @class(['nav-link', 'is-active' => $node->active]) x-ref="trigger" @click="toggle()" @keydown.arrow-down.prevent="openAndFocus()" :aria-expanded="open.toString()" aria-expanded="false" aria-haspopup="true">
                                {{ $node->label }}
                                <x-heroicon-m-chevron-down class="size-4 opacity-70 transition" ::class="open && 'rotate-180'" aria-hidden="true" />
                            </button>
                            <div class="dropdown-panel" x-cloak x-show="open" x-transition.opacity.duration.150ms x-ref="panel" @keydown.arrow-down.prevent="move(1)" @keydown.arrow-up.prevent="move(-1)">
                                <ul class="dropdown-list">
                                    @foreach ($node->children as $child)
                                        @continue(! $child->url || ! $child->showOn('desktop'))
                                        <li>
                                            <a href="{{ $child->url }}" class="dropdown-link" data-dropdown-item @if ($child->active) aria-current="page" @endif @if ($child->newTab) target="_blank" rel="noopener" @endif>{{ $child->label }}</a>
                                        </li>
                                    @endforeach
                                </ul>
                                @if ($viewAll)
                                    <a href="{{ $viewAll['url'] }}" class="dropdown-all" data-dropdown-item>
                                        {{ $viewAll['label'] }}
                                        <x-heroicon-m-chevron-right class="size-4" aria-hidden="true" />
                                    </a>
                                @endif
                            </div>
                        @elseif ($node->url)
                            <a href="{{ $node->url }}" @class(['nav-link', 'is-active' => $node->active]) @if ($node->active) aria-current="page" @endif @if ($node->newTab) target="_blank" rel="noopener" @endif>{{ $node->label }}</a>
                        @else
                            <span class="nav-link">{{ $node->label }}</span>
                        @endif
                    </li>
                @endforeach
            </ul>
        </nav>

        <div class="flex items-center gap-2">
            <a href="{{ $site->quoteUrl() }}" class="btn btn-primary btn-sm hidden lg:inline-flex">{{ __('site.request_quote') }}</a>
            <button
                type="button"
                class="inline-grid size-11 place-items-center rounded-full lg:hidden"
                aria-controls="mobile-nav"
                aria-expanded="false"
                aria-label="{{ __('site.open_menu') }}"
                data-mobile-nav-open
            >
                <x-heroicon-o-bars-3 class="size-6" aria-hidden="true" />
            </button>
        </div>
    </div>
</header>
