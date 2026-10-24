@php
    $key = $this->routeKey();
    $rows = $this->rows;
    $sort = $this->currentSort();
    $counts = $this->statusCounts;
    $tabs = ['' => [__('admin.common.all'), $counts[''] ?? 0]];
    foreach ($this->statuses as $status) {
        $tabs[(string) $status->id] = [$status->getTranslation('name', app()->getLocale()), $counts[(string) $status->id] ?? 0];
    }
    $extraColumns = $this->extraColumns();
    $canDelete = auth()->user()->can('deleteAny', $rows->first() ? get_class($rows->first()) : \App\Models\QuoteRequest::class);
    $canExport = auth()->user()->can('export requests');
@endphp
<div>
    <x-admin.page-header :title="__('admin.nav.'.$key)" :subtitle="__('admin.requests.subtitle_'.$key)"
        :breadcrumbs="[__('admin.nav.requests') => null, __('admin.nav.'.$key) => null]">
        <x-slot:actions>
            @can('manage request statuses')
                <a href="{{ route('admin.request-statuses.index', ['type' => $this->type()->value]) }}" wire:navigate class="a-btn a-btn-secondary">{{ __('admin.nav.request_statuses') }}</a>
            @endcan
            @if ($canExport)
                <button type="button" wire:click="export" class="a-btn a-btn-secondary">{{ __('admin.common.export') }}</button>
            @endif
        </x-slot:actions>
    </x-admin.page-header>

    <x-admin.table.status-tabs key="status" :value="$filters['status'] ?? ''" :tabs="$tabs" />

    <div class="a-panel" x-data="columnToggle('requests-{{ $key }}', [])">
        <div class="flex flex-wrap items-center gap-2 border-b border-a-border p-4">
            <x-admin.table.search :placeholder="__('admin.requests.search')" />
            @foreach ($this->extraFilters() as $filterKey => [$label, $options])
                <x-admin.table.filter :key="$filterKey" :label="$label" :options="$options" :value="$filters[$filterKey] ?? null" />
            @endforeach
            <x-admin.table.filter key="assignee" :label="__('admin.requests.assignee')" :options="['none' => __('admin.requests.unassigned')] + collect($this->assignees)->mapWithKeys(fn ($n, $id) => [(string) $id => $n])->all()" :value="$filters['assignee'] ?? null" />
            <x-admin.table.filter key="device" :label="__('admin.requests.device')" :options="['mobile' => __('admin.requests.devices.mobile'), 'tablet' => __('admin.requests.devices.tablet'), 'desktop' => __('admin.requests.devices.desktop')]" :value="$filters['device'] ?? null" />
            <x-admin.table.filter key="unread" :label="__('admin.requests.read_state')" :options="['1' => __('admin.requests.unread')]" :value="$filters['unread'] ?? null" />
            <div class="flex items-center gap-1.5">
                <label class="sr-only" for="f-from">{{ __('admin.requests.from') }}</label>
                <input id="f-from" type="date" class="a-input h-9 w-[150px] text-[13px]" wire:model.live="filters.from" title="{{ __('admin.requests.from') }}">
                <span class="text-[var(--a-muted)]">–</span>
                <label class="sr-only" for="f-to">{{ __('admin.requests.to') }}</label>
                <input id="f-to" type="date" class="a-input h-9 w-[150px] text-[13px]" wire:model.live="filters.to" title="{{ __('admin.requests.to') }}">
            </div>
            @if ($filters || $search)
                <button type="button" wire:click="clearFilters" class="text-[13.5px] font-bold text-[var(--c-deep)] hover:underline">{{ __('admin.common.clear_filters') }}</button>
            @endif
            <div class="ms-auto">
                <x-admin.table.columns :columns="collect($extraColumns)->map(fn ($c) => $c[0])->all() + ['assignee' => __('admin.requests.assignee')]" />
            </div>
        </div>

        <x-admin.table.bulk-bar :count="$this->selectedCount()" :total="$rows->total()" :all="$selectAllMatching">
            <div class="flex items-center gap-1.5">
                <select class="a-input h-8 w-auto text-[13px]" wire:model="bulkStatus" aria-label="{{ __('admin.requests.change_status') }}">
                    <option value="">{{ __('admin.requests.change_status') }}</option>
                    @foreach ($this->statuses as $status)
                        <option value="{{ $status->id }}">{{ $status->getTranslation('name', app()->getLocale()) }}</option>
                    @endforeach
                </select>
                <button type="button" class="a-btn a-btn-secondary a-btn-sm" wire:click="bulkSetStatus">{{ __('admin.requests.apply') }}</button>
            </div>
            <div class="flex items-center gap-1.5">
                <select class="a-input h-8 w-auto text-[13px]" wire:model="bulkAssignee" aria-label="{{ __('admin.requests.assign_to') }}">
                    <option value="">{{ __('admin.requests.assign_to') }}</option>
                    <option value="none">{{ __('admin.requests.unassigned') }}</option>
                    @foreach ($this->assignees as $id => $name)
                        <option value="{{ $id }}">{{ $name }}</option>
                    @endforeach
                </select>
                <button type="button" class="a-btn a-btn-secondary a-btn-sm" wire:click="bulkAssign">{{ __('admin.requests.apply') }}</button>
            </div>
            <button type="button" class="a-btn a-btn-secondary a-btn-sm" wire:click="bulkMarkRead">{{ __('admin.requests.mark_read') }}</button>
            @if ($canExport)
                <button type="button" class="a-btn a-btn-secondary a-btn-sm" wire:click="exportSelected">{{ __('admin.common.export_selected') }}</button>
            @endif
            @if ($canDelete)
                <button type="button" class="a-link-danger px-2 text-[13.5px]"
                        @click="$confirm({ title: @js(__('admin.common.confirm_bulk_delete_title')), text: @js(__('admin.common.confirm_bulk_delete_text', ['count' => $this->selectedCount()])), confirmText: @js(__('admin.common.yes_delete')), danger: true }).then(ok => ok && $wire.bulkDelete())">
                    {{ __('admin.common.delete') }}
                </button>
            @endif
        </x-admin.table.bulk-bar>

        <div class="overflow-x-auto">
            <table class="a-table">
                <thead>
                    <tr>
                        <th class="w-10">
                            <input type="checkbox" class="a-check" aria-label="{{ __('admin.common.select_page') }}"
                                   @checked($rows->count() && count($selected) >= $rows->count())
                                   wire:click="{{ count($selected) >= $rows->count() && $rows->count() ? 'clearSelection' : 'selectPage' }}">
                        </th>
                        <x-admin.table.th field="name" :sort="$sort">{{ __('admin.requests.client') }}</x-admin.table.th>
                        @foreach ($extraColumns as $columnKey => [$label])
                            <x-admin.table.th :column="$columnKey">{{ $label }}</x-admin.table.th>
                        @endforeach
                        <x-admin.table.th field="status" :sort="$sort">{{ __('admin.requests.status') }}</x-admin.table.th>
                        <x-admin.table.th column="assignee">{{ __('admin.requests.assignee') }}</x-admin.table.th>
                        <x-admin.table.th field="created" :sort="$sort">{{ __('admin.requests.date') }}</x-admin.table.th>
                        <th><span class="sr-only">{{ __('admin.common.actions') }}</span></th>
                    </tr>
                </thead>
                <tbody wire:loading.class="opacity-60">
                    @forelse ($rows as $row)
                        @php($unread = $row->read_at === null)
                        <tr wire:key="req-{{ $row->id }}" @class(['font-bold' => $unread]) data-selected="{{ in_array((string) $row->id, $selected, true) || $selectAllMatching ? 'true' : 'false' }}">
                            <td><input type="checkbox" class="a-check" value="{{ $row->id }}" wire:model.live="selected" aria-label="{{ $row->name }}"></td>
                            <td>
                                <a href="{{ route('admin.'.$key.'.show', $row->id) }}" wire:navigate class="block">
                                    <span class="flex items-center gap-2 text-[var(--c-deep)]">
                                        @if ($unread)<span class="size-2 rounded-full bg-[var(--c-sky)]" title="{{ __('admin.requests.unread') }}"></span>@endif
                                        {{ $row->name }}
                                        @if (($row->media_count ?? 0) > 0)
                                            <x-heroicon-m-paper-clip class="size-4 text-[var(--a-muted)]" :title="__('admin.requests.attachments')" />
                                        @endif
                                    </span>
                                    <span class="mt-0.5 block whitespace-nowrap text-[12.5px] font-normal text-[var(--a-muted)]"><bdi dir="ltr">{{ \App\Support\RequestPresenter::number($row) }}</bdi> · <bdi dir="ltr">{{ $row->phone }}</bdi></span>
                                </a>
                            </td>
                            @foreach ($extraColumns as $columnKey => [, $value])
                                <td x-show="visible('{{ $columnKey }}')" class="font-normal">{{ $value($row) }}</td>
                            @endforeach
                            <td>
                                @if ($row->status)
                                    <x-admin.badge :color="$row->status->color->value">{{ $row->status->getTranslation('name', app()->getLocale()) }}</x-admin.badge>
                                @endif
                            </td>
                            <td x-show="visible('assignee')" class="font-normal">{{ $row->assignee?->name ?? '—' }}</td>
                            <td class="whitespace-nowrap text-[13px] font-normal text-[var(--a-muted)]" title="{{ $row->created_at?->format('Y-m-d H:i') }}">{{ $row->created_at?->diffForHumans() }}</td>
                            <td class="whitespace-nowrap text-end font-normal">
                                <a href="{{ route('admin.'.$key.'.show', $row->id) }}" wire:navigate class="a-link">{{ __('admin.common.open') }}</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ 6 + count($extraColumns) }}">
                                <x-admin.empty-state :title="__('admin.requests.empty')" />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <x-admin.table.pagination :paginator="$rows" />
    </div>
</div>
