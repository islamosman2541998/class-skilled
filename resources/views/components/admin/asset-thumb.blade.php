{{--
    Small preview of a library asset (image, video/embed poster, PDF tile).
    The whole image is shown (no cropping) on a light checkerboard, so logos and
    transparent PNGs stay readable.
    <x-admin.asset-thumb :asset="$asset" class="aspect-[4/3]" />
--}}
@props(['asset'])
@php
    $kind = $asset->kind->value;
    $thumb = match ($kind) {
        'image' => $asset->url('sm'),
        'video', 'embed' => $asset->poster?->url('sm'),
        default => null,
    };
@endphp
<span {{ $attributes->merge(['class' => 'a-thumb relative flex items-center justify-center overflow-hidden rounded-lg']) }}>
    @if ($thumb)
        <img src="{{ $thumb }}" alt="{{ $asset->altText() }}" loading="lazy" decoding="async" class="h-full w-full object-contain">
    @else
        <span class="flex flex-col items-center gap-1 text-[var(--a-muted)]">
            <x-heroicon-o-document-text class="h-8 w-8" />
            <span class="text-[11px] font-bold uppercase" dir="ltr">{{ $kind === 'document' ? 'PDF' : $kind }}</span>
        </span>
    @endif
    @if (in_array($kind, ['video', 'embed'], true))
        <span class="absolute inset-0 m-auto flex h-9 w-9 items-center justify-center rounded-full bg-black/55 text-white">
            <x-heroicon-s-play class="h-4 w-4" />
        </span>
    @endif
</span>
