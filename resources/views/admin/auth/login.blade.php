<x-layouts::auth :title="__('admin.auth.login_title')">
    <h1 class="text-[30px] font-extrabold leading-tight text-[var(--c-deep)]">{{ __('admin.auth.login_title') }}</h1>
    <p class="mt-2 text-[var(--a-muted)]">{{ __('admin.auth.login_subtitle') }}</p>

    @if (session('status'))
        <div class="mt-6 rounded-xl bg-emerald-50 px-4 py-3 text-[14px] text-emerald-800" role="status">{{ session('status') }}</div>
    @endif

    <form method="POST" action="{{ route('login') }}" class="mt-8 space-y-5" x-data="{ busy: false }" @submit="busy = true">
        @csrf
        <div>
            <label for="email" class="a-label">{{ __('admin.auth.email') }}</label>
            <input id="email" name="email" type="email" dir="ltr" value="{{ old('email') }}" required autofocus autocomplete="username"
                   class="a-input h-12" @error('email') aria-invalid="true" aria-describedby="email-error" @enderror>
            @error('email')<p id="email-error" class="a-error">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="password" class="a-label">{{ __('admin.auth.password') }}</label>
            <input id="password" name="password" type="password" dir="ltr" required autocomplete="current-password"
                   class="a-input h-12" @error('password') aria-invalid="true" @enderror>
            @error('password')<p class="a-error">{{ $message }}</p>@enderror
        </div>
        <div class="flex items-center justify-between gap-4">
            <label class="flex items-center gap-2 text-[14px]">
                <input type="checkbox" name="remember" value="1" class="a-check" @checked(old('remember', true))>
                {{ __('admin.auth.remember') }}
            </label>
            <a href="{{ route('password.request') }}" class="text-[14px] font-bold text-[var(--c-deep)] hover:underline">{{ __('admin.auth.forgot') }}</a>
        </div>
        <button type="submit" class="a-btn a-btn-primary h-12 w-full text-[15px]" :disabled="busy">{{ __('admin.auth.login') }}</button>
        <p class="text-[13px] text-[var(--a-muted)]">{{ __('admin.auth.two_factor_note') }}</p>
    </form>
</x-layouts::auth>
