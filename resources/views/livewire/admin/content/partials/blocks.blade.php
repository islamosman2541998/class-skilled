{{-- Block editor (WithBlockEditor). --}}
<x-admin.panel :title="__('admin.blocks.title')" :subtitle="__('admin.blocks.subtitle')">
    <x-slot:actions>
        <div x-data="{ open: false }" class="relative" @click.outside="open = false" @keydown.escape="open = false">
            <button type="button" class="a-btn a-btn-secondary a-btn-sm" @click="open = ! open" :aria-expanded="open">
                <x-heroicon-m-plus class="h-4 w-4" /> {{ __('admin.blocks.add') }}
            </button>
            <div x-cloak x-show="open" x-transition class="a-popover end-0 top-10 w-80 max-h-96 overflow-y-auto">
                @foreach (\App\Enums\BlockType::cases() as $type)
                    <button type="button" class="a-menu-item flex-col items-start gap-0.5" wire:click="addBlock('{{ $type->value }}')" @click="open = false">
                        <span class="font-bold">{{ $type->label() }}</span>
                        <span class="text-[12px] text-[var(--a-muted)]">{{ __('admin.blocks.descriptions.'.$type->value) }}</span>
                    </button>
                @endforeach
            </div>
        </div>
    </x-slot:actions>

    @if ($blocks === [])
        <p class="py-6 text-center text-[13.5px] text-[var(--a-muted)]">{{ __('admin.blocks.empty') }}</p>
    @endif

    <div class="space-y-3" x-sortable.handle="sortBlocks">
        @foreach ($blocks as $i => $block)
            <div wire:key="block-{{ $block['id'] }}-{{ $i }}" data-sort-id="{{ $block['id'] }}" x-data="{ open: {{ count($blocks) <= 2 ? 'true' : 'false' }} }"
                 x-on:block-added.window="if ($event.detail.id === @js($block['id'])) { open = true; $nextTick(() => $el.scrollIntoView({ behavior: 'smooth', block: 'center' })) }"
                 class="rounded-xl border border-a-border bg-[#fafbfc]">
                <div class="flex items-center gap-2 px-3 py-2.5">
                    <button type="button" data-sort-handle class="a-drag-handle rounded p-1" aria-label="{{ __('admin.common.drag') }}"><x-heroicon-m-bars-2 class="h-4 w-4" /></button>
                    <button type="button" class="flex min-w-0 flex-1 items-center gap-3 text-start" @click="open = ! open" :aria-expanded="open">
                        <span class="shrink-0 font-bold text-[var(--c-deep)]">{{ \App\Enums\BlockType::from($block['type'])->label() }}</span>
                        <span class="truncate text-[13px] text-[var(--a-muted)]">{{ $this->blockSummary($block) }}</span>
                    </button>
                    <button type="button" class="rounded px-2 py-1 text-[13px] text-[var(--a-muted)] hover:bg-white" @click="open = ! open" x-text="open ? @js(__('admin.common.collapse')) : @js(__('admin.common.expand'))"></button>
                    <button type="button" class="rounded px-2 py-1 text-[13px] text-[var(--a-muted)] hover:bg-white" wire:click="duplicateBlock({{ $i }})">{{ __('admin.blocks.duplicate') }}</button>
                    <button type="button" class="rounded px-2 py-1 text-[13px] text-[var(--a-danger)] hover:bg-red-50"
                            @click="$confirm({ title: @js(__('admin.blocks.delete_confirm')), danger: true, confirmText: @js(__('admin.common.yes_delete')) }).then(ok => ok && $wire.removeBlock({{ $i }}))">{{ __('admin.common.delete') }}</button>
                </div>
                <div x-show="open" x-collapse class="space-y-4 border-t border-a-border bg-white px-4 py-4">
                    @include('livewire.admin.content.partials.blocks.'.$block['type'], ['i' => $i, 'block' => $block, 'p' => 'blocks.'.$i.'.data'])
                </div>
            </div>
        @endforeach
    </div>
</x-admin.panel>
