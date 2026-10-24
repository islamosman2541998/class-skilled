@php($cta = $site->ctaBand())
@if ($cta['title'] !== '')
    {{-- A navy card on a light band, so it reads as its own section above the footer. --}}
    <section class="cta-band" aria-labelledby="cta-band-title">
        <div class="container-site">
            <div class="cta-card on-dark">
                @include('site.partials.cta-content', ['cta' => $cta, 'headingId' => 'cta-band-title', 'bare' => true])
            </div>
        </div>
    </section>
@endif
