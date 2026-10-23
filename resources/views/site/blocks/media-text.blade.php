<div @class(['grid items-center gap-8 lg:grid-cols-2 lg:gap-12'])>
    @if ($block['asset'])
        <div @class(['overflow-hidden rounded-[14px]', 'lg:order-last' => $block['image_side'] === 'end']) data-reveal="image">
            <x-site.picture :asset="$block['asset']" sizes="(min-width: 1024px) 40vw, 100vw" class="aspect-[4/3] w-full object-cover" />
        </div>
    @endif
    <div>
        @if ($block['title'] !== '')
            <h2 class="t-section">{{ $block['title'] }}</h2>
        @endif
        @if (trim(strip_tags($block['html'])) !== '')
            <div class="prose-site mt-4">{!! $block['html'] !!}</div>
        @endif
    </div>
</div>
