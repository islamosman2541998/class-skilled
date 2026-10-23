@php($locale = app()->getLocale())
<div>
    <x-admin.page-header :title="__('admin.nav.request_statuses')" :subtitle="__('admin.requests.statuses_subtitle')"
        :breadcrumbs="[__('admin.nav.requests') => null, __('admin.nav.request_statuses') => null]">
        <x-slot:actions>
            <button type="button" class="a-btn a-btn-primary" wire:click="create">{{ __('admin.requests.add_status') }}</button>
        </x-slot:actions>
    </x-admin.page-header>

    <nav class="mb-6 flex gap-2" role="tablist">
        @foreach (\App\Enums\RequestType::cases() as $case)
            <button type="button" role="tab" class="a-chip" aria-selected="{{ $type === $case->value ? 'true' : 'false' }}" aria-pressed="{{ $type === $case->value ? 'true' : 'false' }}" wire:click="setType('{{ $case->value }}')">{{ __('admin.requests.types.'.$case->value) }}</button>
        @endforeach
    </nav>

    <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_380px]">
        <div class="a-panel overflow-x-auto">
            <table class="a-table">
                <thead>
                    <tr>
                        <th class="w-10"><span class="sr-only">{{ __('admin.common.drag') }}</span></th>
                        <th>{{ __('admin.requests.status_name') }}</th>
                        <th>{{ __('admin.requests.flags') }}</th>
                        <th>{{ __('admin.requests.in_use') }}</th>
                        <th><span class="sr-only">{{ __('admin.common.actions') }}</span></th>
                    </tr>
                </thead>
                <tbody x-sortable.handle="reorder" wire:key="statuses-{{ $type }}">
                    @foreach ($this->statuses as $status)
                        <tr wire:key="status-{{ $status->id }}" data-sort-id="{{ $status->id }}">
                            <td><button type="button" data-sort-handle class="a-drag-handle" aria-label="{{ __('admin.common.drag') }}"><x-heroicon-m-bars-2 class="h-4 w-4" /></button></td>
                            <td>
                                <x-admin.badge :color="$status->color->value">{{ $status->getTranslation('name', $locale) }}</x-admin.badge>
                                @if (blank($status->getTranslation('name', 'en', false)))
                                    <span class="ms-2 text-[12px] text-[#b54708]">{{ __('admin.common.missing_english') }}</span>
                                @endif
                            </td>
                            <td class="text-[13px]">
                                @if ($status->is_default)<x-admin.badge color="sky" :dot="false">{{ __('admin.requests.is_default') }}</x-admin.badge>@endif
                                @if ($status->is_final)<x-admin.badge color="gray" :dot="false">{{ __('admin.requests.is_final') }}</x-admin.badge>@endif
                            </td>
                            <td class="text-[13px] text-[var(--a-muted)]"><bdi>{{ $this->usage[$status->id] ?? 0 }}</bdi></td>
                            <td class="whitespace-nowrap text-end">
                                <button type="button" class="a-link me-3" wire:click="edit({{ $status->id }})">{{ __('admin.common.edit') }}</button>
                                @if (($this->usage[$status->id] ?? 0) === 0 && ! $status->is_default)
                                    <button type="button" class="a-link-danger"
                                            @click="$confirm({ title: @js(__('admin.common.confirm_delete_title')), text: @js(__('admin.common.confirm_delete_text', ['name' => $status->getTranslation('name', $locale)])), confirmText: @js(__('admin.common.yes_delete')), danger: true }).then(ok => ok && $wire.delete({{ $status->id }}))">
                                        {{ __('admin.common.delete') }}
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <aside>
            @if ($editingId !== null)
                <x-admin.panel :title="$editingId ? __('admin.requests.edit_status') : __('admin.requests.add_status')">
                    <form wire:submit="save" class="space-y-4">
                        <x-admin.pair model="form.name" :label="__('admin.requests.status_name')" required />
                        <fieldset>
                            <legend class="a-label">{{ __('admin.requests.color') }}</legend>
                            <div class="mt-1 flex flex-wrap gap-2">
                                @foreach (\App\Enums\StatusColor::cases() as $color)
                                    <label class="cursor-pointer">
                                        <input type="radio" class="peer sr-only" wire:model="form.color" value="{{ $color->value }}">
                                        <span class="inline-flex rounded-full p-0.5 ring-2 ring-transparent peer-checked:ring-[var(--c-navy)] peer-focus-visible:ring-[var(--c-sky)]">
                                            <x-admin.badge :color="$color->value">{{ $color->label() }}</x-admin.badge>
                                        </span>
                                    </label>
                                @endforeach
                            </div>
                        </fieldset>
                        <x-admin.toggle model="form.is_default" :label="__('admin.requests.is_default')" :hint="__('admin.requests.is_default_hint')" />
                        <x-admin.toggle model="form.is_final" :label="__('admin.requests.is_final')" :hint="__('admin.requests.is_final_hint')" />
                        <div class="flex justify-end gap-2">
                            <button type="button" class="a-btn a-btn-secondary" wire:click="cancel">{{ __('admin.common.cancel') }}</button>
                            <button type="submit" class="a-btn a-btn-primary">{{ __('admin.common.save') }}</button>
                        </div>
                    </form>
                </x-admin.panel>
            @else
                <div class="a-panel p-6 text-center text-[13.5px] text-[var(--a-muted)]">{{ __('admin.requests.statuses_hint') }}</div>
            @endif
        </aside>
    </div>
</div>
