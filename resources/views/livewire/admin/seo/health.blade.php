<div>
    <x-admin.page-header :title="__('admin.nav.seo_health')" :subtitle="__('admin.seo_health.subtitle')"
        :breadcrumbs="[__('admin.nav.seo') => null, __('admin.nav.seo_health') => null]" />

    @php($rows = $this->rows)
    <div class="a-panel">
        <div class="flex flex-wrap items-center gap-2 border-b border-a-border p-4">
            <select wire:model.live="type" class="a-input w-auto" aria-label="{{ __('admin.seo_health.type') }}">
                <option value="">{{ __('admin.common.all') }}</option>
                @foreach (array_keys(\App\Livewire\Admin\Seo\Health::TYPES) as $type)
                    <option value="{{ $type }}">{{ \App\Support\AdminLinks::typeLabel($type) }}</option>
                @endforeach
            </select>
            <select wire:model.live="issue" class="a-input w-auto" aria-label="{{ __('admin.seo_health.issue') }}">
                <option value="">{{ __('admin.seo_health.all_issues') }}</option>
                @foreach (\App\Livewire\Admin\Seo\Health::ISSUES as $issueKey)
                    <option value="{{ $issueKey }}">{{ __('admin.seo_health.issues.'.$issueKey) }}</option>
                @endforeach
            </select>
            <span class="ms-auto text-[13.5px] text-[var(--a-muted)]">{{ __('admin.seo_health.count', ['count' => count($rows)]) }}</span>
        </div>
        <div class="overflow-x-auto">
        <table class="a-table">
            <thead><tr><th>{{ __('admin.common.title') }}</th><th>{{ __('admin.seo_health.type') }}</th><th>{{ __('admin.seo_health.issue') }}</th><th></th></tr></thead>
            <tbody>
                @forelse ($rows as $row)
                    <tr>
                        <td class="font-semibold">{{ $row['title'] }}</td>
                        <td class="text-[13px]">{{ \App\Support\AdminLinks::typeLabel($row['type']) }}</td>
                        <td>
                            <div class="flex flex-wrap gap-1">
                                @foreach ($row['issues'] as $issueKey)
                                    <x-admin.badge :color="$issueKey === 'english' ? 'amber' : 'red'">{{ __('admin.seo_health.issues.'.$issueKey) }}</x-admin.badge>
                                @endforeach
                            </div>
                        </td>
                        <td class="text-end">@if ($row['url'])<a href="{{ $row['url'] }}" wire:navigate class="a-link">{{ __('admin.seo_health.fix') }}</a>@endif</td>
                    </tr>
                @empty
                    <tr><td colspan="4"><x-admin.empty-state :title="__('admin.seo_health.all_good')" /></td></tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </div>
</div>
