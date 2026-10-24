<div>
    <x-admin.page-header :title="__('admin.users.title')" :subtitle="__('admin.users.subtitle')"
        :breadcrumbs="[__('admin.nav.system') => null, __('admin.users.title') => null]">
        <x-slot:actions>
            <a href="{{ route('admin.roles.index') }}" wire:navigate class="a-btn a-btn-secondary">{{ __('admin.nav.roles') }}</a>
            <button type="button" wire:click="export" class="a-btn a-btn-secondary">{{ __('admin.common.export') }}</button>
            <a href="{{ route('admin.users.create') }}" wire:navigate class="a-btn a-btn-primary">{{ __('admin.users.add') }}</a>
        </x-slot:actions>
    </x-admin.page-header>

    @php($rows = $this->rows)
    @php($sort = $this->currentSort())

    <div class="a-panel" x-data="columnToggle('users', [])">
        <div class="flex flex-wrap items-center gap-2 border-b border-a-border p-4">
            <x-admin.table.search :placeholder="__('admin.common.search')" />
            <x-admin.table.filter key="role" :label="__('admin.users.role')" :options="$roles" :value="$filters['role'] ?? null" />
            <x-admin.table.filter key="active" :label="__('admin.users.status')" :options="['1' => __('admin.users.active'), '0' => __('admin.users.inactive')]" :value="$filters['active'] ?? null" />
            @if ($filters || $search)
                <button type="button" wire:click="clearFilters" class="text-[13.5px] font-bold text-[var(--c-deep)] hover:underline">{{ __('admin.common.clear_filters') }}</button>
            @endif
            <div class="ms-auto">
                <x-admin.table.columns :columns="['email' => __('admin.users.email'), 'role' => __('admin.users.role'), 'login' => __('admin.users.last_login')]" />
            </div>
        </div>

        <x-admin.table.bulk-bar :count="$this->selectedCount()" :total="$rows->total()" :all="$selectAllMatching">
            <button type="button" class="a-btn a-btn-secondary a-btn-sm" wire:click="bulkSetActive(true)">{{ __('admin.users.activate') }}</button>
            <button type="button" class="a-btn a-btn-secondary a-btn-sm" wire:click="bulkSetActive(false)">{{ __('admin.users.deactivate') }}</button>
            <button type="button" class="a-btn a-btn-secondary a-btn-sm" wire:click="exportSelected">{{ __('admin.common.export_selected') }}</button>
            <button type="button" class="a-link-danger px-2 text-[13.5px]"
                    @click="$confirm({ title: @js(__('admin.common.confirm_bulk_delete_title')), text: @js(__('admin.common.confirm_bulk_delete_text', ['count' => $this->selectedCount()])), confirmText: @js(__('admin.common.yes_delete')), danger: true }).then(ok => ok && $wire.bulkDelete())">
                {{ __('admin.common.delete') }}
            </button>
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
                        <x-admin.table.th field="name" :sort="$sort">{{ __('admin.users.name') }}</x-admin.table.th>
                        <x-admin.table.th field="email" :sort="$sort" column="email">{{ __('admin.users.email') }}</x-admin.table.th>
                        <x-admin.table.th column="role">{{ __('admin.users.role') }}</x-admin.table.th>
                        <x-admin.table.th>{{ __('admin.users.status') }}</x-admin.table.th>
                        <x-admin.table.th field="login" :sort="$sort" column="login">{{ __('admin.users.last_login') }}</x-admin.table.th>
                        <th><span class="sr-only">{{ __('admin.common.actions') }}</span></th>
                    </tr>
                </thead>
                <tbody wire:loading.class="opacity-60">
                    @forelse ($rows as $user)
                        <tr wire:key="user-{{ $user->id }}" data-selected="{{ in_array((string) $user->id, $selected, true) || $selectAllMatching ? 'true' : 'false' }}">
                            <td><input type="checkbox" class="a-check" value="{{ $user->id }}" wire:model.live="selected" aria-label="{{ $user->name }}"></td>
                            <td>
                                <div class="flex items-center gap-3">
                                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-[#e8edf5] text-[13px] font-bold text-[var(--c-navy)]">{{ $user->initials() }}</span>
                                    <div>
                                        <p class="font-bold text-[var(--c-deep)]">{{ $user->name }}
                                            @if ($user->is(auth()->user()))<span class="ms-1 text-[12px] font-semibold text-[var(--a-muted)]">({{ __('admin.users.you') }})</span>@endif
                                        </p>
                                    </div>
                                </div>
                            </td>
                            <td x-show="visible('email')" dir="ltr" class="text-start">{{ $user->email }}</td>
                            <td x-show="visible('role')">
                                @foreach ($user->roles as $role)
                                    <x-admin.badge color="navy" :dot="false">{{ __('admin.roles.'.$role->name) }}</x-admin.badge>
                                @endforeach
                            </td>
                            <td>
                                <button type="button" role="switch" class="a-toggle" aria-checked="{{ $user->is_active ? 'true' : 'false' }}" wire:click="toggleActive({{ $user->id }})" aria-label="{{ __('admin.users.status') }}"></button>
                            </td>
                            <td x-show="visible('login')" class="text-[13px] text-[var(--a-muted)]">{{ $user->last_login_at?->diffForHumans() ?? __('admin.common.never') }}</td>
                            <td class="whitespace-nowrap text-end">
                                <a href="{{ route('admin.users.edit', $user) }}" wire:navigate class="a-link me-3">{{ __('admin.common.edit') }}</a>
                                @unless ($user->is(auth()->user()))
                                    <button type="button" class="a-link-danger"
                                            @click="$confirm({ title: @js(__('admin.common.confirm_delete_title')), text: @js(__('admin.common.confirm_delete_text', ['name' => $user->name])), confirmText: @js(__('admin.common.yes_delete')), danger: true }).then(ok => ok && $wire.delete({{ $user->id }}))">
                                        {{ __('admin.common.delete') }}
                                    </button>
                                @endunless
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">
                                <x-admin.empty-state :title="__('admin.users.empty')">
                                    <a href="{{ route('admin.users.create') }}" wire:navigate class="a-btn a-btn-primary">{{ __('admin.users.add') }}</a>
                                </x-admin.empty-state>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <x-admin.table.pagination :paginator="$rows" />
    </div>
</div>
