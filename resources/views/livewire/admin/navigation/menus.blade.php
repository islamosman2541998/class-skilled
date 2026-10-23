<div>
    <x-admin.page-header :title="__('admin.nav.menus')" :subtitle="__('admin.menus.subtitle')" :breadcrumbs="[__('admin.nav.navigation') => null, __('admin.nav.menus') => null]">
        <x-slot:actions>
            <a href="{{ url('/') }}" target="_blank" rel="noopener" class="a-btn a-btn-secondary">{{ __('admin.menus.preview') }}</a>
        </x-slot:actions>
    </x-admin.page-header>

    <nav class="mb-6 flex gap-2 overflow-x-auto pb-1" role="tablist">
        @foreach ($locations as $loc)
            <button type="button" role="tab" class="a-chip shrink-0" aria-selected="{{ $location === $loc->value ? 'true' : 'false' }}" aria-pressed="{{ $location === $loc->value ? 'true' : 'false' }}" wire:click="$set('location', '{{ $loc->value }}')">{{ $loc->label() }}</button>
        @endforeach
    </nav>

    @php($menu = $this->menu)
    @php($roots = $menu->items->whereNull('parent_id')->sortBy('sort'))

    <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_420px]">
        <div class="space-y-6">
            <x-admin.panel :title="$menu->getTranslation('name', 'ar')" :subtitle="__('admin.menus.tree_hint', ['count' => $menu->items->count()])">
                @if ($location === 'mobile')
                    <div class="mb-4 flex items-center justify-between gap-4 rounded-xl border border-a-border bg-[#fafbfc] p-4">
                        <div>
                            <p class="font-bold">{{ __('admin.menus.use_header') }}</p>
                            <p class="text-[12.5px] text-[var(--a-muted)]">{{ __('admin.menus.use_header_hint') }}</p>
                        </div>
                        <button type="button" role="switch" class="a-toggle" aria-checked="{{ $menu->use_header_menu ? 'true' : 'false' }}" wire:click="toggleUseHeader" aria-label="{{ __('admin.menus.use_header') }}"></button>
                    </div>
                @endif

                @if ($location === 'mobile' && $menu->use_header_menu)
                    <p class="py-6 text-center text-[13.5px] text-[var(--a-muted)]">{{ __('admin.menus.mirrors_header') }}</p>
                @elseif ($roots->isEmpty())
                    <p class="py-6 text-center text-[13.5px] text-[var(--a-muted)]">{{ __('admin.menus.empty') }}</p>
                @else
                    <div x-data x-sortable-tree="saveTree" wire:key="tree-{{ $menu->id }}-{{ md5($menu->items->map(fn ($i) => $i->id.'.'.$i->parent_id.'.'.$i->sort)->implode(',')) }}">
                        <ul data-sort-list="root" class="space-y-2">
                            @foreach ($roots as $root)
                                @php($children = $menu->items->where('parent_id', $root->id)->sortBy('sort'))
                                <li data-sort-id="{{ $root->id }}" wire:key="mi-{{ $root->id }}">
                                    @include('livewire.admin.navigation.partials.item-row', ['row' => $root])
                                    @if ($root->type !== \App\Enums\MenuItemType::DynamicGroup)
                                        <ul data-sort-list="{{ $root->id }}" class="mt-2 min-h-2 space-y-2 ps-8">
                                            @foreach ($children as $child)
                                                <li data-sort-id="{{ $child->id }}" wire:key="mi-{{ $child->id }}">
                                                    @include('livewire.admin.navigation.partials.item-row', ['row' => $child])
                                                </li>
                                            @endforeach
                                        </ul>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form wire:submit="saveMenu" class="mt-6 grid items-end gap-3 border-t border-a-border pt-5 sm:grid-cols-[minmax(0,1fr)_auto]">
                    <x-admin.pair model="menuName" :label="__('admin.menus.name')" :hint="str_starts_with($location, 'footer') ? __('admin.menus.name_hint') : null" required />
                    <button type="submit" class="a-btn a-btn-secondary">{{ __('admin.common.save') }}</button>
                </form>
            </x-admin.panel>

            @unless ($location === 'mobile' && $menu->use_header_menu)
                @can('create', \App\Models\MenuItem::class)
                    @include('livewire.admin.navigation.partials.add-panel')
                @endcan
            @endunless
        </div>

        <aside class="min-w-0">
            @if ($editing && $item)
                @include('livewire.admin.navigation.partials.edit-panel')
            @else
                <div class="a-panel p-6 text-center text-[13.5px] text-[var(--a-muted)]">{{ __('admin.menus.pick_hint') }}</div>
            @endif
        </aside>
    </div>
</div>
