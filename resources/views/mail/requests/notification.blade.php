<x-mail::message>
# {{ $number }}

<x-mail::table>
| | |
|:--|:--|
@foreach ($fields as $label => $value)
| **{{ $label }}** | {{ str_replace(["\r", "\n", '|'], [' ', ' ', '/'], $value) }} |
@endforeach
</x-mail::table>

<x-mail::button :url="$url">
{{ __('mail.open_dashboard') }}
</x-mail::button>

<x-slot:subcopy>
{{ __('mail.footer') }}
</x-slot:subcopy>
</x-mail::message>
