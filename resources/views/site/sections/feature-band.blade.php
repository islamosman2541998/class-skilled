<x-site.section :section="$section">
    <div class="container-site grid items-center gap-10 lg:grid-cols-2 lg:gap-16">
        <div data-reveal="fade-up">
            @if ($section['title'] !== '')
                <h2 id="sec-{{ $section['id'] }}-title" class="t-section">{{ $section['title'] }}</h2>
            @endif
            @if ($section['subtitle'] !== '')
                <p class="t-lead mt-4">{{ $section['subtitle'] }}</p>
            @endif
            @if ($section['bullets'] !== [])
                <ul class="mt-6 grid gap-3 sm:grid-cols-2">
                    @foreach ($section['bullets'] as $bullet)
                        <li class="flex gap-3 border-t border-current/15 pt-3"><span class="mt-2.5 size-1.5 shrink-0 rounded-full bg-sky" aria-hidden="true"></span><span>{{ $bullet }}</span></li>
                    @endforeach
                </ul>
            @endif
            @if ($section['cta'])
                <a href="{{ $section['cta']['url'] }}" class="btn btn-primary mt-8" @if ($section['cta']['target_blank']) target="_blank" rel="noopener" @endif>{{ $section['cta']['label'] }}</a>
            @endif
        </div>
        @if ($section['images'] !== [])
            @php($images = array_slice($section['images'], 0, 3))
            <div @class(['grid gap-4', 'grid-cols-2 grid-rows-2' => count($images) === 3, 'grid-cols-2' => count($images) === 2])>
                @foreach ($images as $image)
                    <div @class([
                        'overflow-hidden rounded-[14px]',
                        'row-span-2 aspect-[3/4] h-full' => count($images) === 3 && $loop->first,
                        'aspect-[4/3]' => ! (count($images) === 3 && $loop->first),
                    ]) data-reveal="image">
                        <x-site.picture :asset="$image" sizes="(min-width: 1024px) 25vw, 50vw" class="size-full object-cover" />
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</x-site.section>
