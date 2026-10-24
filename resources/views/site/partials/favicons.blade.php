@php($icons = \App\Services\FaviconGenerator::urls())
<link rel="icon" href="{{ url('/favicon.ico') }}" sizes="48x48">
@foreach ($icons as $size => $url)
    @if ($size === 180)
        <link rel="apple-touch-icon" sizes="180x180" href="{{ $url }}">
    @else
        <link rel="icon" type="image/png" sizes="{{ $size }}x{{ $size }}" href="{{ $url }}">
    @endif
@endforeach
