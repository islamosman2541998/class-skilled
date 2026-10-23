<x-layouts::auth :title="__('admin.auth.reset_title')">
    <h1 class="text-[28px] font-extrabold leading-tight text-[var(--c-deep)]">{{ __('admin.auth.reset_title') }}</h1>

    <form method="POST" action="{{ route('password.update') }}" class="mt-8 space-y-5">
        @csrf
        <input type="hidden" name="token" value="{{ $request->route('token') }}">
        <div>
            <label for="email" class="a-label">{{ __('admin.auth.email') }}</label>
            <input id="email" name="email" type="email" dir="ltr" value="{{ old('email', $request->email) }}" required class="a-input h-12" @error('email') aria-invalid="true" @enderror>
            @error('email')<p class="a-error">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="password" class="a-label">{{ __('admin.auth.new_password') }}</label>
            <input id="password" name="password" type="password" dir="ltr" required autocomplete="new-password" class="a-input h-12" @error('password') aria-invalid="true" @enderror>
            @error('password')<p class="a-error">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="password_confirmation" class="a-label">{{ __('admin.auth.confirm_password') }}</label>
            <input id="password_confirmation" name="password_confirmation" type="password" dir="ltr" required autocomplete="new-password" class="a-input h-12">
        </div>
        <button type="submit" class="a-btn a-btn-primary h-12 w-full">{{ __('admin.auth.reset') }}</button>
    </form>
</x-layouts::auth>
