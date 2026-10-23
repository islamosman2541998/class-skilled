<div x-data="{ open: false, active: 0 }"
     @open-command-palette.window="open = true; $nextTick(() => $refs.input.focus())"
     @keydown.escape.window="open = false">
        <div x-cloak x-show="open" x-transition.opacity class="a-modal-backdrop pt-[12vh]" @click.self="open = false">
            <div class="a-modal max-w-xl overflow-hidden" role="dialog" aria-modal="true" aria-label="{{ __('admin.topbar.search') }}" x-trap.noscroll="open">
                <div class="flex items-center gap-3 border-b border-a-border px-4">
                    <x-heroicon-o-magnifying-glass class="h-5 w-5 text-[var(--a-muted)]" />
                    <input x-ref="input" type="search" wire:model.live.debounce.250ms="query" class="h-14 flex-1 bg-transparent text-[15px] outline-none"
                           placeholder="{{ __('admin.palette.placeholder') }}" aria-label="{{ __('admin.palette.placeholder') }}"
                           @keydown.arrow-down.prevent="active = Math.min(active + 1, $refs.list.querySelectorAll('a').length - 1); $refs.list.querySelectorAll('a')[active]?.focus()"
                    >
                    <kbd class="rounded-md border border-a-border px-1.5 text-[11px] text-[var(--a-muted)]">Esc</kbd>
                </div>
                <div x-ref="list" class="max-h-[60vh] overflow-y-auto p-2" @keydown.arrow-down.prevent="$focus.wrap().next()" @keydown.arrow-up.prevent="$focus.wrap().previous()">
                    @if (mb_strlen(trim($query)) < 2)
                        <p class="px-3 py-6 text-center text-[13.5px] text-[var(--a-muted)]">{{ __('admin.palette.hint') }}</p>
                    @elseif ($this->results === [])
                        <p class="px-3 py-6 text-center text-[13.5px] text-[var(--a-muted)]">{{ __('admin.palette.empty') }}</p>
                    @else
                        @foreach ($this->results as $group => $items)
                            <p class="px-3 pb-1 pt-3 text-[12px] font-semibold text-[var(--a-muted)]">{{ __('admin.palette.groups.'.$group) }}</p>
                            @foreach ($items as $item)
                                <a href="{{ $item['url'] }}" wire:navigate @click="open = false" class="a-menu-item justify-between">
                                    <span class="truncate font-semibold">{{ $item['title'] }}</span>
                                    @if ($item['meta'])<span class="shrink-0 text-[12.5px] text-[var(--a-muted)]" dir="ltr">{{ $item['meta'] }}</span>@endif
                                </a>
                            @endforeach
                        @endforeach
                    @endif
                </div>
            </div>
        </div>
</div>
