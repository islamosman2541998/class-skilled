<x-admin.settings-frame :group="$this->group()" :tabs="$this->tabs()">
    <x-admin.panel :title="__('admin.settings.sections.media_limits')">
        <div class="grid gap-5 md:grid-cols-2">
            <x-admin.input model="data.max_image_mb" type="number" :label="__('admin.settings.fields.max_image_mb')" />
            <x-admin.input model="data.max_video_mb" type="number" :label="__('admin.settings.fields.max_video_mb')" :hint="__('admin.settings.hints.max_video_mb')" />
            <x-admin.input model="data.max_image_dimension" type="number" :label="__('admin.settings.fields.max_image_dimension')" />
            <x-admin.input model="data.webp_quality" type="number" :label="__('admin.settings.fields.webp_quality')" />
        </div>
    </x-admin.panel>

    <x-admin.panel :title="__('admin.settings.sections.regenerate')" :subtitle="__('admin.settings.hints.regenerate')">
        @if ($regenerating)
            <div wire:poll.750ms="regenerateChunk">
                <p class="text-[14px] font-semibold">{{ __('admin.settings.regenerating', ['done' => $regenDone, 'total' => $regenTotal]) }}</p>
                <div class="mt-3 h-2 overflow-hidden rounded-full bg-a-bg">
                    <div class="h-full rounded-full bg-[var(--c-sky)] transition-all" style="width: {{ $regenTotal ? round($regenDone / $regenTotal * 100) : 0 }}%"></div>
                </div>
            </div>
        @else
            <button type="button" class="a-btn a-btn-secondary" wire:click="startRegeneration">{{ __('admin.settings.regenerate') }}</button>
        @endif
    </x-admin.panel>
</x-admin.settings-frame>
