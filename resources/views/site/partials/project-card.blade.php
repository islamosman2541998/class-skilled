@php
    $cover = $project->firstAsset('cover');
    $meta = collect([$project->category?->name, $project->location])->filter()->implode(app()->getLocale() === 'ar' ? "\u{060C} " : ', ');
@endphp
<article class="group relative flex h-full flex-col overflow-hidden rounded-[20px] border border-line bg-white transition hover:-translate-y-1 hover:border-sky/60 motion-reduce:hover:translate-y-0">
    <div class="aspect-[4/3] overflow-hidden bg-paper">
        @if ($cover)
            <x-site.picture :asset="$cover" sizes="(min-width: 1024px) 380px, (min-width: 640px) 45vw, 100vw" class="size-full object-cover transition duration-300 group-hover:scale-[1.04]" />
        @endif
    </div>
    <div class="flex flex-1 flex-col gap-1.5 p-5">
        <h3 class="t-card text-navy"><a href="{{ $project->localizedUrl() }}" class="stretched">{{ $project->title }}</a></h3>
        @if ($meta !== '')
            <p class="t-small text-muted">{{ $meta }}</p>
        @endif
    </div>
</article>
