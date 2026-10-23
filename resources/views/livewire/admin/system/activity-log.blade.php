<div>
    <x-admin.page-header :title="__('admin.activity.title')" :subtitle="__('admin.activity.subtitle')"
        :breadcrumbs="[__('admin.nav.system') => null, __('admin.activity.title') => null]">
        <x-slot:actions>
            <button type="button" wire:click="export" class="a-btn a-btn-secondary">{{ __('admin.common.export') }}</button>
        </x-slot:actions>
    </x-admin.page-header>

    @php($rows = $this->rows)

    <div class="a-panel">
        <div class="flex flex-wrap items-center gap-2 border-b border-a-border p-4">
            <x-admin.table.search />
            <x-admin.table.filter key="user" :label="__('admin.activity.user')" :options="$users" :value="$filters['user'] ?? null" />
            <x-admin.table.filter key="module" :label="__('admin.activity.module')" :options="$modules" :value="$filters['module'] ?? null" />
            <x-admin.table.filter key="event" :label="__('admin.activity.action')" :options="__('admin.activity.events')" :value="$filters['event'] ?? null" />
            <x-admin.table.filter key="period" :label="__('admin.table.date_range')" :options="['7' => __('admin.table.last_7'), '30' => __('admin.table.last_30'), '90' => __('admin.table.last_90')]" :value="$filters['period'] ?? null" />
            @if ($filters || $search)
                <button type="button" wire:click="clearFilters" class="text-[13.5px] font-bold text-[var(--c-deep)] hover:underline">{{ __('admin.common.clear_filters') }}</button>
            @endif
        </div>

        <div class="overflow-x-auto">
            <table class="a-table">
                <thead>
                    <tr>
                        <x-admin.table.th field="created" :sort="$this->currentSort()">{{ __('admin.common.date') }}</x-admin.table.th>
                        <th>{{ __('admin.activity.user') }}</th>
                        <th>{{ __('admin.activity.action') }}</th>
                        <th>{{ __('admin.activity.module') }}</th>
                        <th>{{ __('admin.activity.subject') }}</th>
                        <th><span class="sr-only">{{ __('admin.activity.changes') }}</span></th>
                    </tr>
                </thead>
                <tbody wire:loading.class="opacity-60">
                    @forelse ($rows as $activity)
                        <tr wire:key="act-{{ $activity->id }}">
                            <td class="whitespace-nowrap text-[13px] text-[var(--a-muted)]" title="{{ $activity->created_at }}">{{ $activity->created_at?->diffForHumans() }}</td>
                            <td class="font-semibold">{{ $activity->causer?->name ?? __('admin.activity.system') }}</td>
                            <td>
                                @php($event = $activity->event ?? 'updated')
                                <x-admin.badge :color="match ($event) { 'created' => 'green', 'deleted' => 'red', default => 'navy' }">{{ __('admin.activity.events.'.$event) === 'admin.activity.events.'.$event ? $event : __('admin.activity.events.'.$event) }}</x-admin.badge>
                            </td>
                            <td class="text-[13px]">{{ $activity->log_name }}</td>
                            <td class="max-w-[260px] truncate">{{ \App\Livewire\Admin\System\ActivityLog::subjectTitle($activity->subject) ?: ($activity->subject_id ? '#'.$activity->subject_id : $activity->description) }}</td>
                            <td class="text-end">
                                @if ($activity->properties?->isNotEmpty())
                                    <button type="button" class="a-link" wire:click="show({{ $activity->id }})">{{ __('admin.activity.view_changes') }}</button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6"><x-admin.empty-state :title="__('admin.activity.empty')" /></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <x-admin.table.pagination :paginator="$rows" />
    </div>

    <x-admin.modal name="activity-diff" :title="__('admin.activity.changes')" width="max-w-3xl">
        @if ($diff = $this->diff)
            <div class="overflow-x-auto">
                <table class="a-table">
                    <thead><tr><th>{{ __('admin.activity.field') }}</th><th>{{ __('admin.activity.old') }}</th><th>{{ __('admin.activity.new') }}</th></tr></thead>
                    <tbody>
                        @foreach (array_unique([...array_keys($diff['old']), ...array_keys($diff['new'])]) as $field)
                            <tr>
                                <td class="font-semibold" dir="ltr">{{ $field }}</td>
                                <td class="max-w-xs break-words text-[13px] text-[var(--a-danger)]">{{ \App\Livewire\Admin\System\ActivityLog::display($diff['old'][$field] ?? '') }}</td>
                                <td class="max-w-xs break-words text-[13px] text-[var(--a-success)]">{{ \App\Livewire\Admin\System\ActivityLog::display($diff['new'][$field] ?? '') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </x-admin.modal>
</div>
