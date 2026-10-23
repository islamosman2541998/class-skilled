{{-- Item side panel. --}}
@php($type = $item['type'])
<div class="a-panel xl:sticky xl:top-20" x-data="dirtyGuard(@js(__('admin.common.unsaved_changes')))" wire:key="edit-{{ $editing }}">
    <div class="a-panel-header">
        <h2 class="a-panel-title">{{ __('admin.menus.edit_item') }}</h2>
        <button type="button" class="rounded-lg p-1.5 hover:bg-a-bg" wire:click="closeItem" aria-label="{{ __('admin.common.close') }}"><x-heroicon-o-x-mark class="h-5 w-5" /></button>
    </div>
    <form wire:submit="saveItem" class="max-h-[calc(100vh-12rem)] space-y-5 overflow-y-auto p-5">
        <p><x-admin.badge color="navy">{{ \App\Enums\MenuItemType::from($type)->label() }}</x-admin.badge></p>

        <x-admin.pair model="item.label" :label="__('admin.menus.label')" :required="in_array($type, ['external', 'heading'], true)"
            :hint="in_array($type, ['external', 'heading'], true) ? null : __('admin.menus.label_hint')" />

        @if ($type === 'system_page')
            <x-admin.select model="item.system_route" :label="__('admin.link.route')" :options="collect($this->options['routes'])->except('home')->all()" :placeholder="false" />
        @endif

        @if (in_array($type, ['page', 'model'], true))
            @php($targets = collect($this->options['models'])->filter(fn ($g, $t) => $type === 'page' ? $t === 'page' : $t !== 'page')->flatMap(fn ($g, $t) => collect($g['items'])->mapWithKeys(fn ($label, $id) => [$t.':'.$id => $g['label'].' — '.$label]))->all())
            <x-admin.select model="item.linkable" searchable :label="__('admin.link.item')" :options="$targets" />
        @endif

        @if ($type === 'external')
            <x-admin.input model="item.url" type="url" :label="__('admin.link.url')" required />
        @endif

        @if ($type === 'dynamic_group')
            <x-admin.select model="item.dynamic_source" live :label="__('admin.menus.source')" :options="\App\Enums\DynamicSource::options()" :placeholder="false" />
            <x-admin.segmented model="item.dynamic_mode" live :label="__('admin.menus.mode')" :options="\App\Enums\DataSource::options()" />
            @if ($item['dynamic_mode'] === 'manual')
                <div>
                    <p class="a-label">{{ __('admin.menus.manual_picks') }}</p>
                    <div class="flex gap-2">
                        <select wire:model="item.entry_pick" class="a-input" aria-label="{{ __('admin.menus.manual_picks') }}">
                            <option value="">{{ __('admin.common.choose') }}</option>
                            @foreach ($this->entryOptions() as $id => $label)
                                @unless (in_array((int) $id, $item['entries'], true))
                                    <option value="{{ $id }}">{{ $label }}</option>
                                @endunless
                            @endforeach
                        </select>
                        <button type="button" class="a-btn a-btn-secondary" wire:click="addEntry">{{ __('admin.common.add') }}</button>
                    </div>
                    <ul class="mt-3 space-y-1.5" x-data x-sortable.handle="sortEntries">
                        @foreach ($item['entries'] as $entryId)
                            <li data-sort-id="{{ $entryId }}" wire:key="entry-{{ $entryId }}" class="flex items-center gap-2 rounded-lg border border-a-border px-3 py-2 text-[13.5px]">
                                <button type="button" data-sort-handle class="a-drag-handle" aria-label="{{ __('admin.common.drag') }}"><x-heroicon-m-bars-2 class="h-4 w-4" /></button>
                                <span class="flex-1 truncate">{{ $this->entryOptions()[$entryId] ?? '#'.$entryId }}</span>
                                <button type="button" class="text-[var(--a-danger)]" wire:click="removeEntry({{ $entryId }})" aria-label="{{ __('admin.common.remove') }}"><x-heroicon-m-x-mark class="h-4 w-4" /></button>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @else
                <x-admin.input model="item.dynamic_limit" type="number" min="1" max="30" :label="__('admin.menus.limit')" />
            @endif
            <x-admin.select model="item.system_route" :label="__('admin.menus.own_link')" :options="$this->options['routes']" :placeholder="__('admin.common.none')" />
            <x-admin.toggle model="item.show_all_link" :label="__('admin.menus.show_all_link')" />
            <x-admin.toggle model="item.show_descriptions" :label="__('admin.menus.show_descriptions')" />
        @endif

        @unless ($type === 'heading')
            <x-admin.toggle model="item.target_blank" :label="__('admin.link.new_tab')" />
        @endunless
        <x-admin.segmented model="item.visibility" :label="__('admin.menus.visibility')" :options="\App\Enums\Visibility::options()" />
        <x-admin.toggle model="item.is_active" :label="__('admin.common.active')" />

        <div class="sticky bottom-0 -mx-5 -mb-5 border-t border-a-border bg-white px-5 py-3">
            <button type="submit" class="a-btn a-btn-primary w-full">{{ __('admin.home.apply') }}</button>
        </div>
    </form>
</div>
