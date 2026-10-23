{{-- Before/after comparison: native range input (keyboard + touch accessible) moves the divider. --}}
<figure x-data="{ pos: 50 }" class="relative aspect-[4/3] select-none overflow-hidden rounded-[14px] bg-paper">
    <x-site.picture :asset="$after" sizes="(min-width: 1024px) 800px, 100vw" class="absolute inset-0 size-full object-cover" />
    <div class="absolute inset-0" :style="document.documentElement.dir === 'rtl' ? `clip-path: inset(0 0 0 ${100 - pos}%)` : `clip-path: inset(0 ${100 - pos}% 0 0)`" style="clip-path: inset(0 50% 0 0)">
        <x-site.picture :asset="$before" sizes="(min-width: 1024px) 800px, 100vw" class="absolute inset-0 size-full object-cover" />
    </div>
    <span class="pointer-events-none absolute inset-y-0 w-0.5 -translate-x-1/2 bg-white shadow rtl:translate-x-1/2" :style="`inset-inline-start: ${pos}%`" style="inset-inline-start: 50%" aria-hidden="true">
        <span class="absolute top-1/2 left-1/2 grid size-10 -translate-1/2 place-items-center rounded-full bg-white text-navy shadow-lg">
            <x-heroicon-m-chevron-up-down class="size-5 rotate-90" />
        </span>
    </span>
    <span class="pointer-events-none absolute start-3 top-3 rounded-full bg-deep/75 px-3 py-1 text-[12.5px] font-bold text-white">{{ __('lightbox.before') }}</span>
    <span class="pointer-events-none absolute end-3 top-3 rounded-full bg-deep/75 px-3 py-1 text-[12.5px] font-bold text-white">{{ __('lightbox.after') }}</span>
    <input type="range" min="0" max="100" step="1" x-model.number="pos" class="absolute inset-0 size-full cursor-ew-resize opacity-0" aria-label="{{ __('lightbox.compare') }}">
</figure>
