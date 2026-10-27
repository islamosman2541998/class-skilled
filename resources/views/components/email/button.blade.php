{{-- Pill link button. <x-email.button href="…" :primary="true">Label</x-email.button> --}}
@props(['href', 'primary' => false])
<a href="{{ $href }}" class="cs-btn" style="display:inline-block; margin:0 3px 8px; padding:11px 20px; border-radius:9999px; font-size:14px; font-weight:700; line-height:1.2; text-align:center; text-decoration:none; {{ $primary ? 'background:#4FC3EE; color:#1B2A47; border:1px solid #4FC3EE;' : 'background:#FFFFFF; color:#2B4168; border:1px solid #C9D3E3;' }}">{{ $slot }}</a>
