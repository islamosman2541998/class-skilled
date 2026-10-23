@extends('layouts.site')

@section('hide_cta', '1')

@section('content')
    @include('site.partials.page-header', ['title' => __('quote.title'), 'lead' => __('quote.lead'), 'crumbs' => [__('quote.title') => null]])

    <section class="section !pt-10">
        <div class="container-site">
            <livewire:site.quote-form />
        </div>
    </section>
@endsection
