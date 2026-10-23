<x-layouts::auth :title="__('admin.auth.challenge_title')">
    <div x-data="{ recovery: {{ $errors->has('recovery_code') ? 'true' : 'false' }} }">
        <h1 class="text-[28px] font-extrabold leading-tight text-[var(--c-deep)]">{{ __('admin.auth.challenge_title') }}</h1>
        <p class="mt-2 text-[var(--a-muted)]" x-show="!recovery">{{ __('admin.auth.challenge_text') }}</p>
        <p class="mt-2 text-[var(--a-muted)]" x-show="recovery" x-cloak>{{ __('admin.auth.challenge_recovery_text') }}</p>

        <form method="POST" action="{{ route('two-factor.login') }}" class="mt-8 space-y-5">
            @csrf
            <div x-show="!recovery">
                <label for="code" class="a-label">{{ __('admin.auth.code') }}</label>
                <input id="code" name="code" type="text" inputmode="numeric" autocomplete="one-time-code" dir="ltr" autofocus
                       class="a-input h-12 text-center tracking-[0.4em]" x-bind:disabled="recovery" @error('code') aria-invalid="true" @enderror>
                @error('code')<p class="a-error">{{ $message }}</p>@enderror
            </div>
            <div x-show="recovery" x-cloak>
                <label for="recovery_code" class="a-label">{{ __('admin.auth.recovery_code') }}</label>
                <input id="recovery_code" name="recovery_code" type="text" autocomplete="one-time-code" dir="ltr"
                       class="a-input h-12" x-bind:disabled="!recovery" @error('recovery_code') aria-invalid="true" @enderror>
                @error('recovery_code')<p class="a-error">{{ $message }}</p>@enderror
            </div>
            <button type="submit" class="a-btn a-btn-primary h-12 w-full">{{ __('admin.auth.verify') }}</button>
            <button type="button" class="block w-full text-center text-[14px] font-bold text-[var(--c-deep)] hover:underline" @click="recovery = !recovery">
                <span x-show="!recovery">{{ __('admin.auth.use_recovery') }}</span>
                <span x-show="recovery" x-cloak>{{ __('admin.auth.use_code') }}</span>
            </button>
        </form>
    </div>
</x-layouts::auth>
