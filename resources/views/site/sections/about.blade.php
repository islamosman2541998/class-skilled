<x-site.section :section="$section">
    <div @class(['container-site grid items-center gap-10 lg:gap-16', 'lg:grid-cols-2' => $section['image']])>
        <div data-reveal="fade-up">
            @if ($section['title'] !== '')
                <h2 id="sec-{{ $section['id'] }}-title" class="t-section">{{ $section['title'] }}</h2>
            @endif
            @if ($section['subtitle'] !== '')
                <p class="t-lead mt-4 !max-w-none">{{ $section['subtitle'] }}</p>
            @endif
            @if ($section['text'] !== '')
                <p class="mt-4 whitespace-pre-line">{{ $section['text'] }}</p>
            @endif
            @if ($section['cta'])
                <a href="{{ $section['cta']['url'] }}" class="text-link mt-7" @if ($section['cta']['target_blank']) target="_blank" rel="noopener" @endif>{{ $section['cta']['label'] }}</a>
            @endif
        </div>
        @if ($section['image'])
            <div class="overflow-hidden rounded-[14px]" data-reveal="image">
                <x-site.picture :asset="$section['image']" sizes="(min-width: 1024px) 600px, 100vw" class="aspect-[4/3] w-full object-cover" />
            </div>
        @endif
    </div>
</x-site.section>
