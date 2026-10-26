{{--
    Drag & drop uploader (Alpine `assetUploader`) for Livewire components using WithAssetUploads.
    Per-file progress, type/size checks from MediaSettings, video poster capture.
    <x-admin.uploader :kind="null" on-finished="uploadsFinished" />
    `on-finished` is a Livewire method called when the queue is done.
--}}
@props(['kind' => null, 'onFinished' => null])
@php($media = app(\App\Settings\MediaSettings::class))
<div {{ $attributes }}
     x-data="assetUploader({
        maxImageMb: {{ $media->max_image_mb }},
        maxVideoMb: {{ $media->max_video_mb }},
        accept: @js((array) ($kind ?? [])),
        messages: { type: @js(__('admin.media.unsupported_type')), size: @js(__('admin.media.too_large')), failed: @js(__('admin.media.upload_failed')) },
     })"
     @if ($onFinished) @uploads-finished="$wire.{{ $onFinished }}()" @endif
     @cs-upload-files.window="add($event.detail)">
    <label @dragover.prevent="dragging = true" @dragleave.prevent="dragging = false" @drop.prevent="drop($event)"
           class="flex cursor-pointer flex-col items-center justify-center rounded-2xl border-2 border-dashed px-6 py-8 text-center transition"
           :class="dragging ? 'border-[var(--c-sky)] bg-sky-50' : 'border-a-border bg-[#fafbfc]'">
        <x-heroicon-o-arrow-up-tray class="h-8 w-8 text-[var(--a-muted)]" />
        <span class="mt-3 font-semibold">{{ __('admin.media.drop_here') }} <span class="text-[var(--c-navy)] underline">{{ __('admin.media.browse') }}</span></span>
        <span class="mt-1 text-[12.5px] text-[var(--a-muted)]">{{ __('admin.media.allowed', ['image' => $media->max_image_mb, 'video' => $media->max_video_mb]) }}</span>
        <input type="file" class="sr-only" multiple accept="image/jpeg,image/png,image/webp,video/mp4,video/webm,application/pdf" @change="add($event.target.files); $event.target.value = ''">
    </label>

    <template x-if="video">
        <div class="mt-5 rounded-xl border border-a-border p-4">
            <p class="font-bold">{{ __('admin.media.video_poster_title') }}: <span class="font-semibold" dir="auto" x-text="video.name"></span></p>
            <p class="text-[13px] text-[var(--a-muted)]">{{ __('admin.media.video_poster_text') }}</p>
            <video x-ref="video" :src="video.url" controls muted playsinline preload="auto" class="mt-3 max-h-72 w-full rounded-lg bg-black" @loadedmetadata="readDuration($event.target)" @loadeddata="markReady($event.target)"></video>
            <div class="mt-3 flex gap-2">
                <button type="button" class="a-btn a-btn-primary a-btn-sm" :disabled="!video.ready" @click="capturePoster($refs.video)">{{ __('admin.media.capture') }}</button>
                <button type="button" class="a-btn a-btn-secondary a-btn-sm" @click="skipPoster()">{{ __('admin.media.skip_poster') }}</button>
            </div>
        </div>
    </template>

    <ul class="mt-4 space-y-2" x-show="queue.length" x-cloak>
        <template x-for="item in queue" :key="item.id">
            <li class="rounded-xl border border-a-border px-4 py-3">
                <div class="flex items-center justify-between gap-3 text-[13.5px]">
                    <span class="truncate font-semibold" x-text="item.name"></span>
                    <span class="shrink-0 text-[12.5px]"
                          :class="item.status === 'error' ? 'text-[var(--a-danger)]' : (item.status === 'done' ? 'text-[var(--a-success)]' : 'text-[var(--a-muted)]')"
                          x-text="item.error ?? ({ waiting: @js(__('admin.media.waiting')), poster: @js(__('admin.media.video_poster_title')), uploading: @js(__('admin.media.uploading')), processing: @js(__('admin.media.processing')), done: @js(__('admin.media.done')) })[item.status]"></span>
                </div>
                <div class="mt-2 h-1.5 overflow-hidden rounded-full bg-a-bg" x-show="item.status !== 'error'">
                    <div class="h-full rounded-full bg-[var(--c-sky)] transition-all" :style="`width: ${item.progress}%`"></div>
                </div>
            </li>
        </template>
    </ul>
</div>
