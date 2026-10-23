<div>
    <x-admin.page-header :title="__('admin.nav.services')" :subtitle="__('admin.services.subtitle')" :breadcrumbs="[__('admin.nav.content') => null, __('admin.nav.services') => null]">
        <x-slot:actions>
            <button type="button" wire:click="export" class="a-btn a-btn-secondary">{{ __('admin.common.export') }}</button>
            @can('create', \App\Models\Service::class)
                <a href="{{ route('admin.services.create') }}" wire:navigate class="a-btn a-btn-primary">{{ __('admin.services.add') }}</a>
            @endcan
        </x-slot:actions>
    </x-admin.page-header>

    @php($rows = $this->rows)
    <div class="a-panel">
        @include('livewire.admin.content.partials.list-toolbar', ['featurable' => true, 'filtersView' => 'livewire.admin.content.services.filters'])
        @include('livewire.admin.content.partials.bulk', ['actions' => ['activate', 'deactivate', 'feature', 'unfeature']])
        <div class="overflow-x-auto">
            <table class="a-table">
                <thead>
                    <tr>
                        <th class="w-10"><input type="checkbox" class="a-check" aria-label="{{ __('admin.common.select_page') }}" wire:click="{{ count($selected) && count($selected) >= $rows->count() ? 'clearSelection' : 'selectPage' }}" @checked($rows->count() && count($selected) >= $rows->count())></th>
                        <th class="w-8"></th>
                        <th>{{ __('admin.content.fields.title') }}</th>
                        <th>{{ __('admin.services.projects') }}</th>
                        <th>{{ __('admin.common.translation') }}</th>
                        <th>{{ __('admin.content.featured') }}</th>
                        <th>{{ __('admin.content.active') }}</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody @if ($this->canReorder()) x-sortable.handle="reorder" @endif wire:loading.class="opacity-60">
                    @forelse ($rows as $service)
                        <tr wire:key="svc-{{ $service->id }}" data-sort-id="{{ $service->id }}">
                            <td><input type="checkbox" class="a-check" value="{{ $service->id }}" wire:model.live="selected" aria-label="{{ $service->getTranslation('title', 'ar') }}"></td>
                            <td>@if ($this->canReorder())<button type="button" data-sort-handle class="a-drag-handle" aria-label="{{ __('admin.common.drag') }}"><x-heroicon-m-bars-2 class="h-4 w-4" /></button>@endif</td>
                            <td>
                                <div class="flex items-center gap-3">
                                    @if ($cover = $service->firstAsset('cover'))
                                        <x-admin.asset-thumb :asset="$cover" class="h-11 w-14 shrink-0" />
                                    @else
                                        <span class="h-11 w-14 shrink-0 rounded-lg bg-a-bg"></span>
                                    @endif
                                    <div class="min-w-0">
                                        <a href="{{ route('admin.services.edit', $service->id) }}" wire:navigate class="font-bold text-[var(--c-deep)] hover:underline">{{ $service->getTranslation('title', app()->getLocale(), false) ?: $service->getTranslation('title', 'ar') }}</a>
                                        <p class="truncate text-[12.5px] text-[var(--a-muted)]">{{ $service->getTranslation('summary', app()->getLocale(), false) ?: $service->getTranslation('summary', 'ar', false) }}</p>
                                    </div>
                                </div>
                            </td>
                            <td dir="ltr" class="text-start">{{ $service->projects_count }}</td>
                            <td><x-admin.badge :color="$this->englishMissing($service) ? 'amber' : 'green'">{{ $this->englishMissing($service) ? __('admin.common.translation_missing') : __('admin.common.translation_complete') }}</x-admin.badge></td>
                            <td><button type="button" role="switch" class="a-toggle" aria-checked="{{ $service->is_featured ? 'true' : 'false' }}" wire:click="toggle({{ $service->id }}, 'is_featured')" aria-label="{{ __('admin.content.featured') }}"></button></td>
                            <td><button type="button" role="switch" class="a-toggle" aria-checked="{{ $service->is_active ? 'true' : 'false' }}" wire:click="toggle({{ $service->id }}, 'is_active')" aria-label="{{ __('admin.content.active') }}"></button></td>
                            @include('livewire.admin.content.partials.row-actions', ['row' => $service, 'editUrl' => route('admin.services.edit', $service->id), 'name' => $service->getTranslation('title', 'ar'), 'moduleName' => __('admin.content.singular.service')])
                        </tr>
                    @empty
                        <tr><td colspan="8"><x-admin.empty-state :title="__('admin.common.no_results')" /></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <x-admin.table.pagination :paginator="$rows" />
    </div>
</div>
