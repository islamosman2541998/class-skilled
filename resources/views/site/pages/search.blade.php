@extends('layouts.site')

@section('content')
    @include('site.partials.page-header', ['title' => __('search.title'), 'crumbs' => [__('search.title') => null]])

    <section class="section !pt-10">
        <div class="container-site max-w-3xl">
            <livewire:site.search-page />
        </div>
    </section>
@endsection
