{{-- Team notification for a new visitor request (quote / contact message / job application). Data: App\Support\RequestMail::notification() --}}
@php($rtl = $locale === 'ar')
<x-email.layout :locale="$locale" :site="$site" :preheader="$title.' '.$number" :note="__('mail.footer', [], $locale)">
    <h1 style="margin:0; font-size:22px; line-height:1.4; font-weight:800; color:#1B2A47;">{{ $title }}</h1>
    <p style="margin:10px 0 22px; font-size:13.5px; color:#5D6A80;">
        <span style="display:inline-block; padding:3px 12px; border-radius:9999px; background:#EAF7FD; color:#2B4168; font-weight:700;"><bdi dir="ltr">{{ $number }}</bdi></span>
        &nbsp; {{ $date }}
    </p>

    <div style="margin:0 0 18px;">
        @foreach ($actions as $action)
            <x-email.button :href="$action['href']">{{ $action['label'] }}</x-email.button>
        @endforeach
        <x-email.button :href="$dashboardUrl" :primary="true">{{ __('mail.open_dashboard', [], $locale) }}</x-email.button>
    </div>

    @foreach ($sections as $section)
        <x-email.section :title="$section['title']" :rows="$section['rows']" :rtl="$rtl" />
    @endforeach

    @if ($long)
        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:0 0 22px; border:1px solid #E8ECF1; border-radius:12px; border-collapse:separate;">
            <tr><td style="padding:11px 16px; background:#F5F7FA; border-bottom:1px solid #E8ECF1; font-size:14px; font-weight:700; color:#2B4168;">{{ $long['title'] }}</td></tr>
            <tr><td dir="auto" style="padding:14px 16px; font-size:14.5px; line-height:1.9; color:#24324D;">{!! nl2br(e($long['body'])) !!}</td></tr>
        </table>
    @endif

    @if ($files !== [])
        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:0 0 22px; border:1px solid #E8ECF1; border-radius:12px; border-collapse:separate;">
            <tr><td colspan="2" style="padding:11px 16px; background:#F5F7FA; border-bottom:1px solid #E8ECF1; font-size:14px; font-weight:700; color:#2B4168;">{{ __('mail.attachments', [], $locale) }} ({{ count($files) }})</td></tr>
            @foreach ($files as $file)
                <tr>
                    <td dir="ltr" style="padding:9px 16px; border-bottom:1px solid #EEF1F5; font-size:14px; color:#24324D; text-align:{{ $rtl ? 'right' : 'left' }};">{{ $file['name'] }}</td>
                    <td dir="ltr" width="90" style="padding:9px 16px; border-bottom:1px solid #EEF1F5; font-size:13px; color:#5D6A80; text-align:{{ $rtl ? 'left' : 'right' }};">{{ $file['size'] }}</td>
                </tr>
            @endforeach
            <tr><td colspan="2" style="padding:10px 16px; font-size:13px; color:#5D6A80;">{{ $filesAttached ? __('mail.attachments_attached', [], $locale) : __('mail.attachments_in_dashboard', [], $locale) }}</td></tr>
        </table>
    @endif

    @if ($meta !== [])
        <x-email.section :title="__('mail.section_more', [], $locale)" :rows="$meta" :rtl="$rtl" />
    @endif
</x-email.layout>
