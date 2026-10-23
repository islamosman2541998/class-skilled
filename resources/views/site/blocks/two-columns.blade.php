@php($columns = array_values(array_filter([$block['left'], $block['right']], fn ($c) => $c['title'] !== '' || trim(strip_tags($c['html'])) !== '')))
@if ($columns !== [])
    <div class="grid gap-5 lg:grid-cols-2">
        @foreach ($columns as $column)
            <div @class([
                'rounded-[20px] p-6 lg:p-8',
                'on-dark bg-navy text-[#E4EAF3] [&_.prose-site]:text-[#E4EAF3] [&_.prose-site_a]:text-white' => $column['dark'],
                'border border-line bg-white' => ! $column['dark'],
            ])>
                @if ($column['title'] !== '')
                    <h2 @class(['t-card', 'text-white' => $column['dark']])>{{ $column['title'] }}</h2>
                @endif
                <div class="prose-site mt-3">{!! $column['html'] !!}</div>
            </div>
        @endforeach
    </div>
@endif
