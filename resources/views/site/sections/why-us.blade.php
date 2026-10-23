<x-site.section :section="$section">
    <div class="container-site">
        <x-site.section-head :section="$section" />
        <div class="grid gap-x-10 gap-y-8 sm:grid-cols-2" data-reveal="stagger">
            @foreach ($section['items'] as $item)
                <div class="border-t-2 border-sky pt-5" style="--i: {{ $loop->index }}">
                    <h3 class="t-card">{{ $item['title'] }}</h3>
                    @if ($item['text'] !== '')
                        <p class="mt-2 max-w-[60ch] text-muted in-[.on-dark]:text-[#C9D3E3]">{{ $item['text'] }}</p>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
</x-site.section>
