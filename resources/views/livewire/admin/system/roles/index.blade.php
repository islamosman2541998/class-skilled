<div x-data="dirtyGuard(@js(__('admin.common.unsaved_changes')))">
    <x-admin.page-header :title="__('admin.roles_screen.title')" :subtitle="__('admin.roles_screen.subtitle')"
        :breadcrumbs="[__('admin.nav.system') => null, __('admin.users.title') => route('admin.users.index'), __('admin.roles_screen.title') => null]">
        <x-slot:actions>
            <button type="button" wire:click="save" class="a-btn a-btn-primary">{{ __('admin.common.save_changes') }}</button>
        </x-slot:actions>
    </x-admin.page-header>

    <div class="mb-5 flex flex-wrap gap-2" role="tablist">
        <span class="a-chip" aria-disabled="true" title="{{ __('admin.roles_screen.admin_locked') }}">
            {{ __('admin.roles.admin') }} <span class="count" dir="ltr">{{ $counts['admin'] ?? 0 }}</span>
        </span>
        @foreach (['editor', 'sales'] as $r)
            <button type="button" role="tab" class="a-chip" aria-pressed="{{ $role === $r ? 'true' : 'false' }}" wire:click="$set('role', '{{ $r }}')">
                {{ __('admin.roles.'.$r) }} <span class="count" dir="ltr">{{ $counts[$r] ?? 0 }}</span>
            </button>
        @endforeach
    </div>

    <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_360px]">
        <x-admin.panel :padded="false">
            <div class="overflow-x-auto">
                <table class="a-table">
                    <thead>
                        <tr>
                            <th>{{ __('admin.roles_screen.module') }}</th>
                            @foreach ($actions as $action)
                                <th class="text-center">{{ __('admin.roles_screen.'.$action) }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($modules as $module)
                            <tr wire:key="m-{{ $module }}">
                                <td class="font-semibold">{{ __('admin.roles_screen.modules.'.$module) }}</td>
                                @foreach ($actions as $action)
                                    <td class="text-center">
                                        <input type="checkbox" class="a-check" wire:model="grants.{{ $this->key($action.' '.$module) }}"
                                               aria-label="{{ __('admin.roles_screen.'.$action) }} — {{ __('admin.roles_screen.modules.'.$module) }}">
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </x-admin.panel>

        <x-admin.panel :title="__('admin.roles_screen.special')">
            <div class="space-y-3">
                @foreach ($special as $permission)
                    <label class="flex items-center gap-3 text-[14px]" wire:key="s-{{ $this->key($permission) }}">
                        <input type="checkbox" class="a-check" wire:model="grants.{{ $this->key($permission) }}">
                        {{ __('admin.roles_screen.permissions.'.$permission) }}
                    </label>
                @endforeach
            </div>
        </x-admin.panel>
    </div>
</div>
