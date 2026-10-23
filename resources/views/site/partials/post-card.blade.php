@php($cover = $post->firstAsset('cover'))
<article @class(['group relative flex h-full overflow-hidden rounded-[20px] border border-line bg-white transition hover:border-sky/60', 'flex-col' => ! ($wide ?? false), 'flex-col lg:flex-row' => $wide ?? false])>
    @if ($cover)
        <div @class(['overflow-hidden bg-paper', 'aspect-[16/10]' => ! ($wide ?? false), 'aspect-[16/10] lg:aspect-auto lg:w-[55%] lg:shrink-0' => $wide ?? false])>
            <x-site.picture :asset="$cover" :sizes="($wide ?? false) ? '(min-width: 1024px) 660px, 100vw' : '(min-width: 1024px) 380px, (min-width: 640px) 45vw, 100vw'" class="size-full object-cover transition duration-300 group-hover:scale-[1.04]" />
        </div>
    @endif
    <div @class(['flex flex-1 flex-col gap-2 p-5', 'lg:justify-center lg:p-10' => $wide ?? false])>
        <p class="t-small flex flex-wrap gap-x-3 text-muted">
            @if ($wide ?? false)
                <span class="font-bold text-navy">{{ __('posts.featured') }}</span>
            @endif
            @if ($post->category)
                <span>{{ $post->category->name }}</span>
            @endif
            @if ($post->published_at)
                <time datetime="{{ $post->published_at->toDateString() }}">{{ $post->published_at->locale(app()->getLocale())->translatedFormat('j F Y') }}</time>
            @endif
        </p>
        <h3 @class(['t-card text-navy', 'lg:text-[21px]' => $wide ?? false])><a href="{{ $post->localizedUrl() }}" class="stretched">{{ $post->title }}</a></h3>
        @if (($wide ?? false) && filled($post->excerpt))
            <p class="text-muted">{{ $post->excerpt }}</p>
        @endif
    </div>
</article>
