{{--
    Status badge. Colors: sky, amber, violet, navy, green, red, gray.
    <x-admin.badge color="green">{{ __('…') }}</x-admin.badge>
--}}
@props(['color' => 'gray', 'dot' => true])
<span {{ $attributes->class(['a-badge', 'a-badge-'.$color, 'no-dot' => ! $dot]) }}>{{ $slot }}</span>
