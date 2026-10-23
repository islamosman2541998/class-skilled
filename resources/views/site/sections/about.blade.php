<x-site.section :section="$section">
    <div class="container-site grid items-center gap-10 lg:grid-cols-2 lg:gap-16">
        <div data-reveal="fade-up">
            @if ($section['title'] !== '')
                <h2 id="sec-{{ $section['id'] }}-title" class="t-section">{{ $section['title'] }}</h2>
            @endif
            @if ($section['subtitle'] !== '')
                <p class="t-lead mt-4">{{ $section['subtitle'] }}</p>
            @endif
            @if ($section['text'] !== '')
                <p class="mt-4 max-w-[70ch]">{{ $section['text'] }}</p>
            @endif
            @if ($section['meta'] !== [])
                <dl class="mt-6 grid gap-x-8 gap-y-4 sm:grid-cols-2">
                    @foreach ($section['meta'] as $meta)
                        <div class="border-s-2 border-sky ps-4">
                            <dt class="t-small text-muted">{{ $meta['label'] }}</dt>
                            <dd class="font-bold text-navy">{{ $meta['value'] }}</dd>
                        </div>
                    @endforeach
                </dl>
            @endif
            @if ($section['cta'])
                <a href="{{ $section['cta']['url'] }}" class="text-link mt-7" @if ($section['cta']['target_blank']) target="_blank" rel="noopener" @endif>{{ $section['cta']['label'] }}</a>
            @endif
        </div>
        @if ($section['images'] !== [])
            <div @class(['grid gap-4', 'grid-cols-[1.25fr_1fr] items-end' => count($section['images']) > 1])>
                @foreach (array_slice($section['images'], 0, 2) as $image)
                    <div @class(['rounded-[14px]', 'aspect-[4/5]' => $loop->first, 'aspect-[4/5] -mb-0 lg:mb-[-2rem]' => ! $loop->first]) data-reveal="image">
                        <x-site.picture :asset="$image" sizes="(min-width: 1024px) 30vw, 50vw" class="size-full rounded-[14px] object-cover" />
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</x-site.section>
