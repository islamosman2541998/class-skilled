@extends('layouts.site')

@php
    $title = $category?->name ?? __('projects.title');
    $crumbs = $category
        ? [__('projects.title') => \App\Support\LocalizedRoute::url('projects.index'), $category->name => null]
        : [__('projects.title') => null];
@endphp

@section('content')
    @include('site.partials.page-header', ['title' => $title, 'lead' => __('projects.lead'), 'crumbs' => $crumbs])

    <section class="section !pt-10">
        <div class="container-site">
            <livewire:site.projects-index :category-id="$category?->id" />
        </div>
    </section>
@endsection
