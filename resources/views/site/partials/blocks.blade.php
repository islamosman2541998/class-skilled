{{-- Content blocks prepared by App\Support\Blocks\Blocks::prepare(); one view per block type. --}}
<div class="space-y-12 lg:space-y-16">
    @foreach ($blocks as $block)
        @includeIf($block['view'], ['block' => $block['data'], 'blockId' => $block['id']])
    @endforeach
</div>
