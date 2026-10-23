@php($items = array_values(array_filter($block['items'], fn ($item) => $item['title'] !== '')))
@if ($items !== [])
    <div>
        @if ($block['title'] !== '')
            <h2 class="t-section mb-6">{{ $block['title'] }}</h2>
        @endif
        <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3" data-reveal="stagger">
            @foreach ($items as $item)
                <article class="overflow-hidden rounded-[20px] border border-line bg-white">
                    @if ($item['asset'])
                        <x-site.picture :asset="$item['asset']" sizes="(min-width: 1024px) 30vw, (min-width: 640px) 45vw, 100vw" class="aspect-[4/3] w-full object-cover" />
                    @endif
                    <div class="p-5">
                        <h3 class="t-card">{{ $item['title'] }}</h3>
                        @if ($item['text'] !== '')
                            <p class="mt-2 text-[14.5px] text-muted">{{ $item['text'] }}</p>
                        @endif
                    </div>
                </article>
            @endforeach
        </div>
    </div>
@endif
