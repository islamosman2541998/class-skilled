@extends('layouts.site')

@if (($sections[0]['type'] ?? null) === 'hero')
    @section('header_overlay', '1')
@endif

@section('hide_cta', collect($sections)->contains('type', 'cta') ? '1' : '')

@section('content')
    @unless (($sections[0]['type'] ?? null) === 'hero' && $sections[0]['slides'][0]['title'] !== '')
        <h1 class="sr-only">{{ $site->name() }}</h1>
    @endunless
    @foreach ($sections as $section)
        @includeIf($section['view'], ['section' => $section, 'first' => $loop->first])
    @endforeach
@endsection
