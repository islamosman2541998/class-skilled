@extends('layouts.site')

@php
    $title = $category?->name ?? __('posts.title');
    $crumbs = $category
        ? [__('posts.title') => \App\Support\LocalizedRoute::url('posts.index'), $category->name => null]
        : [__('posts.title') => null];
@endphp

@section('content')
    @include('site.partials.page-header', ['title' => $title, 'lead' => __('posts.lead'), 'crumbs' => $crumbs])

    <section class="section !pt-10">
        <div class="container-site">
            <livewire:site.posts-index :category-id="$category?->id" />
        </div>
    </section>
@endsection
