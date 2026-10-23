{{--
    Footer: "showing x–y of z", rows per page and page buttons (WithDataTable).
    <x-admin.table.pagination :paginator="$this->rows" />
--}}
@props(['paginator'])
@php
    $current = $paginator->currentPage();
    $last = $paginator->lastPage();
    $pages = collect(range(max(1, $current - 2), min($last, $current + 2)));
    if (! $pages->contains(1)) { $pages->prepend(1); }
    if (! $pages->contains($last)) { $pages->push($last); }
@endphp
<div class="flex flex-wrap items-center justify-between gap-3 border-t border-a-border px-4 py-3 text-[13.5px]">
    <p class="text-[var(--a-muted)]">
        @if ($paginator->total() > 0)
            {{ __('admin.common.showing', ['from' => $paginator->firstItem(), 'to' => $paginator->lastItem(), 'total' => $paginator->total()]) }}
        @endif
    </p>
    <div class="flex flex-wrap items-center gap-3">
        <label class="flex items-center gap-2 text-[var(--a-muted)]">
            {{ __('admin.common.per_page') }}
            <select wire:model.live="perPage" class="a-input h-9 min-h-0 w-20 py-1">
                <option value="10">10</option>
                <option value="25">25</option>
                <option value="50">50</option>
            </select>
        </label>
        @if ($last > 1)
            <nav class="flex items-center gap-1" aria-label="pagination">
                @php($previous = null)
                @foreach ($pages as $page)
                    @if ($previous !== null && $page - $previous > 1)
                        <span class="px-1 text-[var(--a-muted)]">…</span>
                    @endif
                    <button type="button" wire:click="gotoPage({{ $page }})"
                            @class(['h-9 min-w-9 rounded-lg border px-2 font-semibold', 'border-[var(--c-navy)] bg-[var(--c-navy)] text-white' => $page === $current, 'border-a-border bg-white hover:bg-a-bg' => $page !== $current])
                            @if ($page === $current) aria-current="page" @endif>{{ $page }}</button>
                    @php($previous = $page)
                @endforeach
            </nav>
        @endif
    </div>
</div>
