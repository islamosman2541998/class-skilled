{{--
    Alpine modal opened with $dispatch('open-modal', 'name') and closed with $dispatch('close-modal', 'name').
    From Livewire: $this->dispatch('open-modal', name: '…').
    <x-admin.modal name="diff" :title="…" width="max-w-2xl"> … <x-slot:footer>…</x-slot:footer></x-admin.modal>
--}}
@props(['name', 'title' => null, 'width' => 'max-w-lg'])
<div
    x-data="{ open: false }"
    x-on:open-modal.window="($event.detail === '{{ $name }}' || $event.detail?.name === '{{ $name }}') && (open = true)"
    x-on:close-modal.window="($event.detail === '{{ $name }}' || $event.detail?.name === '{{ $name }}') && (open = false)"
    x-on:keydown.escape.window="open = false"
>
        <div x-cloak x-show="open" x-transition.opacity class="a-modal-backdrop" @click.self="open = false">
            <div class="a-modal {{ $width }}" role="dialog" aria-modal="true" @if ($title) aria-label="{{ $title }}" @endif x-trap.noscroll="open">
                @if ($title)
                    <div class="flex items-center justify-between gap-4 border-b border-a-border px-6 py-4">
                        <h2 class="a-panel-title">{{ $title }}</h2>
                        <button type="button" class="rounded-lg p-1.5 hover:bg-a-bg" @click="open = false" aria-label="{{ __('admin.common.close') }}">
                            <x-heroicon-o-x-mark class="h-5 w-5" />
                        </button>
                    </div>
                @endif
                <div {{ $attributes->merge(['class' => 'px-6 py-5']) }}>{{ $slot }}</div>
                @isset($footer)
                    <div class="flex justify-end gap-2 border-t border-a-border px-6 py-4">{{ $footer }}</div>
                @endisset
            </div>
        </div>
</div>
