<div x-data="{ open: false }" class="relative" @click.outside="open = false" @keydown.escape="open = false">
    <button type="button" class="a-icon-btn relative" :aria-expanded="open" aria-haspopup="menu"
            @click="open = ! open; if (open) $wire.load()" title="{{ __('admin.topbar.notifications') }}">
        <x-heroicon-o-bell class="h-5 w-5" />
        @if ($this->count > 0)
            <span class="a-icon-badge"><bdi>{{ $this->count > 99 ? '99+' : $this->count }}</bdi></span>
        @endif
        <span class="sr-only">{{ __('admin.topbar.notifications') }}{{ $this->count > 0 ? ' ('.$this->count.')' : '' }}</span>
    </button>
    <div x-cloak x-show="open" x-transition.origin.top class="a-popover end-0 top-[52px] w-80 max-w-[90vw]" role="menu">
        <div class="flex items-center justify-between border-b border-a-border px-3 pt-1.5 pb-2.5">
            <span class="text-[14px] font-bold text-[var(--c-deep)]">{{ __('admin.topbar.notifications') }}</span>
            @if ($this->count > 0)
                <span class="a-badge a-badge-sky no-dot"><bdi>{{ $this->count }}</bdi></span>
            @endif
        </div>
        <div wire:loading.delay class="space-y-2 p-3">
            <span class="a-skeleton"></span><span class="a-skeleton w-2/3"></span>
        </div>
        <div wire:loading.remove class="max-h-96 overflow-y-auto pt-1">
            @forelse ($this->items as $item)
                @if ($item['url'])
                    <a href="{{ $item['url'] }}" wire:navigate class="a-menu-item flex-col items-start gap-0.5" role="menuitem">
                @else
                    <div class="a-menu-item flex-col items-start gap-0.5">
                @endif
                    <span class="text-[12px] text-[var(--a-muted)]">{{ $item['label'] }} · {{ $item['time'] }}</span>
                    <span class="font-semibold">{{ $item['title'] }}</span>
                @if ($item['url'])</a>@else</div>@endif
            @empty
                <div class="px-3 py-6 text-center">
                    <x-heroicon-o-bell-slash class="mx-auto h-7 w-7 text-[#c5cdd9]" />
                    <p class="mt-2 text-[13.5px] text-[var(--a-muted)]">{{ __('admin.topbar.no_notifications') }}</p>
                </div>
            @endforelse
        </div>
    </div>
</div>
