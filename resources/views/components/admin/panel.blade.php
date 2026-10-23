{{--
    White panel with optional header.
    <x-admin.panel :title="__('…')" :subtitle="…"><x-slot:actions>…</x-slot:actions> content </x-admin.panel>
    Pass :padded="false" for tables that touch the edges.
--}}
@props(['title' => null, 'subtitle' => null, 'padded' => true])
<section {{ $attributes->merge(['class' => 'a-panel']) }}>
    @if ($title || isset($actions))
        <header class="a-panel-header">
            <div>
                @if ($title)<h2 class="a-panel-title">{{ $title }}</h2>@endif
                @if ($subtitle)<p class="mt-0.5 text-[13px] text-[var(--a-muted)]">{{ $subtitle }}</p>@endif
            </div>
            @isset($actions)<div class="flex items-center gap-2">{{ $actions }}</div>@endisset
        </header>
    @endif
    <div @class(['a-panel-body' => $padded])>{{ $slot }}</div>
</section>
