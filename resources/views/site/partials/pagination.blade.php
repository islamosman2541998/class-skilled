{{-- Livewire pagination (no reloads) with site UI strings. --}}
@if ($paginator->hasPages())
    <nav class="mt-10 flex flex-wrap items-center justify-center gap-2" aria-label="{{ __('site.pagination') }}">
        <button type="button" class="chip" wire:click="previousPage('{{ $paginator->getPageName() }}')" @disabled($paginator->onFirstPage()) aria-label="{{ __('site.previous') }}">
            <x-heroicon-m-chevron-left class="size-4 ltr:rotate-0 rtl:rotate-180" aria-hidden="true" /> {{ __('site.previous') }}
        </button>
        @foreach ($elements as $element)
            @if (is_string($element))
                <span class="px-2 text-muted" aria-hidden="true">{{ $element }}</span>
            @endif
            @if (is_array($element))
                @foreach ($element as $page => $url)
                    <button type="button" class="chip min-w-10 justify-center" wire:click="gotoPage({{ $page }}, '{{ $paginator->getPageName() }}')" aria-pressed="{{ $page === $paginator->currentPage() ? 'true' : 'false' }}" @if ($page === $paginator->currentPage()) aria-current="page" @endif>
                        <bdi>{{ $page }}</bdi>
                    </button>
                @endforeach
            @endif
        @endforeach
        <button type="button" class="chip" wire:click="nextPage('{{ $paginator->getPageName() }}')" @disabled(! $paginator->hasMorePages()) aria-label="{{ __('site.next') }}">
            {{ __('site.next') }} <x-heroicon-m-chevron-right class="size-4 rtl:rotate-180" aria-hidden="true" />
        </button>
    </nav>
@endif
