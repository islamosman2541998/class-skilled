<div class="grid gap-4 sm:grid-cols-3">
    <x-admin.input model="section.data.autoplay" type="number" min="4" max="10" :label="__('admin.home.autoplay_seconds')" />
    <x-admin.toggle model="section.data.show_arrows" :label="__('admin.home.show_arrows')" />
    <x-admin.toggle model="section.data.show_progress" :label="__('admin.home.show_progress')" />
</div>

@php
    $slides = array_values((array) ($section['data']['slides'] ?? []));
    $thumbs = \App\Models\Asset::query()->with('media')->whereIn('id', collect($slides)->pluck('image_asset_id')->filter()->all())->get()->keyBy('id');
@endphp
<div>
    <div class="mb-2 flex items-center justify-between gap-3">
        <p class="a-label !mb-0">{{ __('admin.home.slides') }} <span class="font-normal text-[var(--a-muted)]">({{ count($slides) }})</span></p>
        <a href="{{ route('admin.home-sections.slides.create', $editing) }}" wire:navigate class="a-btn a-btn-primary a-btn-sm">
            <x-heroicon-m-plus class="h-4 w-4" />{{ __('admin.home.add_slide') }}
        </a>
    </div>

    @if ($slides === [])
        <p class="rounded-xl border border-dashed border-a-border p-5 text-center text-[13.5px] text-[var(--a-muted)]">{{ __('admin.home.no_slides') }}</p>
    @else
        <ul class="space-y-2" x-data x-sortable.handle="reorderSlides" wire:key="slides-{{ $editing }}-{{ md5(json_encode(array_column($slides, 'id'))) }}">
            @foreach ($slides as $i => $slide)
                @php($thumb = $thumbs->get($slide['image_asset_id'] ?? 0))
                <li data-sort-id="{{ $slide['id'] }}" wire:key="slide-row-{{ $slide['id'] }}" class="flex items-center gap-3 rounded-xl border border-a-border bg-white p-2 pe-3">
                    <button type="button" data-sort-handle class="a-drag-handle shrink-0" aria-label="{{ __('admin.common.drag') }}"><x-heroicon-m-bars-2 class="h-4 w-4" /></button>
                    <span class="a-thumb block h-12 w-16 shrink-0 overflow-hidden rounded-lg">
                        @if ($thumb)
                            <img src="{{ $thumb->url('thumb') }}" alt="" class="h-full w-full object-cover">
                        @endif
                    </span>
                    <span class="min-w-0 flex-1">
                        <span class="block truncate text-[14px] font-bold text-[var(--c-deep)]">{{ ($slide['title']['ar'] ?? '') ?: __('admin.home.slide_number', ['n' => $i + 1]) }}</span>
                        @if (blank($slide['title']['en'] ?? null))
                            <span class="text-[11.5px] text-[#b54708]">{{ __('admin.common.missing_english') }}</span>
                        @endif
                    </span>
                    <a href="{{ route('admin.home-sections.slides.edit', [$editing, $slide['id']]) }}" wire:navigate class="a-link shrink-0 text-[13px]">{{ __('admin.common.edit') }}</a>
                    <button type="button" class="a-link-danger shrink-0 text-[13px]"
                            @click="$confirm({ title: @js(__('admin.home.delete_slide_title')), danger: true, confirmText: @js(__('admin.common.yes_delete')) }).then(ok => ok && $wire.deleteSlide(@js($slide['id'])))">
                        {{ __('admin.common.delete') }}
                    </button>
                </li>
            @endforeach
        </ul>
    @endif
</div>
