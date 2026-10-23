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

        <x-admin.panel :title="__('admin.account.two_factor')" id="two-factor" class="xl:col-span-2">
            <p class="text-[14px] text-[var(--a-muted)]">{{ __('admin.account.two_factor_text') }}</p>
            <p class="mt-3">
                <x-admin.badge :color="$enabled ? 'green' : 'gray'">{{ $enabled ? __('admin.account.two_factor_on') : __('admin.account.two_factor_off') }}</x-admin.badge>
            </p>

            @if ($pending)
                <div class="mt-5 grid gap-6 md:grid-cols-[220px_minmax(0,1fr)]">
                    <div class="rounded-xl border border-a-border bg-white p-3 [&_svg]:h-auto [&_svg]:w-full">{!! $qr !!}</div>
                    <div class="space-y-4">
                        <p class="text-[14px]">{{ __('admin.account.scan') }}</p>
                        <p class="text-[13px] text-[var(--a-muted)]">{{ __('admin.account.setup_key') }}: <code class="rounded bg-a-bg px-2 py-0.5 font-mono" dir="ltr">{{ $secret }}</code></p>
                        <form wire:submit="confirmTwoFactor" class="flex flex-wrap items-end gap-3">
                            <x-admin.input model="code" :label="__('admin.account.confirm_code')" inputmode="numeric" autocomplete="one-time-code" dir="ltr" class="w-48" />
                            <button type="submit" class="a-btn a-btn-primary">{{ __('admin.account.confirm') }}</button>
                        </form>
                    </div>
                </div>
            @else
                <div class="mt-5 flex flex-wrap items-end gap-3">
                    <x-admin.input model="twoFactorPassword" type="password" :label="__('admin.account.current_password')" :hint="__('admin.account.enter_password')" autocomplete="current-password" class="w-72" />
                    @if ($enabled)
                        <button type="button" class="a-btn a-btn-secondary" wire:click="showRecoveryCodes">{{ __('admin.account.show_codes') }}</button>
                        <button type="button" class="a-btn a-btn-secondary" wire:click="regenerateRecoveryCodes">{{ __('admin.account.regenerate') }}</button>
                        <button type="button" class="a-btn a-btn-danger"
                                @click="$confirm({ title: @js(__('admin.account.disable_confirm')), danger: true, confirmText: @js(__('admin.account.disable')) }).then(ok => ok && $wire.disableTwoFactor())">
                            {{ __('admin.account.disable') }}
                        </button>
                    @else
                        <button type="button" class="a-btn a-btn-primary" wire:click="enableTwoFactor">{{ __('admin.account.enable') }}</button>
                    @endif
                </div>
            @endif

            @if ($recoveryCodes)
                <div class="mt-6 rounded-xl border border-a-border bg-[#fafbfc] p-4">
                    <p class="font-bold">{{ __('admin.account.recovery_codes') }}</p>
                    <p class="text-[13px] text-[var(--a-muted)]">{{ __('admin.account.recovery_text') }}</p>
                    <ul class="mt-3 grid gap-1 font-mono text-[13.5px] sm:grid-cols-2" dir="ltr">
                        @foreach ($recoveryCodes as $recovery)
                            <li>{{ $recovery }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </x-admin.panel>
    </div>
</div>
