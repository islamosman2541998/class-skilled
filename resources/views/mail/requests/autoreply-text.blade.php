{{ $title }}
{{ __('mail.reference', [], $locale) }}: {{ $number }}

{{ $body }}

@if ($summary !== [])
{{ __('mail.your_request', [], $locale) }}
@foreach ($summary as $label => $value)
- {{ $label }}: {{ $value }}
@endforeach

@endif
{{ __('mail.contact_us', [], $locale) }}
@foreach ($site['phones'] as $phone)
{{ $phone }}
@endforeach
@if ($site['whatsappUrl'])
{{ __('mail.action_whatsapp', [], $locale) }}: {{ $site['whatsappUrl'] }}
@endif

{{ $site['name'] }}
{{ __('mail.autoreply_reason', [], $locale) }}
