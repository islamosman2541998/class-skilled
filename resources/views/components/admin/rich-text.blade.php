{{--
    Rich text editor (TipTap) bound to an HTML string property; purified server-side.
    <x-admin.rich-text model="form.body.ar" :label="…" dir="rtl" />
    Toolbar: H2, H3, bold, italic, bullet/ordered list, link, quote, undo/redo.
--}}
@props(['model', 'label' => null, 'required' => false, 'hint' => null, 'dir' => 'rtl', 'placeholder' => null])
@php($tools = [['h2', 'H2'], ['h3', 'H3'], ['bold', 'B'], ['italic', 'I'], ['bullet', '•'], ['ordered', '1.'], ['quote', '❝']])
<x-admin.field :label="$label" :error="$model" :required="$required" :hint="$hint" {{ $attributes->only('class') }}>
    <div wire:ignore
         x-data="richEditor({ dir: @js($dir), placeholder: @js($placeholder ?? __('admin.editor.placeholder')) })"
         x-modelable="value"
         wire:model="{{ $model }}"
         class="a-editor overflow-hidden rounded-[10px] border border-a-border bg-white focus-within:border-[var(--c-sky)] focus-within:ring-3 focus-within:ring-[var(--c-sky)]/25">
        <div class="a-editor-toolbar flex flex-wrap items-center gap-0.5 border-b border-a-border bg-[#fafbfc] px-1.5 py-1" role="toolbar">
            @foreach ($tools as [$command, $text])
                <button type="button" @click="run('{{ $command }}')" :aria-pressed="active.{{ $command }} ? 'true' : 'false'" title="{{ __('admin.editor.'.$command) }}" aria-label="{{ __('admin.editor.'.$command) }}">{{ $text }}</button>
            @endforeach
            <button type="button" @click="link(@js(__('admin.editor.link_prompt')))" :aria-pressed="active.link ? 'true' : 'false'" title="{{ __('admin.editor.link') }}" aria-label="{{ __('admin.editor.link') }}">
                <x-heroicon-m-link class="mx-auto h-4 w-4" />
            </button>
            <span class="mx-1 h-5 w-px bg-a-border" aria-hidden="true"></span>
            <button type="button" @click="run('undo')" title="{{ __('admin.editor.undo') }}" aria-label="{{ __('admin.editor.undo') }}"><x-heroicon-m-arrow-uturn-left class="mx-auto h-4 w-4 rtl:-scale-x-100" /></button>
            <button type="button" @click="run('redo')" title="{{ __('admin.editor.redo') }}" aria-label="{{ __('admin.editor.redo') }}"><x-heroicon-m-arrow-uturn-right class="mx-auto h-4 w-4 rtl:-scale-x-100" /></button>
        </div>
        <div x-ref="editor" dir="{{ $dir }}"></div>
    </div>
</x-admin.field>
