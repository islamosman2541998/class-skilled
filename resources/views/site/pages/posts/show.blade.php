@extends('layouts.site')

@php
    $cover = $post->firstAsset('cover');
    $url = $post->localizedUrl();
    $crumbs = [__('posts.title') => \App\Support\LocalizedRoute::url('posts.index')];
    if ($post->category && $post->category->localizedUrl()) {
        $crumbs[$post->category->name] = $post->category->localizedUrl();
    }
    $crumbs[$post->title] = null;
    $shares = [
        'whatsapp' => 'https://wa.me/?text='.rawurlencode($post->title.' '.$url),
        'linkedin' => 'https://www.linkedin.com/sharing/share-offsite/?url='.rawurlencode((string) $url),
        'x' => 'https://x.com/intent/post?url='.rawurlencode((string) $url).'&text='.rawurlencode($post->title),
    ];
@endphp

@section('content')
    @include('site.partials.page-header', ['title' => $post->title, 'lead' => $post->excerpt, 'crumbs' => $crumbs])

    <article class="section !pt-10">
        <div class="container-site max-w-[860px]">
            <p class="t-small flex flex-wrap gap-x-4 gap-y-1 text-muted">
                @if ($post->category)
                    <span class="font-bold text-navy">{{ $post->category->name }}</span>
                @endif
                @if ($post->published_at)
                    <time datetime="{{ $post->published_at->toDateString() }}">{{ __('posts.published_on', ['date' => $post->published_at->locale(app()->getLocale())->translatedFormat('j F Y')]) }}</time>
                @endif
                @if ($post->reading_minutes > 0)
                    <span>{{ __('site.minutes_read', ['count' => $post->reading_minutes]) }}</span>
                @endif
            </p>

            @if ($cover)
                <x-site.picture :asset="$cover" sizes="(min-width: 900px) 860px, 100vw" :eager="true" class="mt-6 aspect-[16/9] w-full rounded-[14px] object-cover" />
            @endif

            {{-- Body is sanitised with Purifier on save. --}}
            <div class="prose-site mt-8 !max-w-none">{!! $post->body !!}</div>

            <div class="mt-10 flex flex-wrap items-center gap-3 border-t border-line pt-6" x-data="{ copied: false }">
                <p class="font-bold text-navy">{{ __('posts.share_title') }}</p>
                <button type="button" class="chip" @click="navigator.clipboard?.writeText(@js($url)).then(() => { copied = true; window.csToast?.('success', @js(__('site.link_copied'))) })">{{ __('site.copy_link') }}</button>
                @foreach ($shares as $network => $shareUrl)
                    <a href="{{ $shareUrl }}" target="_blank" rel="noopener" class="chip">{{ __($network === 'whatsapp' ? 'site.whatsapp' : 'site.social_'.$network) }}</a>
                @endforeach
            </div>
        </div>
    </article>

    @if ($related->isNotEmpty())
        @include('site.sections.posts', ['section' => [
            'id' => 'related', 'type' => 'posts', 'background' => 'paper', 'bg_image' => null,
            'title' => __('posts.related'), 'subtitle' => '', 'cta' => null, 'display' => 'slider', 'autoplay' => false,
            'items' => $related,
        ]])
    @endif
@endsection
