<x-site.section :section="$section" class="!py-0">
    <div class="container-site">
        <dl class="grid grid-cols-2 divide-line border-b border-line lg:grid-cols-4 lg:divide-x rtl:lg:divide-x-reverse" data-reveal="stagger">
            @foreach ($section['items'] as $fact)
                <div class="border-line px-4 py-6 odd:border-e lg:border-0 lg:px-6 lg:py-8 [&:nth-child(-n+2)]:border-b lg:[&:nth-child(-n+2)]:border-b-0" style="--i: {{ $loop->index }}">
                    <dt class="t-small text-muted">{{ $fact['label'] }}</dt>
                    <dd class="mt-1 font-heading text-[17px] font-bold text-navy">{{ $fact['value'] }}</dd>
                </div>
            @endforeach
        </dl>
    </div>
</x-site.section>
