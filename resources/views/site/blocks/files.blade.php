@if ($block['assets']->isNotEmpty())
    <div>
        @if ($block['title'] !== '')
            <h2 class="t-section mb-6">{{ $block['title'] }}</h2>
        @endif
        @include('site.partials.file-list', ['files' => $block['assets']])
    </div>
@endif
