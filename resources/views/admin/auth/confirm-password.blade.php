<x-layouts::auth :title="__('admin.auth.confirm_title')">
    <h1 class="text-[28px] font-extrabold leading-tight text-[var(--c-deep)]">{{ __('admin.auth.confirm_title') }}</h1>
    <p class="mt-2 text-[var(--a-muted)]">{{ __('admin.auth.confirm_text') }}</p>

    <form method="POST" action="{{ route('password.confirm') }}" class="mt-8 space-y-5">
        @csrf
        <div>
            <label for="password" class="a-label">{{ __('admin.auth.password') }}</label>
            <input id="password" name="password" type="password" dir="ltr" required autofocus autocomplete="current-password" class="a-input h-12" @error('password') aria-invalid="true" @enderror>
            @error('password')<p class="a-error">{{ $message }}</p>@enderror
        </div>
        <button type="submit" class="a-btn a-btn-primary h-12 w-full">{{ __('admin.auth.confirm') }}</button>
    </form>
</x-layouts::auth>
