{{-- Confirmation to the visitor in their language. Data: App\Support\RequestMail::autoreply() --}}
@php($rtl = $locale === 'ar')
<x-email.layout :locale="$locale" :site="$site" :preheader="$title.' '.$number" :note="__('mail.autoreply_reason', [], $locale)">
    <h1 style="margin:0; font-size:22px; line-height:1.4; font-weight:800; color:#1B2A47;">{{ $title }}</h1>
    <p style="margin:10px 0 20px; font-size:13.5px; color:#5D6A80;">
        {{ __('mail.reference', [], $locale) }}:
        <span style="display:inline-block; padding:3px 12px; border-radius:9999px; background:#EAF7FD; color:#2B4168; font-weight:700;"><bdi dir="ltr">{{ $number }}</bdi></span>
    </p>

    <p style="margin:0 0 22px; font-size:15px; line-height:1.9; color:#24324D;">{!! nl2br(e($body)) !!}</p>

    @if ($summary !== [])
        <x-email.section :title="__('mail.your_request', [], $locale)" :rows="$summary" :rtl="$rtl" />
    @endif

    @if ($site['phoneUrl'] || $site['whatsappUrl'])
        <p style="margin:0 0 10px; font-size:14px; font-weight:700; color:#2B4168;">{{ __('mail.contact_us', [], $locale) }}</p>
        <div style="margin:0 0 18px;">
            @if ($site['whatsappUrl'])<x-email.button :href="$site['whatsappUrl']" :primary="true">{{ __('mail.action_whatsapp', [], $locale) }}</x-email.button>@endif
            @if ($site['phoneUrl'])<x-email.button :href="$site['phoneUrl']">{{ __('mail.action_call', [], $locale) }}</x-email.button>@endif
        </div>
    @endif
</x-email.layout>
