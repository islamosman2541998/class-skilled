@if (trim(strip_tags($block['html'])) !== '')
    {{-- Stored HTML is sanitised with Purifier on save. --}}
    <div class="prose-site">{!! $block['html'] !!}</div>
@endif
