{{-- Accessible accordion: native <details>. $items = list of {q, a}. --}}
<div class="divide-y divide-line rounded-[14px] border border-line bg-white">
    @foreach ($items as $item)
        <details class="group" @if (isset($item['key'])) data-faq="{{ $item['key'] }}" @endif>
            <summary class="flex min-h-14 cursor-pointer list-none items-center justify-between gap-4 px-5 py-4 font-bold text-navy [&::-webkit-details-marker]:hidden">
                <span>{{ $item['q'] }}</span>
                <x-heroicon-m-chevron-down class="size-5 shrink-0 text-muted transition-transform group-open:rotate-180" aria-hidden="true" />
            </summary>
            <div class="px-5 pb-5 text-muted">{!! nl2br(e($item['a'])) !!}</div>
        </details>
    @endforeach
</div>
