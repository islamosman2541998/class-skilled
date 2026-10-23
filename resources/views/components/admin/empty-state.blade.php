{{--
    Empty state with optional call to action.
    <x-admin.empty-state :title="…" :text="…"><a class="a-btn a-btn-primary" …>…</a></x-admin.empty-state>
--}}
@props(['title', 'text' => null])
<div {{ $attributes->merge(['class' => 'flex flex-col items-center px-6 py-14 text-center']) }}>
    <p class="font-[family-name:var(--font-head)] text-[17px] font-extrabold text-[var(--c-deep)]">{{ $title }}</p>
    @if ($text)<p class="mt-1.5 max-w-md text-[14px] text-[var(--a-muted)]">{{ $text }}</p>@endif
    @if (trim($slot) !== '')<div class="mt-5 flex gap-2">{{ $slot }}</div>@endif
</div>
