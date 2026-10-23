@if ($block['asset'])
    <div>
        @if ($block['title'] !== '')
            <h2 class="t-section mb-6">{{ $block['title'] }}</h2>
        @endif
        @include('site.partials.video-player', ['video' => $block['asset']])
    </div>
@endif
