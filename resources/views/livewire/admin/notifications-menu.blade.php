<div x-data="{ open: false }" class="relative" @click.outside="open = false" @keydown.escape="open = false">
    <button type="button" class="flex items-center gap-2 rounded-lg px-2 py-1.5 hover:bg-a-bg" :aria-expanded="open"
            @click="open = ! open; if (open) $wire.load()">
        @if ($this->count > 0)
            <span class="flex h-5 min-w-5 items-center justify-center rounded-full bg-[var(--a-danger)] px-1 text-[11px] font-bold text-white">{{ $this->count }}</span>
        @endif
        <span class="hidden text-[13.5px] font-semibold sm:inline">{{ __('admin.topbar.notifications') }}</span>
        <x-heroicon-o-bell class="h-5 w-5 sm:hidden" />
        <span class="sr-only sm:hidden">{{ __('admin.topbar.notifications') }}</span>
    </button>
    <div x-cloak x-show="open" x-transition class="a-popover end-0 top-11 w-80 max-w-[90vw]">
        <div wire:loading.delay class="space-y-2 p-3">
            <span class="a-skeleton"></span><span class="a-skeleton w-2/3"></span>
        </div>
        <div wire:loading.remove>
            @forelse ($this->items as $item)
                @if ($item['url'])
                    <a href="{{ $item['url'] }}" wire:navigate class="a-menu-item flex-col items-start gap-0.5">
                @else
                    <div class="a-menu-item flex-col items-start gap-0.5">
                @endif
                    <span class="text-[12px] text-[var(--a-muted)]">{{ $item['label'] }} · {{ $item['time'] }}</span>
                    <span class="font-semibold">{{ $item['title'] }}</span>
                @if ($item['url'])</a>@else</div>@endif
            @empty
                <p class="px-3 py-4 text-center text-[13.5px] text-[var(--a-muted)]">{{ __('admin.topbar.no_notifications') }}</p>
            @endforelse
        </div>
    </div>
</div>
