<div>
    @if ($open)
        <div class="a-modal-backdrop" x-data @keydown.escape.window="$wire.close()" @click.self="$wire.close()">
            <div class="a-modal flex max-h-[92vh] max-w-5xl flex-col" role="dialog" aria-modal="true" aria-label="{{ __('admin.media.pick_title') }}">
                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-a-border px-5 py-3">
                    <h2 class="a-panel-title">{{ __('admin.media.pick_title') }}</h2>
                    <div class="a-segmented" role="tablist">
                        <button type="button" role="tab" aria-selected="{{ $tab === 'library' ? 'true' : 'false' }}" wire:click="$set('tab', 'library')">{{ __('admin.media.library') }}</button>
                        @can('create', \App\Models\Asset::class)
                            <button type="button" role="tab" aria-selected="{{ $tab === 'upload' ? 'true' : 'false' }}" wire:click="$set('tab', 'upload')">{{ __('admin.media.upload_new') }}</button>
                        @endcan
                    </div>
                    <button type="button" class="rounded-lg p-1.5 hover:bg-a-bg" wire:click="close" aria-label="{{ __('admin.common.close') }}">
                        <x-heroicon-o-x-mark class="h-5 w-5" />
                    </button>
                </div>

                {{-- Library --}}
                <div @class(['flex min-h-0 flex-1 flex-col', 'hidden' => $tab !== 'library'])>
                    <div class="flex flex-wrap items-center gap-2 border-b border-a-border px-5 py-3">
                        <div class="relative min-w-[200px] flex-1">
                            <x-heroicon-o-magnifying-glass class="pointer-events-none absolute inset-y-0 start-3 my-auto h-4 w-4 text-[var(--a-muted)]" />
                            <input type="search" wire:model.live.debounce.300ms="search" class="a-input ps-9" placeholder="{{ __('admin.media.search') }}" aria-label="{{ __('admin.media.search') }}">
                        </div>
                        <select wire:model.live="folder" class="a-input w-auto" aria-label="{{ __('admin.media.all_folders') }}">
                            <option value="">{{ __('admin.media.all_folders') }}</option>
                            @foreach ($this->folders as $f)
                                <option value="{{ $f->id }}">{{ $f->getTranslation('name', app()->getLocale(), false) ?: $f->getTranslation('name', 'ar') }}</option>
                            @endforeach
                        </select>
                        <label class="a-chip cursor-pointer">
                            <input type="checkbox" wire:model.live="missingAlt" class="a-check"> {{ __('admin.media.missing_alt') }}
                        </label>
                    </div>
                    <div class="min-h-0 flex-1 overflow-y-auto p-5">
                        @php($items = $this->assets)
                        @if ($items->isEmpty())
                            <x-admin.empty-state :title="__('admin.media.empty')" />
                        @else
                            <ul class="grid grid-cols-2 gap-3 sm:grid-cols-4 lg:grid-cols-6">
                                @foreach ($items->take($limit) as $asset)
                                    @php($isSelected = in_array($asset->id, $selected, true))
                                    <li wire:key="pick-{{ $asset->id }}">
                                        <button type="button" wire:click="toggle({{ $asset->id }})" aria-pressed="{{ $isSelected ? 'true' : 'false' }}"
                                                @class(['group relative block w-full overflow-hidden rounded-xl border-2 text-start', 'border-[var(--c-sky)] ring-2 ring-[var(--c-sky)]/30' => $isSelected, 'border-transparent hover:border-a-border' => ! $isSelected])>
                                            <x-admin.asset-thumb :asset="$asset" class="aspect-[4/3]" />
                                            @if ($isSelected)
                                                <span class="absolute end-2 top-2 flex h-6 w-6 items-center justify-center rounded-full bg-[var(--c-sky)] text-[var(--c-deep)]">
                                                    @if ($multiple){{ array_search($asset->id, $selected, true) + 1 }}@else<x-heroicon-m-check class="h-4 w-4" />@endif
                                                </span>
                                            @endif
                                            <span class="block truncate px-1 pt-1 text-[12px] text-[var(--a-muted)]">{{ $asset->titleText() }}</span>
                                        </button>
                                    </li>
                                @endforeach
                            </ul>
                            @if ($items->count() > $limit)
                                <div class="mt-4 text-center">
                                    <button type="button" class="a-btn a-btn-secondary a-btn-sm" wire:click="loadMore">{{ __('admin.media.load_more') }}</button>
                                </div>
                            @endif
                        @endif
                    </div>
                </div>

                {{-- Upload --}}
                @can('create', \App\Models\Asset::class)
                    <x-admin.uploader :kind="$this->kinds()" on-finished="uploadsFinished" :class="\Illuminate\Support\Arr::toCssClasses(['min-h-0 flex-1 overflow-y-auto p-5', 'hidden' => $tab !== 'upload'])" />
                @endcan

                <div class="flex items-center justify-between gap-3 border-t border-a-border px-5 py-3">
                    <span class="text-[13px] text-[var(--a-muted)]">{{ __('admin.media.selected_count', ['count' => count($selected)]) }}</span>
                    <div class="flex gap-2">
                        <button type="button" class="a-btn a-btn-secondary" wire:click="close">{{ __('admin.common.cancel') }}</button>
                        <button type="button" class="a-btn a-btn-primary" wire:click="confirm">{{ $multiple ? __('admin.media.choose_count', ['count' => count($selected)]) : __('admin.media.choose') }}</button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
