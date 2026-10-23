<x-admin.panel :title="__('admin.dashboard.attention')">
    @if ($items === [])
        <p class="text-[13.5px] text-[var(--a-muted)]">{{ __('admin.dashboard.all_clear') }}</p>
    @else
        <ul class="divide-y divide-a-border">
            @foreach ($items as $item)
                <li>
                    <a href="{{ $item['url'] }}" wire:navigate class="flex items-center justify-between gap-3 py-2.5 text-[13.5px] hover:text-[var(--c-navy)]">
                        <span>{{ $item['label'] }}</span>
                        <x-admin.badge :color="$item['tone']" :dot="false"><bdi>{{ $item['count'] }}</bdi></x-admin.badge>
                    </a>
                </li>
            @endforeach
        </ul>
    @endif
    @if ($licences)
        <div class="mt-4 space-y-2 border-t border-a-border pt-4">
            @foreach ($licences as $licence)
                <div class="flex items-center justify-between gap-3 text-[13px]">
                    <span class="text-[var(--a-muted)]">{{ $licence['label'] }}</span>
                    <span @class(['font-bold', 'text-[var(--a-danger)]' => $licence['days'] <= 90, 'text-[var(--c-deep)]' => $licence['days'] > 90])><bdi dir="ltr">{{ $licence['date'] }}</bdi></span>
                </div>
            @endforeach
        </div>
    @endif
</x-admin.panel>
