<div @class(['container-site' => ! ($bare ?? false), 'flex flex-col items-start justify-between gap-6 lg:flex-row lg:items-center']) data-reveal="stagger">
    <div class="max-w-2xl">
        <h2 id="{{ $headingId }}" class="t-section">{{ $cta['title'] }}</h2>
        @if ($cta['subtitle'] !== '')
            <p class="t-lead mt-3">{{ $cta['subtitle'] }}</p>
        @endif
    </div>
    <div class="flex flex-wrap gap-3">
        @if ($cta['primary'])
            <a href="{{ $cta['primary']['url'] }}" class="btn btn-primary" @if ($cta['primary']['target_blank']) target="_blank" rel="noopener" @endif>{{ $cta['primary']['label'] }}</a>
        @endif
        @if ($cta['secondary'])
            <a href="{{ $cta['secondary']['url'] }}" class="btn btn-ghost-light" @if ($cta['secondary']['target_blank'] || str_starts_with($cta['secondary']['url'], 'https://wa.me')) target="_blank" rel="noopener" @endif>{{ $cta['secondary']['label'] }}</a>
        @endif
    </div>
</div>
