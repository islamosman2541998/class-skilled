{{--
    Request e-mail shell: table layout with inline styles (Gmail / Outlook / mobile clients),
    direction from the e-mail locale, brand header and company footer.
    <x-email.layout :locale="$locale" :site="$site" :preheader="…">…<x-slot:note>…</x-slot:note></x-email.layout>
--}}
@props(['locale' => 'ar', 'site', 'preheader' => '', 'note' => null])
@php
    $rtl = $locale === 'ar';
    $dir = $rtl ? 'rtl' : 'ltr';
    $align = $rtl ? 'right' : 'left';
    $font = "'Segoe UI', Tahoma, Arial, sans-serif";
@endphp
<!DOCTYPE html>
<html lang="{{ $locale }}" dir="{{ $dir }}" xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="color-scheme" content="light">
    <meta name="supported-color-schemes" content="light">
    <title>{{ $site['name'] }}</title>
    <style>
        @media only screen and (max-width: 620px) {
            .cs-card { width: 100% !important; border-radius: 0 !important; }
            .cs-pad { padding-left: 20px !important; padding-right: 20px !important; }
            .cs-btn { display: block !important; width: 100% !important; box-sizing: border-box; margin: 0 0 8px !important; }
            .cs-label, .cs-value { display: block !important; width: 100% !important; padding-bottom: 2px !important; }
        }
        a { color: #2B4168; }
    </style>
</head>
<body dir="{{ $dir }}" style="margin:0; padding:0; background:#F5F7FA; font-family:{{ $font }}; color:#24324D; -webkit-text-size-adjust:100%;">
    @if ($preheader !== '')
        <div style="display:none; max-height:0; overflow:hidden; opacity:0; mso-hide:all;">{{ $preheader }}</div>
    @endif
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#F5F7FA;">
        <tr>
            <td align="center" style="padding:28px 12px;">
                <table role="presentation" class="cs-card" width="600" cellpadding="0" cellspacing="0" style="width:600px; max-width:600px; background:#FFFFFF; border:1px solid #E3E8EF; border-radius:16px; overflow:hidden;">
                    <tr>
                        <td class="cs-pad" dir="{{ $dir }}" style="background:#1B2A47; padding:22px 32px; text-align:{{ $align }};">
                            <a href="{{ $site['url'] }}" style="text-decoration:none; color:#FFFFFF;">
                                <img src="{{ $site['logo'] }}" width="44" height="30" alt="" style="display:inline-block; vertical-align:middle; border:0;">
                                <span style="display:inline-block; vertical-align:middle; margin-{{ $rtl ? 'right' : 'left' }}:10px; font-size:17px; font-weight:700; color:#FFFFFF;">{{ $site['name'] }}</span>
                            </a>
                        </td>
                    </tr>
                    <tr>
                        <td style="height:4px; background:#4FC3EE; font-size:0; line-height:0;">&nbsp;</td>
                    </tr>
                    <tr>
                        <td class="cs-pad" dir="{{ $dir }}" style="padding:28px 32px 8px; text-align:{{ $align }}; font-size:15px; line-height:1.8;">
                            {{ $slot }}
                        </td>
                    </tr>
                    <tr>
                        <td class="cs-pad" dir="{{ $dir }}" style="padding:20px 32px 26px; background:#F8FAFC; border-top:1px solid #E8ECF1; text-align:{{ $align }}; font-size:13px; line-height:1.8; color:#5D6A80;">
                            <strong style="color:#2B4168;">{{ $site['name'] }}</strong>
                            @if ($site['address'] !== '')<br>{{ $site['address'] }}@endif
                            @if ($site['phones'] !== [])<br>@foreach ($site['phones'] as $phone)<bdi dir="ltr">{{ $phone }}</bdi>@if (! $loop->last) &nbsp;·&nbsp; @endif @endforeach @endif
                            @if ($site['emails'] !== [])<br>@foreach ($site['emails'] as $email)<a href="mailto:{{ $email }}" style="color:#2B4168;">{{ $email }}</a>@if (! $loop->last) &nbsp;·&nbsp; @endif @endforeach @endif
                            @if ($note)<br><span style="color:#8A95A8;">{{ $note }}</span>@endif
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
