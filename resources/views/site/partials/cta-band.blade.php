@php($cta = $site->ctaBand())
@if ($cta['title'] !== '')
    <section class="section section--dark on-dark" aria-labelledby="cta-band-title">
        @include('site.partials.cta-content', ['cta' => $cta, 'headingId' => 'cta-band-title'])
    </section>
@endif
