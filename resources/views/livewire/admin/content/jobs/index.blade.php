<div>
    <x-admin.page-header :title="__('admin.nav.jobs')" :subtitle="__('admin.jobs.subtitle')" :breadcrumbs="[__('admin.nav.content') => null, __('admin.nav.jobs') => null]">
        <x-slot:actions>
            <button type="button" wire:click="export" class="a-btn a-btn-secondary">{{ __('admin.common.export') }}</button>
            @can('create', \App\Models\JobPost::class)
                <a href="{{ route('admin.jobs.create') }}" wire:navigate class="a-btn a-btn-primary">{{ __('admin.jobs.add') }}</a>
            @endcan
        </x-slot:actions>
    </x-admin.page-header>

    <x-admin.table.status-tabs :tabs="$this->statusTabs()" :value="$filters['status'] ?? ''" />

    @php($rows = $this->rows)
    <div class="a-panel">
        @include('livewire.admin.content.partials.list-toolbar')
        @include('livewire.admin.content.partials.bulk', ['actions' => ['publish', 'unpublish']])
        <div class="overflow-x-auto">
            <table class="a-table">
                <thead>
                    <tr>
                        <th class="w-10"><input type="checkbox" class="a-check" aria-label="{{ __('admin.common.select_page') }}" wire:click="{{ count($selected) && count($selected) >= $rows->count() ? 'clearSelection' : 'selectPage' }}" @checked($rows->count() && count($selected) >= $rows->count())></th>
                        <th class="w-8"></th>
                        <th>{{ __('admin.content.singular.job') }}</th>
                        <th>{{ __('admin.jobs.type') }}</th>
                        <th>{{ __('admin.jobs.closes_at') }}</th>
                        <th>{{ __('admin.jobs.applications_col') }}</th>
                        <th>{{ __('admin.content.published') }}</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody @if ($this->canReorder()) x-sortable.handle="reorder" @endif wire:loading.class="opacity-60">
                    @forelse ($rows as $job)
                        <tr wire:key="job-{{ $job->id }}" data-sort-id="{{ $job->id }}">
                            <td><input type="checkbox" class="a-check" value="{{ $job->id }}" wire:model.live="selected" aria-label="{{ $job->getTranslation('title', 'ar') }}"></td>
                            <td>@if ($this->canReorder())<button type="button" data-sort-handle class="a-drag-handle" aria-label="{{ __('admin.common.drag') }}"><x-heroicon-m-bars-2 class="h-4 w-4" /></button>@endif</td>
                            <td>
                                <a href="{{ route('admin.jobs.edit', $job->id) }}" wire:navigate class="font-bold text-[var(--c-deep)] hover:underline">{{ $job->getTranslation('title', app()->getLocale(), false) ?: $job->getTranslation('title', 'ar') }}</a>
                                <p class="text-[12.5px] text-[var(--a-muted)]">{{ $job->getTranslation('department', 'ar', false) }}</p>
                            </td>
                            <td class="text-[13.5px]">{{ $job->job_type->label() }}</td>
                            <td class="text-[13px]">
                                @if ($job->closes_at)
                                    <span @class(['text-[var(--a-danger)] font-semibold' => $job->closes_at->endOfDay()->isPast()])>{{ $job->closes_at->format('Y-m-d') }}</span>
                                @else — @endif
                            </td>
                            <td dir="ltr" class="text-start">{{ $job->applications_count }}</td>
                            <td><button type="button" role="switch" class="a-toggle" aria-checked="{{ $job->isPublished() || $job->is_scheduled ? 'true' : 'false' }}" wire:click="toggle({{ $job->id }}, 'published')" aria-label="{{ __('admin.content.published') }}"></button></td>
                            @include('livewire.admin.content.partials.row-actions', ['row' => $job, 'editUrl' => route('admin.jobs.edit', $job->id), 'name' => $job->getTranslation('title', 'ar'), 'moduleName' => __('admin.content.singular.job')])
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
