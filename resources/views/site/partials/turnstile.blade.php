{{-- Cloudflare Turnstile (only when keys are set in Settings → Forms); the token is passed to the Livewire form. --}}
<div class="mt-5" wire:ignore x-data x-init="
    window.csTurnstile = (token) => $wire.set('turnstileToken', token, false);
    if (! document.getElementById('cf-turnstile-js')) {
        const s = document.createElement('script');
        s.id = 'cf-turnstile-js';
        s.src = 'https://challenges.cloudflare.com/turnstile/v0/api.js';
        s.async = true;
        document.head.appendChild(s);
    }
">
    <div class="cf-turnstile" data-sitekey="{{ $siteKey }}" data-callback="csTurnstile" data-language="{{ app()->getLocale() }}"></div>
</div>
