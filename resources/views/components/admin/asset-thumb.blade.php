{{--
    Small preview of a library asset (image thumb, video/embed poster, PDF tile).
    <x-admin.asset-thumb :asset="$asset" class="aspect-[4/3]" />
--}}
@props(['asset'])
@php
    $kind = $asset->kind->value;
    $thumb = match ($kind) {
        'image' => $asset->url('thumb'),
        'video', 'embed' => $asset->poster?->url('thumb'),
        default => null,
    };
@endphp
<span {{ $attributes->merge(['class' => 'relative flex items-center justify-center overflow-hidden rounded-lg bg-[#eef1f5]']) }}>
    @if ($thumb)
        <img src="{{ $thumb }}" alt="{{ $asset->altText() }}" loading="lazy" class="h-full w-full object-cover">
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
    @if ($kind === 'image' && blank($asset->getTranslation('alt', 'ar', false)))
        <span class="absolute bottom-1 start-1 rounded bg-amber-500 px-1.5 text-[10px] font-bold text-white">{{ __('admin.media.no_alt') }}</span>
    @endif
</span>
