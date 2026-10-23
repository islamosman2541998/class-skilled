<div>
    <x-admin.page-header :title="__('admin.nav.projects')" :subtitle="__('admin.projects.subtitle')" :breadcrumbs="[__('admin.nav.content') => null, __('admin.nav.projects') => null]">
        <x-slot:actions>
            <button type="button" wire:click="export" class="a-btn a-btn-secondary">{{ __('admin.common.export') }}</button>
            @can('create', \App\Models\Project::class)
                <a href="{{ route('admin.projects.create') }}" wire:navigate class="a-btn a-btn-primary">{{ __('admin.projects.add') }}</a>
            @endcan
        </x-slot:actions>
    </x-admin.page-header>

    <x-admin.table.status-tabs :tabs="$this->statusTabs()" :value="$filters['status'] ?? ''" />

    @php($rows = $this->rows)
    @php($sort = $this->currentSort())
    <div class="a-panel" x-data="columnToggle('projects', [])">
        @include('livewire.admin.content.partials.list-toolbar', ['featurable' => true, 'filtersView' => 'livewire.admin.content.projects.filters'])
        @include('livewire.admin.content.partials.bulk', ['actions' => ['publish', 'unpublish', 'feature', 'unfeature']])
        <div class="overflow-x-auto">
            <table class="a-table">
                <thead>
                    <tr>
                        <th class="w-10"><input type="checkbox" class="a-check" aria-label="{{ __('admin.common.select_page') }}" wire:click="{{ count($selected) && count($selected) >= $rows->count() ? 'clearSelection' : 'selectPage' }}" @checked($rows->count() && count($selected) >= $rows->count())></th>
                        <th class="w-8"></th>
                        <th>{{ __('admin.content.singular.project') }}</th>
                        <x-admin.table.th column="category">{{ __('admin.projects.category') }}</x-admin.table.th>
                        <x-admin.table.th field="year" :sort="$sort" column="year">{{ __('admin.projects.year') }}</x-admin.table.th>
                        <th>{{ __('admin.common.translation') }}</th>
                        <th>{{ __('admin.content.featured') }}</th>
                        <th>{{ __('admin.content.published') }}</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody @if ($this->canReorder()) x-sortable.handle="reorder" @endif wire:loading.class="opacity-60">
                    @forelse ($rows as $project)
                        @php($photos = $project->assetsIn('gallery')->count())
                        <tr wire:key="prj-{{ $project->id }}" data-sort-id="{{ $project->id }}">
                            <td><input type="checkbox" class="a-check" value="{{ $project->id }}" wire:model.live="selected" aria-label="{{ $project->getTranslation('title', 'ar') }}"></td>
                            <td>@if ($this->canReorder())<button type="button" data-sort-handle class="a-drag-handle" aria-label="{{ __('admin.common.drag') }}"><x-heroicon-m-bars-2 class="h-4 w-4" /></button>@endif</td>
                            <td>
                                <div class="flex items-center gap-3">
                                    @if ($cover = $project->firstAsset('cover'))
                                        <x-admin.asset-thumb :asset="$cover" class="h-11 w-14 shrink-0" />
                                    @else
                                        <span class="h-11 w-14 shrink-0 rounded-lg bg-a-bg"></span>
                                    @endif
                                    <div>
                                        <a href="{{ route('admin.projects.edit', $project->id) }}" wire:navigate class="font-bold text-[var(--c-deep)] hover:underline">{{ $project->getTranslation('title', app()->getLocale(), false) ?: $project->getTranslation('title', 'ar') }}</a>
                                        <p class="text-[12.5px] text-[var(--a-muted)]">{{ __('admin.projects.photos', ['count' => $photos]) }}</p>
                                    </div>
                                </div>
                            </td>
                            <td x-show="visible('category')" class="text-[13.5px]">{{ $project->category?->getTranslation('name', app()->getLocale(), false) ?: $project->category?->getTranslation('name', 'ar') }}</td>
                            <td x-show="visible('year')" dir="ltr" class="text-start">{{ $project->year }}</td>
                            <td><x-admin.badge :color="$this->englishMissing($project) ? 'amber' : 'green'">{{ $this->englishMissing($project) ? __('admin.common.translation_missing') : __('admin.common.translation_complete') }}</x-admin.badge></td>
                            <td><button type="button" role="switch" class="a-toggle" aria-checked="{{ $project->is_featured ? 'true' : 'false' }}" wire:click="toggle({{ $project->id }}, 'is_featured')" aria-label="{{ __('admin.content.featured') }}"></button></td>
                            <td>
                                <button type="button" role="switch" class="a-toggle" aria-checked="{{ $project->isPublished() || $project->is_scheduled ? 'true' : 'false' }}" wire:click="toggle({{ $project->id }}, 'published')" aria-label="{{ __('admin.content.published') }}"></button>
                                @if ($project->is_scheduled)<x-admin.badge color="violet" class="ms-1">{{ __('enums.publish_status.scheduled') }}</x-admin.badge>@endif
                            </td>
                            @include('livewire.admin.content.partials.row-actions', ['row' => $project, 'editUrl' => route('admin.projects.edit', $project->id), 'name' => $project->getTranslation('title', 'ar'), 'moduleName' => __('admin.content.singular.project')])
                        </tr>
                    @empty
                        <tr><td colspan="9"><x-admin.empty-state :title="__('admin.common.no_results')" /></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <x-admin.table.pagination :paginator="$rows" />
    </div>
</div>
