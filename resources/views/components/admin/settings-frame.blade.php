{{--
    Frame of a settings screen: header, group tabs and the save button
    (submits #settings-form). Used by App\Livewire\Admin\Settings\* views.
    <x-admin.settings-frame :group="$this->group()" :tabs="$this->tabs()"> … fields … </x-admin.settings-frame>
--}}
@props(['group', 'tabs' => [], 'saveable' => true])
<div x-data="dirtyGuard(@js(__('admin.common.unsaved_changes')))">
    <x-admin.page-header :title="__('admin.settings.groups.'.$group)" :subtitle="__('admin.settings.subtitles.'.$group)"
        :breadcrumbs="[__('admin.nav.settings') => null, __('admin.settings.groups.'.$group) => null]">
        @if ($saveable)
            <x-slot:actions>
                <button type="submit" form="settings-form" class="a-btn a-btn-primary" wire:loading.attr="disabled" wire:target="save">{{ __('admin.settings.save') }}</button>
            </x-slot:actions>
        @endif
    </x-admin.page-header>

    @if (count($tabs) > 1)
        <nav class="mb-6 flex gap-2 overflow-x-auto pb-1" aria-label="{{ __('admin.nav.settings') }}">
            @foreach ($tabs as $key => $url)
                <a href="{{ $url }}" wire:navigate class="a-chip shrink-0" aria-pressed="{{ $key === $group ? 'true' : 'false' }}" @if ($key === $group) aria-current="page" @endif>{{ __('admin.settings.groups.'.$key) }}</a>
            @endforeach
        </nav>
    @endif

    <form id="settings-form" wire:submit="save" class="space-y-6">
        {{ $slot }}
    </form>
</div>
