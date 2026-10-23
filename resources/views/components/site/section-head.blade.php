@props(['section', 'arrows' => false, 'link' => true])
@if ($section['title'] !== '' || $section['subtitle'] !== '' || $arrows)
    <div class="section-head" data-reveal="fade-up">
        <div class="max-w-2xl">
            @if ($section['title'] !== '')
                <h2 id="sec-{{ $section['id'] }}-title" class="t-section">{{ $section['title'] }}</h2>
            @endif
            @if ($section['subtitle'] !== '')
                <p class="t-lead mt-3">{{ $section['subtitle'] }}</p>
            @endif
        </div>
        <div class="flex items-center gap-5">
            @if ($link && $section['cta'])
                <a href="{{ $section['cta']['url'] }}" class="text-link" @if ($section['cta']['target_blank']) target="_blank" rel="noopener" @endif>{{ $section['cta']['label'] }}</a>
            @endif
            @if ($arrows)
                <div class="hidden gap-2 sm:flex">
                    <button type="button" class="glass-btn" x-ref="prev" aria-label="{{ __('slider.previous') }}"><x-heroicon-m-arrow-left class="flip size-5" aria-hidden="true" /></button>
                    <button type="button" class="glass-btn" x-ref="next" aria-label="{{ __('slider.next') }}"><x-heroicon-m-arrow-right class="flip size-5" aria-hidden="true" /></button>
                </div>
            @endif
        </div>
    </div>
@endif
