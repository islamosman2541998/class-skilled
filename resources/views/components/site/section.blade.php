@props(['section', 'default' => null])
{{-- Wrapper for a home section: background variant (white / paper / dark / dark photo) from the dashboard. --}}
@php
    $bg = $section['background'] ?? $default ?? 'white';
    $photo = $bg === 'dark_photo' && ($section['bg_image'] ?? null) ? $section['bg_image']->url('lg') : null;
    $photoSmall = $photo ? $section['bg_image']->url('sm') : null;
    $bg = $bg === 'dark_photo' && ! $photo ? 'dark' : $bg;
    $dark = in_array($bg, ['dark', 'dark_photo'], true);
@endphp
<section
    id="{{ str_replace('_', '-', $section['type']) }}-{{ $section['id'] }}"
    {{ $attributes->class([
        'section',
        'section--paper' => $bg === 'paper',
        'section--dark' => $bg === 'dark',
        'section--dark-photo' => $bg === 'dark_photo',
        'on-dark' => $dark,
        'on-light' => ! $dark,
    ]) }}
    @if ($photo) data-reveal="bg" style="--section-bg: url('{{ $photo }}'); --section-bg-sm: url('{{ $photoSmall }}')" @endif
    @if ($section['title'] !== '') aria-labelledby="sec-{{ $section['id'] }}-title" @endif
    data-section="{{ $section['type'] }}"
>
    {{ $slot }}
</section>
