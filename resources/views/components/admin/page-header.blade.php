{{--
    Page header: breadcrumbs, title, subtitle, actions.
    <x-admin.page-header :title="__('admin.users.title')" :subtitle="…" :breadcrumbs="[__('admin.nav.system') => null, __('admin.nav.users') => route('admin.users.index')]">
        <x-slot:actions><a class="a-btn a-btn-primary" href="…">…</a></x-slot:actions>
    </x-admin.page-header>
--}}
@props(['title', 'subtitle' => null, 'breadcrumbs' => []])
<div {{ $attributes->merge(['class' => 'mb-6 flex flex-wrap items-end justify-between gap-4']) }}>
    <div class="min-w-0">
        @if ($breadcrumbs)
            <nav aria-label="breadcrumbs" class="mb-1.5 text-[13px] text-[var(--a-muted)]">
                <ol class="flex flex-wrap items-center gap-1.5">
                    @foreach ($breadcrumbs as $label => $url)
                        <li class="flex items-center gap-1.5">
                            @if (! $loop->first)<span aria-hidden="true">/</span>@endif
                            @if ($url)
                                <a href="{{ $url }}" wire:navigate class="hover:underline">{{ $label }}</a>
                            @else
                                <span>{{ $label }}</span>
                            @endif
                        </li>
                    @endforeach
                </ol>
            </nav>
        @endif
        <h1 class="text-[26px] font-extrabold leading-tight text-[var(--c-deep)]">{{ $title }}</h1>
        @if ($subtitle)
            <p class="mt-1 text-[14px] text-[var(--a-muted)]">{{ $subtitle }}</p>
        @endif
    </div>
    @isset($actions)
        <div class="flex flex-wrap items-center gap-2">{{ $actions }}</div>
    @endisset
</div>
