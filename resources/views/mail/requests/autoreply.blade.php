<x-mail::message>
{!! nl2br(e($body)) !!}

<x-slot:subcopy>
{{ __('mail.footer') }}
</x-slot:subcopy>
</x-mail::message>
