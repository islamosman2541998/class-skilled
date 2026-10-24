{{-- Same look as the CTA band above the footer: a navy card on the light band, so it never merges with the footer. --}}
<x-site.section :section="[...$section, 'background' => 'paper']">
    <div class="container-site">
        <div class="cta-card on-dark">
            @include('site.partials.cta-content', ['cta' => $section, 'headingId' => 'sec-'.$section['id'].'-title', 'bare' => true])
        </div>
    </div>
</x-site.section>
