@php
    $img = $asset;
    $set = $srcset($img);
@endphp
<picture>
    @if ($mobile && $mobile->isImage() && $mobile->file())
        <source media="(max-width: 639px)" type="image/webp" srcset="{{ $srcset($mobile) }}" sizes="100vw">
    @endif
    @if ($set !== '')
        <source type="image/webp" srcset="{{ $set }}" sizes="{{ $sizes }}">
    @endif
    <img
        src="{{ $img->url($fallback) }}"
        alt="{{ $alt ?? $img->altText() }}"
        @if ($img->width && $img->height) width="{{ $img->width }}" height="{{ $img->height }}" @endif
        loading="{{ $eager ? 'eager' : 'lazy' }}"
        decoding="{{ $eager ? 'sync' : 'async' }}"
        @if ($eager) fetchpriority="high" @endif
        style="object-position: {{ $objectPosition($img) }}"
        {{ $attributes->merge(['class' => $class]) }}
    >
</picture>
