<x-site.section :section="$section">
    @include('site.partials.cta-content', ['cta' => $section, 'headingId' => 'sec-'.$section['id'].'-title'])
</x-site.section>
