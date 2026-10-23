{{-- Publish panel. Optional $extra = view name with more side fields. --}}
<x-admin.panel :title="__('admin.content.publish_panel')">
    <div class="space-y-5">
        @if ($publishable ?? true)
            <div>
                <x-admin.select model="status" live :label="__('admin.common.status')" :options="['draft' => __('enums.publish_status.draft'), 'published' => __('enums.publish_status.published')]" :placeholder="false" />
                @if ($this->displayStatus() === 'scheduled')
                    <p class="mt-2"><x-admin.badge color="violet">{{ __('enums.publish_status.scheduled') }}</x-admin.badge></p>
                @endif
            </div>
            <x-admin.date model="published_at" time :label="__('admin.content.published_at')" :hint="__('admin.content.published_at_hint')" />
        @endif
        @isset($extra)
            @include($extra)
        @endisset
        <div class="flex items-center justify-between border-t border-a-border pt-4 text-[13px]">
            <span class="text-[var(--a-muted)]">{{ __('admin.common.translation') }}</span>
            <x-admin.badge :color="$this->englishMissing() ? 'amber' : 'green'">{{ $this->englishMissing() ? __('admin.common.translation_missing') : __('admin.common.translation_complete') }}</x-admin.badge>
        </div>
        @if ($this->record())
            @can('delete', $this->record())
                <button type="button" class="a-link-danger text-[13.5px]"
                        @click="$confirm({ title: @js(__('admin.common.confirm_delete_title')), text: @js(__('admin.common.confirm_delete_text', ['name' => \App\Support\Translate::pick($form[$this->titleField() ?? 'title'] ?? [])])), danger: true, confirmText: @js(__('admin.common.yes_delete')) }).then(ok => ok && $wire.delete())">
                    {{ __('admin.common.delete') }}
                </button>
            @endcan
        @endif
    </div>
</x-admin.panel>
