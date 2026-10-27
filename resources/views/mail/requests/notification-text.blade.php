{{ $title }} — {{ $number }}
{{ $date }}

@foreach ($sections as $section)
{{ $section['title'] }}
@foreach ($section['rows'] as $label => $value)
- {{ $label }}: {{ $value }}
@endforeach

@endforeach
@if ($long)
{{ $long['title'] }}
{{ $long['body'] }}

@endif
@if ($files !== [])
{{ __('mail.attachments', [], $locale) }}
@foreach ($files as $file)
- {{ $file['name'] }} ({{ $file['size'] }})
@endforeach
{{ $filesAttached ? __('mail.attachments_attached', [], $locale) : __('mail.attachments_in_dashboard', [], $locale) }}

@endif
@foreach ($meta as $label => $value)
{{ $label }}: {{ $value }}
@endforeach

{{ __('mail.open_dashboard', [], $locale) }}: {{ $dashboardUrl }}

{{ __('mail.footer', [], $locale) }}
