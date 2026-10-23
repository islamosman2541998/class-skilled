@php
    $title = __('errors.'.$code.'_title');
    $text = \Illuminate\Support\Facades\Lang::has('errors.'.$code.'_text') ? __('errors.'.$code.'_text') : __('errors.404_text');
    $links = array_values(array_filter(app(\App\Services\Menus\MenuResolver::class)->forLocation(\App\Enums\MenuLocation::Header), fn ($node) => $node->url !== null));
    $seo->title($title);
@endphp
<section class="section">
    <div class="container-site max-w-3xl text-center">
        <p class="font-heading text-[56px] leading-none font-extrabold text-sky lg:text-[72px]"><bdi dir="ltr">{{ $code }}</bdi></p>
        <h1 class="t-page mt-4">{{ $title }}</h1>
        <p class="t-lead mx-auto mt-3">{{ $text }}</p>

        @if (in_array($code, [404, 410], true))
            <form action="{{ \App\Support\LocalizedRoute::url('search') }}" method="get" role="search" class="mx-auto mt-8 flex max-w-md gap-2">
                <label for="error-search" class="sr-only">{{ __('site.search') }}</label>
                <input id="error-search" type="search" name="q" placeholder="{{ __('site.search_placeholder') }}" class="h-12 min-w-0 flex-1 rounded-[12px] border border-line bg-white px-4 text-ink focus:border-sky focus:outline-none">
                <button type="submit" class="btn btn-primary">{{ __('site.search') }}</button>
            </form>
        @endif

        <div class="mt-8 flex flex-wrap justify-center gap-3">
            <a href="{{ $site->homeUrl() }}" class="btn btn-secondary">{{ __('site.back_home') }}</a>
        </div>

        @if ($links !== [])
            <nav class="mt-10 border-t border-line pt-6" aria-label="{{ __('errors.useful_links') }}">
                <h2 class="t-small font-bold text-muted">{{ __('errors.useful_links') }}</h2>
                <ul class="mt-3 flex flex-wrap justify-center gap-x-6 gap-y-2">
                    @foreach ($links as $node)
                        <li><a href="{{ $node->url }}" class="text-link">{{ $node->label }}</a></li>
                    @endforeach
                </ul>
            </nav>
        @endif
    </div>
</section>
