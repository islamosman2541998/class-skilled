<x-layouts::auth :title="__('admin.auth.forgot_title')">
    <h1 class="text-[28px] font-extrabold leading-tight text-[var(--c-deep)]">{{ __('admin.auth.forgot_title') }}</h1>
    <p class="mt-2 text-[var(--a-muted)]">{{ __('admin.auth.forgot_text') }}</p>

    @if (session('status'))
        <div class="mt-6 rounded-xl bg-emerald-50 px-4 py-3 text-[14px] text-emerald-800" role="status">{{ session('status') }}</div>
    @endif

    <form method="POST" action="{{ route('password.email') }}" class="mt-8 space-y-5">
        @csrf
        <div>
            <label for="email" class="a-label">{{ __('admin.auth.email') }}</label>
            <input id="email" name="email" type="email" dir="ltr" value="{{ old('email') }}" required autofocus class="a-input h-12" @error('email') aria-invalid="true" @enderror>
            @error('email')<p class="a-error">{{ $message }}</p>@enderror
        </div>
        <button type="submit" class="a-btn a-btn-primary h-12 w-full">{{ __('admin.auth.send_link') }}</button>
        <a href="{{ route('login') }}" class="block text-center text-[14px] font-bold text-[var(--c-deep)] hover:underline">{{ __('admin.auth.back_to_login') }}</a>
    </form>
</x-layouts::auth>
