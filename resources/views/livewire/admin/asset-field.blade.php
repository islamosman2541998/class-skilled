<div class="min-w-0">
    @if ($label)
        <p class="a-label">{{ $label }}@if ($required)<span class="a-required" aria-hidden="true">*</span>@endif</p>
    @endif

    @php($assets = $this->assets)

    @if ($assets->isNotEmpty())
        <ul @class(['grid gap-3', 'grid-cols-3 sm:grid-cols-4 lg:grid-cols-5' => $multiple, 'max-w-[220px]' => ! $multiple])
            @if ($multiple) x-data x-sortable="reorder" @endif>
            @foreach ($assets as $asset)
                <li wire:key="{{ $fieldId }}-{{ $asset->id }}" data-sort-id="{{ $asset->id }}" class="group relative">
                    <x-admin.asset-thumb :asset="$asset" @class(['aspect-[4/3] w-full', 'cursor-grab' => $multiple]) />
                    <p class="mt-1 truncate text-[12px] text-[var(--a-muted)]">{{ $asset->titleText() }}</p>
                    <button type="button" wire:click="remove({{ $asset->id }})"
                            class="absolute end-1.5 top-1.5 rounded-full bg-white/90 p-1 text-[var(--a-danger)] shadow opacity-90 hover:opacity-100"
                            aria-label="{{ __('admin.media.remove') }}">
                        <x-heroicon-m-x-mark class="h-4 w-4" />
                    </button>
                </li>
            @endforeach
        </ul>
    @endif

    <button type="button" @class(['a-btn a-btn-secondary a-btn-sm', 'mt-3' => $assets->isNotEmpty()])
            x-on:click="$dispatch('open-asset-picker', { target: @js($fieldId), multiple: @js($multiple), kind: @js($kind), selected: @js($multiple ? [] : $this->ids()) })">
        <x-heroicon-m-photo class="h-4 w-4" />
        {{ $assets->isNotEmpty() && ! $multiple ? __('admin.media.replace') : __('admin.media.pick') }}
    </button>

    @if ($hint)<p class="a-hint">{{ $hint }}</p>@endif
    @if ($error)<p class="a-error" role="alert">{{ $error }}</p>@endif
</div>
