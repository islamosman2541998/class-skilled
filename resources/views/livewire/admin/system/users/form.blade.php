<div x-data="dirtyGuard(@js(__('admin.common.unsaved_changes')))">
    <x-admin.page-header :title="$title"
        :breadcrumbs="[__('admin.nav.system') => null, __('admin.users.title') => route('admin.users.index'), $title => null]">
        <x-slot:actions>
            <a href="{{ route('admin.users.index') }}" wire:navigate class="a-btn a-btn-secondary">{{ __('admin.common.cancel') }}</a>
            <button type="submit" form="user-form" class="a-btn a-btn-primary" wire:loading.attr="disabled" wire:target="save">{{ __('admin.common.save') }}</button>
        </x-slot:actions>
    </x-admin.page-header>

    <form id="user-form" wire:submit="save" class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_320px]">
        <x-admin.panel :title="__('admin.account.profile')">
            <div class="grid gap-5 sm:grid-cols-2">
                <x-admin.input model="name" :label="__('admin.users.name')" required autocomplete="off" />
                <x-admin.input model="email" type="email" :label="__('admin.users.email')" required autocomplete="off" />
                <x-admin.input model="password" type="password" :label="__('admin.users.password')" :required="! $user" :hint="$user ? __('admin.users.password_hint') : null" autocomplete="new-password" />
                <x-admin.select model="locale" :label="__('admin.users.locale')" :options="['ar' => 'العربية', 'en' => 'English']" :placeholder="false" />
            </div>
        </x-admin.panel>

        <div class="space-y-6">
            <x-admin.panel :title="__('admin.users.role')">
                <div class="space-y-5">
                    <x-admin.select model="role" :label="__('admin.users.role')" :options="$roles" :placeholder="false" required />
                    <x-admin.toggle model="is_active" :label="__('admin.users.active')" />
                </div>
            </x-admin.panel>

            @if ($user)
                <x-admin.panel>
                    <p class="text-[13px] text-[var(--a-muted)]">{{ __('admin.users.last_login') }}: {{ $user->last_login_at?->format('Y-m-d H:i') ?? __('admin.common.never') }}</p>
                    <button type="button" wire:click="sendResetLink" class="a-btn a-btn-secondary a-btn-sm mt-4">{{ __('admin.users.send_reset') }}</button>
                </x-admin.panel>
            @endif
        </div>
    </form>
</div>
