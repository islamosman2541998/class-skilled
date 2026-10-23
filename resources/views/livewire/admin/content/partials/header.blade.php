{{-- Editor header: breadcrumbs, title, last update, Preview / Save draft / Publish. Vars: $listLabel, $listRoute, $record --}}
@php($record = $this->record())
<x-admin.page-header :title="$this->pageTitle()"
    :subtitle="$record?->updated_at ? __('admin.content.last_updated', ['date' => $record->updated_at->translatedFormat('j F Y H:i')]) : null"
    :breadcrumbs="[__('admin.nav.content') => null, $listLabel => route($listRoute), $this->pageTitle() => null]">
    <x-slot:actions>
        @if ($record)
            <div x-data="{ open: false }" class="relative" @click.outside="open = false">
                <button type="button" class="a-btn a-btn-secondary" @click="open = ! open">{{ __('admin.common.preview') }} <x-heroicon-m-chevron-down class="h-4 w-4" /></button>
                <div x-cloak x-show="open" class="a-popover end-0 top-11">
                    <a href="{{ $this->previewUrl('ar') }}" target="_blank" rel="noopener" class="a-menu-item">العربية</a>
                    <a href="{{ $this->previewUrl('en') }}" target="_blank" rel="noopener" class="a-menu-item">English</a>
                </div>
            </div>
        @endif
        @if ($publishable ?? true)
            <button type="button" class="a-btn a-btn-secondary" wire:click="saveDraft" wire:loading.attr="disabled">{{ __('admin.content.save_draft') }}</button>
            <button type="button" class="a-btn a-btn-primary" wire:click="publish" wire:loading.attr="disabled">{{ $record && $status === 'published' ? __('admin.content.update') : __('admin.content.publish') }}</button>
        @else
            <button type="button" class="a-btn a-btn-primary" wire:click="save" wire:loading.attr="disabled">{{ __('admin.common.save') }}</button>
        @endif
    </x-slot:actions>
</x-admin.page-header>
