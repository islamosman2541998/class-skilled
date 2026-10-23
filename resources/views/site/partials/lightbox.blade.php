{{-- Lightbox dialog for the enclosing x-data="lightbox"; $items = list of {type, src, alt, caption, poster?}. --}}
<script type="application/json" x-ref="items">@json($items)</script>
<template x-teleport="body">
    <div
        x-show="shown"
        x-cloak
        x-transition.opacity.duration.200ms
        @keydown.window="key($event)"
        @touchstart.passive="touchStart($event)"
        @touchend.passive="touchEnd($event)"
        class="fixed inset-0 z-[80] flex flex-col bg-[#0d1525]/95 text-white"
        role="dialog"
        aria-modal="true"
        aria-label="{{ __('lightbox.label') }}"
    >
        <div class="flex h-16 shrink-0 items-center justify-between px-4 lg:px-8">
            <p class="t-small text-white/80" aria-live="polite" x-text="counter(@js(__('lightbox.counter')))"></p>
            <button type="button" class="glass-btn" @click="close()" data-lightbox-close aria-label="{{ __('lightbox.close') }}">
                <x-heroicon-o-x-mark class="size-5" aria-hidden="true" />
            </button>
        </div>
        <div class="relative flex min-h-0 flex-1 items-center justify-center px-4 lg:px-20" @click.self="close()">
            <template x-if="current.type === 'image'">
                <img :src="current.src" :alt="current.alt" class="max-h-full max-w-full rounded-[10px] object-contain">
            </template>
            <template x-if="current.type === 'video' && shown">
                <video :src="current.src" :poster="current.poster" controls autoplay playsinline class="max-h-full max-w-full rounded-[10px]"></video>
            </template>
            <template x-if="current.type === 'embed' && shown">
                <div class="aspect-video w-full max-w-5xl">
                    <iframe :src="current.src" class="size-full rounded-[10px]" allow="autoplay; encrypted-media; picture-in-picture; fullscreen" allowfullscreen :title="current.alt"></iframe>
                </div>
            </template>
            <template x-if="items.length > 1">
                <div>
                    <button type="button" class="glass-btn absolute start-3 top-1/2 -translate-y-1/2 lg:start-6" @click="step(-1)" aria-label="{{ __('lightbox.previous') }}"><x-heroicon-m-arrow-left class="flip size-5" aria-hidden="true" /></button>
                    <button type="button" class="glass-btn absolute end-3 top-1/2 -translate-y-1/2 lg:end-6" @click="step(1)" aria-label="{{ __('lightbox.next') }}"><x-heroicon-m-arrow-right class="flip size-5" aria-hidden="true" /></button>
                </div>
            </template>
        </div>
        <p class="min-h-14 shrink-0 px-4 py-4 text-center text-[14px] text-white/85" x-text="current.caption"></p>
    </div>
</template>
