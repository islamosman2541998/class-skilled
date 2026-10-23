<x-site.section :section="$section">
    <div class="container-site">
        <x-site.section-head :section="$section" />
        {{-- Stored HTML is sanitised with Purifier on save. --}}
        <div class="prose-site" data-reveal="fade-up">{!! $section['html'] !!}</div>
    </div>
</x-site.section>
