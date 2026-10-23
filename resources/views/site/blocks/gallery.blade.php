{{-- lg:grid-cols-2 lg:grid-cols-3 lg:grid-cols-4 --}}
@if ($block['assets']->isNotEmpty())
    <div>
        @if ($block['title'] !== '')
            <h2 class="t-section mb-6">{{ $block['title'] }}</h2>
        @endif
        @include('site.partials.image-grid', ['images' => $block['assets'], 'columns' => max(2, min(4, $block['columns'])), 'limit' => null])
    </div>
@endif
