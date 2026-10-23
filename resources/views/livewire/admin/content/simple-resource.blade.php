<div>
    <x-admin.page-header :title="__($this->titleKey())" :subtitle="__($this->titleKey().'_hint')" :breadcrumbs="[__('admin.nav.content') => null, __($this->titleKey()) => null]">
        <x-slot:actions>
            <button type="button" wire:click="export" class="a-btn a-btn-secondary">{{ __('admin.common.export') }}</button>
            @can('create', $this->modelClassName())
                <button type="button" wire:click="create" class="a-btn a-btn-primary">{{ __('admin.content.add_item', ['module' => __($this->singularKey())]) }}</button>
            @endcan
        </x-slot:actions>
    </x-admin.page-header>

    @php($rows = $this->rows)
    <div class="a-panel">
        <div class="flex flex-wrap items-center gap-2 border-b border-a-border p-4">
            <x-admin.table.search />
            @foreach ($this->filterChips() as [$key, $label, $options])
                <x-admin.table.filter :key="$key" :label="$label" :options="$options" :value="$filters[$key] ?? null" />
            @endforeach
            <x-admin.table.filter key="missing" :label="__('admin.common.translation')" :options="['1' => __('admin.common.missing_english')]" :value="$filters['missing'] ?? null" />
            @if ($filters || $search)
                <button type="button" wire:click="clearFilters" class="text-[13.5px] font-bold text-[var(--c-deep)] hover:underline">{{ __('admin.common.clear_filters') }}</button>
            @endif
            @if ($this->canReorder())
                <span class="ms-auto text-[12.5px] text-[var(--a-muted)]">{{ __('admin.content.drag_hint') }}</span>
            @endif
        </div>

        <x-admin.table.bulk-bar :count="$this->selectedCount()" :total="$rows->total()" :all="$selectAllMatching">
            <button type="button" class="a-btn a-btn-secondary a-btn-sm" wire:click="exportSelected">{{ __('admin.common.export_selected') }}</button>
            <button type="button" class="a-link-danger px-2 text-[13.5px]"
                    @click="$confirm({ title: @js(__('admin.common.confirm_bulk_delete_title')), danger: true, confirmText: @js(__('admin.common.yes_delete')) }).then(ok => ok && $wire.bulkDelete())">{{ __('admin.common.delete') }}</button>
        </x-admin.table.bulk-bar>

        <div class="overflow-x-auto">
            <table class="a-table">
                <thead>
                    <tr>
                        <th class="w-10"><input type="checkbox" class="a-check" aria-label="{{ __('admin.common.select_page') }}" wire:click="{{ count($selected) && count($selected) >= $rows->count() ? 'clearSelection' : 'selectPage' }}" @checked($rows->count() && count($selected) >= $rows->count())></th>
                        <th class="w-8"></th>
                        @foreach ($this->columns() as $column)
                            <th>{{ __($column['label']) }}</th>
                        @endforeach
                        <th>{{ __('admin.common.translation') }}</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody @if ($this->canReorder()) x-sortable.handle="reorder" @endif wire:loading.class="opacity-60">
                    @forelse ($rows as $row)
                        <tr wire:key="row-{{ $row->id }}" data-sort-id="{{ $row->id }}">
                            <td><input type="checkbox" class="a-check" value="{{ $row->id }}" wire:model.live="selected" aria-label="{{ $this->rowTitle($row) }}"></td>
                            <td>@if ($this->canReorder())<button type="button" data-sort-handle class="a-drag-handle" aria-label="{{ __('admin.common.drag') }}"><x-heroicon-m-bars-2 class="h-4 w-4" /></button>@endif</td>
                            @foreach ($this->columns() as $column)
                                <td @class(['max-w-md' => ($column['type'] ?? '') === 'title'])>
                                    @switch($column['type'] ?? 'text')
                                        @case('title')
                                            <div class="flex items-center gap-3">
                                                @if (isset($column['asset']) && ($asset = $row->firstAsset($column['asset'])))
                                                    <x-admin.asset-thumb :asset="$asset" class="h-10 w-12 shrink-0" />
                                                @endif
                                                <button type="button" class="text-start font-bold text-[var(--c-deep)] hover:underline" wire:click="edit({{ $row->id }})">{{ $this->rowTitle($row) }}</button>
                                            </div>
                                            @break
                                        @case('toggle')
                                            <button type="button" role="switch" class="a-toggle" aria-checked="{{ $row->{$column['key']} ? 'true' : 'false' }}" wire:click="toggle({{ $row->id }}, '{{ $column['key'] }}')" aria-label="{{ __($column['label']) }}"></button>
                                            @break
                                        @case('html')
                                            {!! $this->cell($row, $column['key']) !!}
                                            @break
                                        @default
                                            <span class="text-[13.5px]">{{ $this->cell($row, $column['key']) }}</span>
                                    @endswitch
                                </td>
                            @endforeach
                            <td><x-admin.badge :color="$this->englishMissing($row) ? 'amber' : 'green'">{{ $this->englishMissing($row) ? __('admin.common.translation_missing') : __('admin.common.translation_complete') }}</x-admin.badge></td>
                            <td class="whitespace-nowrap text-end">
                                @can('update', $row)<button type="button" class="a-link me-3" wire:click="edit({{ $row->id }})">{{ __('admin.common.edit') }}</button>@endcan
                                @can('delete', $row)
                                    <button type="button" class="a-link-danger" @click="$confirm({ title: @js(__('admin.common.confirm_delete_title')), text: @js(__('admin.common.confirm_delete_text', ['name' => $this->rowTitle($row)])), danger: true, confirmText: @js(__('admin.common.yes_delete')) }).then(ok => ok && $wire.delete({{ $row->id }}))">{{ __('admin.common.delete') }}</button>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="{{ count($this->columns()) + 4 }}"><x-admin.empty-state :title="__('admin.common.empty')" /></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <x-admin.table.pagination :paginator="$rows" />
    </div>

    <x-admin.modal name="resource-form" :title="$editingId ? __('admin.content.edit_item', ['module' => __($this->singularKey())]) : __('admin.content.add_item', ['module' => __($this->singularKey())])" width="max-w-2xl">
        <form id="resource-form" wire:submit="save" class="space-y-5">
            @foreach ($this->fields() as $field)
                @php($model = 'form.'.$field['key'])
                @switch($field['type'])
                    @case('pair')
                        <x-admin.pair :model="$model" :label="__($field['label'])" :required="$field['required'] ?? false" :hint="isset($field['hint']) ? __($field['hint']) : null" />
                        @break
                    @case('pair_textarea')
                        <x-admin.pair :model="$model" :label="__($field['label'])" :required="$field['required'] ?? false" textarea :rows="$field['rows'] ?? 3" />
                        @break
                    @case('slug')
                        <x-admin.pair :model="$model" :label="__('validation.attributes.slug')" :hint="__('admin.content.slug_hint_short')" />
                        @break
                    @case('toggle')
                        <x-admin.toggle :model="$model" :label="__($field['label'])" />
                        @break
                    @case('select')
                        <x-admin.select :model="$model" :label="__($field['label'])" :options="$field['options']" :required="$field['required'] ?? false" />
                        @break
                    @case('date')
                        <x-admin.date :model="$model" :label="__($field['label'])" />
                        @break
                    @case('asset')
                        <livewire:admin.asset-field wire:model="{{ $model }}" :kind="$field['kind'] ?? 'image'" :label="__($field['label'])" :required="$field['required'] ?? false" :error="$errors->first($model)" wire:key="sr-{{ $field['key'] }}-{{ $editingId ?? 'new' }}" />
                        @break
                    @case('rating')
                        <x-admin.select :model="$model" :label="__($field['label'])" :options="[1 => '1', 2 => '2', 3 => '3', 4 => '4', 5 => '5']" />
                        @break
                    @default
                        <x-admin.input :model="$model" :type="$field['type'] === 'number' ? 'number' : ($field['type'] === 'url' ? 'url' : 'text')" :label="__($field['label'])" :required="$field['required'] ?? false" :dir="($field['ltr'] ?? false) ? 'ltr' : null" />
                @endswitch
            @endforeach
        </form>
        <x-slot:footer>
            <button type="button" class="a-btn a-btn-secondary" x-on:click="$dispatch('close-modal', 'resource-form')">{{ __('admin.common.cancel') }}</button>
            <button type="submit" form="resource-form" class="a-btn a-btn-primary">{{ __('admin.common.save') }}</button>
        </x-slot:footer>
    </x-admin.modal>
</div>
