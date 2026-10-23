@if ($block['text'] !== '')
    <figure class="border-s-4 border-sky ps-6">
        <blockquote class="font-heading text-[19px] leading-relaxed font-bold text-navy lg:text-[21px]">
            <p>{{ $block['text'] }}</p>
        </blockquote>
        @if ($block['author'] !== '')
            <figcaption class="t-small mt-3 text-muted">{{ $block['author'] }}</figcaption>
        @endif
    </figure>
@endif
