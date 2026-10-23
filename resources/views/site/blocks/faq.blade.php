@php($items = array_values(array_filter($block['items'], fn ($item) => $item['q'] !== '')))
@if ($items !== [])
    <div>
        @if ($block['title'] !== '')
            <h2 class="t-section mb-6">{{ $block['title'] }}</h2>
        @endif
        @include('site.partials.faq-list', ['items' => $items])
    </div>
@endif
