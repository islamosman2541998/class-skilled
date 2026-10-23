@php($icons = \App\Services\FaviconGenerator::urls())
@foreach ($icons as $size => $url)
    @if ($size === 180)
        <link rel="apple-touch-icon" sizes="180x180" href="{{ $url }}">
    @else
        <link rel="icon" type="image/png" sizes="{{ $size }}x{{ $size }}" href="{{ $url }}">
    @endif
@endforeach
