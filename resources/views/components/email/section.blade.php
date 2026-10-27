{{-- Titled block of label / value rows. <x-email.section :title="…" :rows="['Label' => 'Value']" :rtl="true" /> --}}
@props(['title', 'rows' => [], 'rtl' => true])
@php($align = $rtl ? 'right' : 'left')
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:0 0 22px; border:1px solid #E8ECF1; border-radius:12px; border-collapse:separate; overflow:hidden;">
    <tr>
        <td colspan="2" style="padding:11px 16px; background:#F5F7FA; border-bottom:1px solid #E8ECF1; text-align:{{ $align }}; font-size:14px; font-weight:700; color:#2B4168;">{{ $title }}</td>
    </tr>
    @foreach ($rows as $label => $value)
        <tr>
            <td class="cs-label" width="38%" valign="top" style="padding:10px 16px; {{ $loop->last ? '' : 'border-bottom:1px solid #EEF1F5;' }} text-align:{{ $align }}; font-size:13.5px; color:#5D6A80;">{{ $label }}</td>
            <td class="cs-value" valign="top" style="padding:10px 16px; {{ $loop->last ? '' : 'border-bottom:1px solid #EEF1F5;' }} text-align:{{ $align }}; font-size:14.5px; font-weight:600; color:#24324D;"><bdi>{{ $value }}</bdi></td>
        </tr>
    @endforeach
</table>
