{{-- Image grid (or masonry) opening a lightbox. $images, $columns (2–4), $limit (null = all; else "+N" on the last tile), $masonry. --}}
{{-- lg:grid-cols-2 lg:grid-cols-3 lg:grid-cols-4 lg:columns-2 lg:columns-3 lg:columns-4 --}}
@php
    $images = collect($images)->filter(fn ($asset) => $asset->isImage())->values();
    $visible = $limit ? $images->take($limit) : $images;
    $more = $images->count() - $visible->count();
    $masonry = $masonry ?? false;
@endphp
@if ($images->isNotEmpty())
    <div x-data="lightbox">
        <ul @class([
            'grid grid-cols-2 gap-3 lg:gap-4 lg:grid-cols-'.$columns => ! $masonry,
            'columns-2 gap-3 lg:gap-4 lg:columns-'.$columns.' [&>li]:mb-3 lg:[&>li]:mb-4 [&>li]:break-inside-avoid' => $masonry,
        ]) data-reveal="stagger">
            @foreach ($visible as $i => $image)
                <li>
                    <button type="button" class="group relative block w-full overflow-hidden rounded-[14px] bg-paper" @click="open({{ $i }})" aria-label="{{ __('lightbox.label') }}: {{ $image->altText() }}" aria-haspopup="dialog">
                        <x-site.picture :asset="$image" sizes="(min-width: 1024px) {{ (int) (100 / $columns) }}vw, 50vw" @class(['w-full object-cover transition duration-300 group-hover:scale-[1.04]', 'aspect-[4/3]' => ! $masonry]) />
                        @if ($loop->last && $more > 0)
                            <span class="absolute inset-0 grid place-items-center bg-deep/65 font-heading text-[22px] font-extrabold text-white">{{ __('site.photos_more', ['count' => $more]) }}</span>
                        @endif
                    </button>
                </li>
            @endforeach
        </ul>
        @include('site.partials.lightbox', ['items' => \App\Support\Lightbox::items($images)])
    </div>
@endif
