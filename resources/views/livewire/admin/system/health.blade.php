<div>
    <x-admin.page-header :title="__('admin.nav.health')" :subtitle="__('admin.health.subtitle')"
        :breadcrumbs="[__('admin.nav.system') => null, __('admin.nav.health') => null]">
        <x-slot:actions>
            <button type="button" class="a-btn a-btn-secondary" wire:click="clearCaches" wire:loading.attr="disabled">{{ __('admin.health.clear') }}</button>
            <button type="button" class="a-btn a-btn-primary" wire:click="optimize" wire:loading.attr="disabled">{{ __('admin.health.optimize') }}</button>
        </x-slot:actions>
    </x-admin.page-header>

    <div @class(['mb-6 rounded-xl border px-4 py-3 text-[14px] font-semibold', 'border-red-200 bg-red-50 text-[var(--a-danger)]' => $failed, 'border-green-200 bg-green-50 text-[var(--a-success)]' => ! $failed]) role="status">
        {{ $failed ? __('admin.health.has_failures') : __('admin.health.all_good') }}
    </div>

    <div class="grid gap-6 xl:grid-cols-2">
        @foreach ($groups as $group => $rows)
            <x-admin.panel :title="__('admin.health.groups.'.$group)" :padded="false">
                <table class="a-table">
                    <tbody>
                        @foreach ($rows as $row)
                            <tr>
                                <td class="font-semibold" dir="ltr">{{ $row['label'] }}</td>
                                <td><x-admin.badge :color="['ok' => 'green', 'warn' => 'amber', 'fail' => 'red'][$row['status']]">{{ __('admin.health.status.'.$row['status']) }}</x-admin.badge></td>
                                <td class="text-[13px] text-[var(--a-muted)]" dir="ltr">{{ $row['detail'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </x-admin.panel>
        @endforeach
    </div>
</div>
