@php($items = array_values(array_filter($block['items'], fn ($item) => $item['title'] !== '')))
@if ($items !== [])
    <div>
        @if ($block['title'] !== '')
            <h2 class="t-section mb-6">{{ $block['title'] }}</h2>
        @endif
        <div class="grid gap-x-10 gap-y-7 sm:grid-cols-2" data-reveal="stagger">
            @foreach ($items as $item)
                <div class="border-t-2 border-sky pt-4">
                    <h3 class="t-card">{{ $item['title'] }}</h3>
                    @if ($item['text'] !== '')
                        <p class="mt-2 text-muted">{{ $item['text'] }}</p>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
@endif
