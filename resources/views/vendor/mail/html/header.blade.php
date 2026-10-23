@props(['url'])
<tr>
<td class="header">
<a href="{{ $url }}" style="display: inline-block;">
<img src="{{ asset('images/brand/mark-white.png') }}" class="logo" alt="" width="58" height="40">
<span class="brand">{{ $slot }}</span>
</a>
</td>
</tr>
