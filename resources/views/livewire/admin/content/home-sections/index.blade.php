<div>
    <x-admin.page-header :title="__('admin.nav.home_sections')" :subtitle="__('admin.home.subtitle')" :breadcrumbs="[__('admin.nav.content') => null, __('admin.nav.home_sections') => null]">
        <x-slot:actions>
            <a href="{{ url('/') }}" target="_blank" rel="noopener" class="a-btn a-btn-secondary">{{ __('admin.common.preview') }}</a>
        </x-slot:actions>
    </x-admin.page-header>

    @php($sections = $this->sections)
    <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_440px]">
        <x-admin.panel :title="__('admin.home.sections')" :subtitle="__('admin.home.count', ['count' => $sections->count(), 'visible' => $sections->where('is_visible', true)->count()])">
            <x-slot:actions>
                @can('create', \App\Models\HomeSection::class)
                    <div x-data="{ open: false }" class="relative" @click.outside="open = false">
                        <button type="button" class="a-btn a-btn-secondary a-btn-sm" @click="open = ! open">{{ __('admin.home.add') }}</button>
                        <div x-cloak x-show="open" x-transition class="a-popover end-0 top-10 max-h-80 w-64 overflow-y-auto">
                            @foreach (\App\Enums\SectionType::cases() as $type)
                                <button type="button" class="a-menu-item" wire:click="add('{{ $type->value }}')" @click="open = false">{{ $type->label() }}</button>
                            @endforeach
                        </div>
                    </div>
                @endcan
            </x-slot:actions>

            <ul class="space-y-3" x-data x-sortable.handle="reorder">
                @foreach ($sections as $row)
                    <li wire:key="hs-{{ $row->id }}" data-sort-id="{{ $row->id }}"
                        @class(['flex items-center gap-3 rounded-xl border bg-white px-4 py-3 transition', 'border-[var(--c-navy)] ring-2 ring-[var(--c-sky)]/30' => $editing === $row->id, 'border-a-border' => $editing !== $row->id, 'opacity-60' => ! $row->is_visible])>
                        <button type="button" data-sort-handle class="a-drag-handle" aria-label="{{ __('admin.common.drag') }}"><x-heroicon-m-bars-2 class="h-5 w-5" /></button>
                        <button type="button" class="min-w-0 flex-1 text-start" wire:click="edit({{ $row->id }})">
                            <span class="block truncate font-bold text-[var(--c-deep)]">{{ $row->getTranslation('title', app()->getLocale(), false) ?: ($row->getTranslation('title', 'ar', false) ?: $row->type->label()) }}</span>
                            <span class="block truncate text-[12.5px] text-[var(--a-muted)]">{{ $this->summary($row) }}</span>
                        </button>
                        <x-admin.badge color="gray">{{ $row->type->label() }}</x-admin.badge>
                        <button type="button" role="switch" class="a-toggle" aria-checked="{{ $row->is_visible ? 'true' : 'false' }}" wire:click="toggleVisible({{ $row->id }})" aria-label="{{ __('admin.home.visible') }}"></button>
                        <button type="button" class="a-link text-[13.5px]" wire:click="edit({{ $row->id }})">{{ __('admin.common.edit') }}</button>
                    </li>
                @endforeach
            </ul>
        </x-admin.panel>

        <aside class="min-w-0">
            @if ($editing && $section)
                <div class="a-panel xl:sticky xl:top-20" x-data="dirtyGuard(@js(__('admin.common.unsaved_changes')))" wire:key="editor-{{ $editing }}">
                    <div class="a-panel-header">
                        <h2 class="a-panel-title">{{ __('admin.home.edit', ['name' => \App\Enums\SectionType::from($section['type'])->label()]) }}</h2>
                        <button type="button" class="rounded-lg p-1.5 hover:bg-a-bg" wire:click="close" aria-label="{{ __('admin.common.close') }}"><x-heroicon-o-x-mark class="h-5 w-5" /></button>
                    </div>
                    <form wire:submit="save" class="max-h-[calc(100vh-12rem)] space-y-5 overflow-y-auto p-5">
                        @include('livewire.admin.content.home-sections.types.'.$section['type'])

                        @unless ($section['type'] === 'hero')
                            <x-admin.segmented model="section.background" live :label="__('admin.home.background')" :options="\App\Enums\SectionBackground::options()" />
                            @if (($section['background'] ?? '') === 'dark_photo')
                                <livewire:admin.asset-field wire:model="section.data.bg_asset_id" kind="image" :label="__('admin.home.bg_image')" wire:key="bg-{{ $editing }}" />
                            @endif
                        @endunless

                        <x-admin.toggle model="section.is_visible" :label="__('admin.home.visible')" />

                        <div class="sticky bottom-0 -mx-5 -mb-5 flex items-center justify-between gap-3 border-t border-a-border bg-white px-5 py-3">
                            @can('delete', \App\Models\HomeSection::class)
                                <button type="button" class="a-link-danger text-[13.5px]" @click="$confirm({ title: @js(__('admin.home.delete_confirm')), danger: true, confirmText: @js(__('admin.common.yes_delete')) }).then(ok => ok && $wire.delete({{ $editing }}))">{{ __('admin.common.delete') }}</button>
                            @endcan
                            <button type="submit" class="a-btn a-btn-primary">{{ __('admin.home.apply') }}</button>
                        </div>
                    </form>
                </div>
            @else
                <div class="a-panel p-6 text-center text-[13.5px] text-[var(--a-muted)]">{{ __('admin.home.pick_hint') }}</div>
            @endif
        </aside>
    </div>
</div>
