{{-- "Add item" panel with one tab per item kind. --}}
<x-admin.panel :title="__('admin.menus.add')">
    <div class="a-segmented mb-5 flex-wrap" role="tablist">
        @foreach (['pages', 'system', 'model', 'dynamic', 'external', 'heading'] as $tab)
            <button type="button" role="tab" aria-selected="{{ $addTab === $tab ? 'true' : 'false' }}" wire:click="$set('addTab', '{{ $tab }}')">{{ __('admin.menus.tabs.'.$tab) }}</button>
        @endforeach
    </div>

    @switch($addTab)
        @case('pages')
            @php($pages = $this->options['models']['page']['items'] ?? [])
            <div class="max-h-64 space-y-1 overflow-y-auto">
                @forelse ($pages as $id => $title)
                    <label class="a-menu-item cursor-pointer"><input type="checkbox" class="a-check" value="{{ $id }}" wire:model="pickPages"> {{ $title }}</label>
                @empty
                    <p class="text-[13px] text-[var(--a-muted)]">{{ __('admin.common.empty') }}</p>
                @endforelse
            </div>
            <button type="button" class="a-btn a-btn-primary a-btn-sm mt-4" wire:click="addPages">{{ __('admin.menus.add_selected') }}</button>
            @break

        @case('system')
            <div class="flex flex-wrap gap-2">
                @foreach ($this->options['routes'] as $route => $label)
                    <button type="button" class="a-chip" wire:click="addSystem('{{ $route }}')"><x-heroicon-m-plus class="h-4 w-4" /> {{ $label }}</button>
                @endforeach
            </div>
            @break

        @case('model')
            <input type="search" wire:model.live.debounce.250ms="search" class="a-input" placeholder="{{ __('admin.menus.search_items') }}" aria-label="{{ __('admin.menus.search_items') }}">
            <ul class="mt-3 max-h-72 space-y-1 overflow-y-auto">
                @foreach ($this->searchResults as $result)
                    <li wire:key="sr-{{ $result['type'] }}-{{ $result['id'] }}">
                        <button type="button" class="a-menu-item justify-between" wire:click="addModel('{{ $result['type'] }}', {{ $result['id'] }})">
                            <span class="truncate">{{ $result['label'] }}</span>
                            <span class="shrink-0 text-[12px] text-[var(--a-muted)]">{{ $result['group'] }}</span>
                        </button>
                    </li>
                @endforeach
            </ul>
            @break

        @case('dynamic')
            <p class="text-[13.5px] text-[var(--a-muted)]">{{ __('admin.menus.dynamic_hint') }}</p>
            <button type="button" class="a-btn a-btn-primary a-btn-sm mt-4" wire:click="addDynamic">{{ __('admin.menus.add_dynamic') }}</button>
            @break

        @case('external')
            <form wire:submit="addExternal" class="space-y-4">
                <x-admin.pair model="external.label" :label="__('admin.menus.label')" required />
                <x-admin.input model="external.url" type="url" :label="__('admin.link.url')" placeholder="https://" required />
                <button type="submit" class="a-btn a-btn-primary a-btn-sm">{{ __('admin.common.add') }}</button>
            </form>
            @break

        @case('heading')
            <form wire:submit="addHeading" class="space-y-4">
                <x-admin.pair model="heading" :label="__('admin.menus.label')" :hint="__('admin.menus.heading_hint')" required />
                <button type="submit" class="a-btn a-btn-primary a-btn-sm">{{ __('admin.common.add') }}</button>
            </form>
            @break
    @endswitch
</x-admin.panel>
