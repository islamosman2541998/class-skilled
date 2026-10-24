<div>
    <x-admin.page-header :title="__('admin.account.title')" :subtitle="__('admin.account.subtitle')" />

    <div class="grid gap-6 xl:grid-cols-2">
        <x-admin.panel :title="__('admin.account.profile')">
            <form wire:submit="saveProfile" class="space-y-5">
                <x-admin.input model="name" :label="__('admin.users.name')" required />
                <x-admin.input model="email" type="email" :label="__('admin.users.email')" required />
                <x-admin.select model="locale" :label="__('admin.users.locale')" :options="['ar' => 'العربية', 'en' => 'English']" :placeholder="false" />
                <button type="submit" class="a-btn a-btn-primary">{{ __('admin.common.save') }}</button>
            </form>
        </x-admin.panel>

        <x-admin.panel :title="__('admin.account.password')">
            <form wire:submit="savePassword" class="space-y-5">
                <x-admin.input model="current_password" type="password" :label="__('admin.account.current_password')" required autocomplete="current-password" />
                <x-admin.input model="password" type="password" :label="__('admin.account.new_password')" required autocomplete="new-password" />
                <x-admin.input model="password_confirmation" type="password" :label="__('admin.account.confirm_password')" required autocomplete="new-password" />
                <button type="submit" class="a-btn a-btn-primary">{{ __('admin.common.save') }}</button>
            </form>
        </x-admin.panel>
    </div>
</div>
