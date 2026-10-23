<div x-data="{ dropping: false }"
     @dragover.window.prevent="if ($event.dataTransfer?.types?.includes('Files')) { dropping = true }"
     @dragleave.window.prevent="if (! $event.relatedTarget) dropping = false"
     @drop.window.prevent="dropping = false; if ($event.dataTransfer.files.length) { $wire.set('showUploader', true); $nextTick(() => $dispatch('cs-upload-files', $event.dataTransfer.files)) }">

    <div x-cloak x-show="dropping" class="pointer-events-none fixed inset-0 z-[70] flex items-center justify-center bg-[rgb(79_195_238/.15)] ring-4 ring-inset ring-[var(--c-sky)]">
        <p class="rounded-2xl bg-white px-6 py-4 font-bold shadow-lg">{{ __('admin.media.drop_here') }}</p>
    </div>

    <x-admin.page-header :title="__('admin.nav.library')"
        :subtitle="__('admin.media_library.stats', ['count' => $this->stats['count'], 'size' => \App\Livewire\Admin\Media\Library::humanSize($this->stats['size'])])"
        :breadcrumbs="[__('admin.nav.media') => null, __('admin.nav.library') => null]">
        <x-slot:actions>
            @can('create', \App\Models\Asset::class)
                <button type="button" class="a-btn a-btn-secondary" wire:click="editFolder">{{ __('admin.media_library.new_folder') }}</button>
                <button type="button" class="a-btn a-btn-secondary" x-on:click="$dispatch('open-modal', 'embed-form')">{{ __('admin.media_library.add_embed') }}</button>
                <button type="button" class="a-btn a-btn-primary" wire:click="$toggle('showUploader')">{{ __('admin.media_library.upload') }}</button>
            @endcan
        </x-slot:actions>
    </x-admin.page-header>

    @if ($showUploader)
        <x-admin.panel class="mb-6">
            <x-admin.uploader on-finished="uploadsFinished" />
        </x-admin.panel>
    @endif

    <div class="grid gap-6 xl:grid-cols-[230px_minmax(0,1fr)_340px]">
        {{-- Folders --}}
        <nav class="a-panel h-fit p-2" aria-label="{{ __('admin.media_library.folders') }}">
            @php($folders = $this->folders)
            <button type="button" wire:click="$set('folder', '')" @class(['a-menu-item justify-between', 'bg-[var(--c-navy)] text-white hover:bg-[var(--c-navy)]' => $folder === ''])>
                <span class="font-semibold">{{ __('admin.media.all_folders') }}</span><span class="text-[12px] opacity-70">{{ $this->stats['count'] }}</span>
            </button>
            @foreach ($folders->whereNull('parent_id') as $f)
                @foreach ([$f, ...$folders->where('parent_id', $f->id)->all()] as $item)
                    <div class="group flex items-center" wire:key="folder-{{ $item->id }}">
                        <button type="button" wire:click="$set('folder', '{{ $item->id }}')"
                                @class(['a-menu-item flex-1 justify-between', 'ps-7' => $item->parent_id, 'bg-[var(--c-navy)] text-white hover:bg-[var(--c-navy)]' => $folder === (string) $item->id])>
                            <span class="truncate font-semibold">{{ $item->getTranslation('name', app()->getLocale(), false) ?: $item->getTranslation('name', 'ar') }}</span>
                            <span class="text-[12px] opacity-70">{{ $item->assets_count }}</span>
                        </button>
                        @can('update', $item)
                            <button type="button" class="hidden rounded p-1 text-[var(--a-muted)] hover:bg-a-bg group-hover:block focus:block" wire:click="editFolder({{ $item->id }})" aria-label="{{ __('admin.common.edit') }}">
                                <x-heroicon-m-pencil-square class="h-4 w-4" />
                            </button>
                            <button type="button" class="hidden rounded p-1 text-[var(--a-danger)] hover:bg-red-50 group-hover:block focus:block"
                                    @click="$confirm({ title: @js(__('admin.media_library.delete_folder')), danger: true, confirmText: @js(__('admin.common.yes_delete')) }).then(ok => ok && $wire.deleteFolder({{ $item->id }}))"
                                    aria-label="{{ __('admin.common.delete') }}">
                                <x-heroicon-m-trash class="h-4 w-4" />
                            </button>
                        @endcan
                    </div>
                @endforeach
            @endforeach
            <button type="button" wire:click="$set('folder', 'none')" @class(['a-menu-item', 'bg-[var(--c-navy)] text-white hover:bg-[var(--c-navy)]' => $folder === 'none'])>
                <span class="font-semibold">{{ __('admin.media_library.no_folder') }}</span>
            </button>
        </nav>

        {{-- Assets --}}
        <section class="a-panel min-w-0">
            <div class="space-y-3 border-b border-a-border p-4">
                <div class="flex flex-wrap items-center gap-2">
                    <div class="relative min-w-[200px] flex-1">
                        <x-heroicon-o-magnifying-glass class="pointer-events-none absolute inset-y-0 start-3 my-auto h-4 w-4 text-[var(--a-muted)]" />
                        <input type="search" wire:model.live.debounce.300ms="search" class="a-input ps-9" placeholder="{{ __('admin.media.search') }}" aria-label="{{ __('admin.media.search') }}">
                    </div>
                    <select wire:model.live="kind" class="a-input w-auto" aria-label="{{ __('admin.media.kind') }}">
                        <option value="">{{ __('admin.media.all_kinds') }}</option>
                        @foreach (\App\Enums\AssetKind::cases() as $case)
                            <option value="{{ $case->value }}">{{ $case->label() }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <label class="a-chip cursor-pointer"><input type="checkbox" class="a-check" wire:model.live="missingAlt"> {{ __('admin.media.missing_alt') }}</label>
                    <select wire:model.live="sort" class="a-input h-9 min-h-0 w-auto py-1" aria-label="{{ __('admin.media_library.sort') }}">
                        <option value="newest">{{ __('admin.media_library.sort_newest') }}</option>
                        <option value="oldest">{{ __('admin.media_library.sort_oldest') }}</option>
                        <option value="name">{{ __('admin.media_library.sort_name') }}</option>
                        <option value="size">{{ __('admin.media_library.sort_size') }}</option>
                    </select>
                    <div class="a-segmented ms-auto">
                        <button type="button" aria-pressed="{{ $view === 'grid' ? 'true' : 'false' }}" wire:click="$set('view', 'grid')">{{ __('admin.media_library.grid') }}</button>
                        <button type="button" aria-pressed="{{ $view === 'list' ? 'true' : 'false' }}" wire:click="$set('view', 'list')">{{ __('admin.media_library.list') }}</button>
                    </div>
                </div>
            </div>

            @if ($selected)
                <div class="flex flex-wrap items-center gap-3 border-b border-a-border bg-[#eef1f6] px-4 py-3">
                    <span class="text-[13.5px] font-semibold">{{ __('admin.common.selected', ['count' => count($selected)]) }}</span>
                    <button type="button" class="a-btn a-btn-secondary a-btn-sm" x-on:click="$dispatch('open-modal', 'move-assets')">{{ __('admin.media_library.move') }}</button>
                    <button type="button" class="a-link-danger px-2 text-[13.5px]"
                            @click="$confirm({ title: @js(__('admin.common.confirm_bulk_delete_title')), text: @js(__('admin.media_library.bulk_delete_text')), danger: true, confirmText: @js(__('admin.common.yes_delete')) }).then(ok => ok && $wire.bulkDelete())">{{ __('admin.common.delete') }}</button>
                    <button type="button" class="ms-auto text-[13px] font-semibold text-[var(--a-muted)] hover:underline" wire:click="clearSelection">{{ __('admin.common.deselect') }}</button>
                </div>
            @endif

            @php($items = $this->assets)
            <div class="p-4" wire:loading.class="opacity-60" wire:target="search,kind,missingAlt,sort,folder">
                @if ($items->isEmpty())
                    <x-admin.empty-state :title="__('admin.media.empty')" />
                @elseif ($view === 'grid')
                    <ul class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4">
                        @foreach ($items->take($limit) as $asset)
                            <li wire:key="lib-{{ $asset->id }}" @class(['group relative rounded-xl border-2 bg-white p-1.5', 'border-[var(--c-navy)]' => $active === $asset->id, 'border-transparent' => $active !== $asset->id])>
                                <button type="button" class="block w-full text-start" wire:click="open({{ $asset->id }})">
                                    <x-admin.asset-thumb :asset="$asset" class="aspect-[4/3] w-full" />
                                    <span class="mt-1 block truncate px-1 text-[12.5px]" dir="auto">{{ $asset->original_name ?? $asset->titleText() }}</span>
                                </button>
                                <input type="checkbox" class="a-check absolute start-3 top-3" @checked(in_array($asset->id, $selected, true)) wire:click="toggleSelect({{ $asset->id }})" aria-label="{{ $asset->titleText() }}">
                            </li>
                        @endforeach
                    </ul>
                @else
                    <table class="a-table">
                        <tbody>
                            @foreach ($items->take($limit) as $asset)
                                <tr wire:key="row-{{ $asset->id }}" data-selected="{{ $active === $asset->id ? 'true' : 'false' }}">
                                    <td class="w-8"><input type="checkbox" class="a-check" @checked(in_array($asset->id, $selected, true)) wire:click="toggleSelect({{ $asset->id }})" aria-label="{{ $asset->titleText() }}"></td>
                                    <td class="w-20"><x-admin.asset-thumb :asset="$asset" class="h-12 w-16" /></td>
                                    <td><button type="button" class="text-start font-semibold hover:underline" wire:click="open({{ $asset->id }})" dir="auto">{{ $asset->original_name ?? $asset->titleText() }}</button></td>
                                    <td class="text-[13px] text-[var(--a-muted)]">{{ $asset->kind->label() }}</td>
                                    <td class="text-[13px] text-[var(--a-muted)]" dir="ltr">{{ $asset->size ? \App\Livewire\Admin\Media\Library::humanSize($asset->size) : '—' }}</td>
                                    <td class="text-[13px] text-[var(--a-muted)]">{{ $asset->created_at?->format('Y-m-d') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
            <div class="flex items-center justify-between border-t border-a-border px-4 py-3 text-[13px] text-[var(--a-muted)]">
                <span>{{ __('admin.common.showing', ['from' => $items->isEmpty() ? 0 : 1, 'to' => min($limit, $this->total), 'total' => $this->total]) }}</span>
                @if ($items->count() > $limit)
                    <button type="button" class="a-btn a-btn-secondary a-btn-sm" wire:click="loadMore">{{ __('admin.media.load_more') }}</button>
                @endif
            </div>
        </section>

        {{-- Details --}}
        <aside class="min-w-0">
            @if ($asset = $this->activeAsset)
                <div class="a-panel overflow-hidden" wire:key="details-{{ $asset->id }}">
                    <div class="relative bg-[#eef1f5]">
                        @if ($asset->isImage())
                            <img src="{{ $asset->url('md') }}" alt="{{ $asset->altText() }}" class="max-h-72 w-full object-contain" style="object-position: center {{ $asset->focal }}">
                        @elseif ($asset->kind === \App\Enums\AssetKind::Video)
                            <video src="{{ $asset->url() }}" @if ($asset->poster) poster="{{ $asset->poster->url('md') }}" @endif controls preload="metadata" class="max-h-72 w-full bg-black"></video>
                        @else
                            <x-admin.asset-thumb :asset="$asset" class="h-48 w-full" />
                        @endif
                        <button type="button" class="absolute end-2 top-2 rounded-full bg-white/90 p-1.5 shadow" wire:click="closeDetails" aria-label="{{ __('admin.common.close') }}"><x-heroicon-m-x-mark class="h-4 w-4" /></button>
                    </div>
                    <div class="space-y-4 p-5">
                        <div>
                            <p class="break-all font-bold text-[var(--c-deep)]" dir="auto">{{ $asset->original_name ?? $asset->titleText() }}</p>
                            <dl class="mt-2 grid grid-cols-[auto_1fr] gap-x-4 gap-y-1 text-[13px]">
                                <dt class="text-[var(--a-muted)]">{{ __('admin.media.kind') }}</dt><dd>{{ $asset->kind->label() }}</dd>
                                @if ($asset->width)
                                    <dt class="text-[var(--a-muted)]">{{ __('admin.media_library.dimensions') }}</dt><dd dir="ltr" class="text-start">{{ $asset->width }} × {{ $asset->height }}</dd>
                                @endif
                                @if ($asset->size)
                                    <dt class="text-[var(--a-muted)]">{{ __('admin.media_library.size') }}</dt><dd dir="ltr" class="text-start">{{ \App\Livewire\Admin\Media\Library::humanSize($asset->size) }}</dd>
                                @endif
                                @if ($asset->isImage() && ($media = $asset->file()))
                                    <dt class="text-[var(--a-muted)]">{{ __('admin.media_library.sizes') }}</dt>
                                    <dd dir="ltr" class="text-start">WebP {{ collect(array_keys(\App\Models\Asset::WIDTHS))->filter(fn ($c) => $media->hasGeneratedConversion($c))->map(fn ($c) => $asset->conversionWidth($c))->unique()->implode(' · ') }}</dd>
                                @endif
                                @if ($asset->duration)
                                    <dt class="text-[var(--a-muted)]">{{ __('admin.media_library.duration') }}</dt><dd dir="ltr" class="text-start">{{ gmdate($asset->duration >= 3600 ? 'H:i:s' : 'i:s', $asset->duration) }}</dd>
                                @endif
                                @if ($asset->kind === \App\Enums\AssetKind::Video && $asset->size > app(\App\Settings\MediaSettings::class)->max_video_mb * 1048576)
                                    <dd class="col-span-2 font-semibold text-[var(--a-warning)]">{{ __('admin.media_library.video_large') }}</dd>
                                @endif
                            </dl>
                        </div>

                        <div>
                            <p class="a-label">{{ __('admin.media_library.used_in') }}</p>
                            @php($usages = $this->usageList($asset))
                            @forelse ($usages as $usage)
                                <p class="text-[13px]"><span class="text-[var(--a-muted)]">{{ $usage['label'] }}:</span>
                                    @if ($usage['url'])<a href="{{ $usage['url'] }}" wire:navigate class="font-semibold hover:underline">{{ $usage['title'] }}</a>@else<span class="font-semibold">{{ $usage['title'] }}</span>@endif
                                </p>
                            @empty
                                <p class="text-[13px] text-[var(--a-muted)]">{{ __('admin.media_library.unused') }}</p>
                            @endforelse
                        </div>

                        @can('update', $asset)
                            <form wire:submit="saveDetails" class="space-y-4">
                                <x-admin.locale-tabs :missing="$this->missingEnglishDetails()">
                                    <x-slot:ar>
                                        <x-admin.textarea model="details.alt.ar" :label="__('admin.media_library.alt')" :required="$asset->isImage()" rows="2" />
                                        <x-admin.input model="details.title.ar" :label="__('admin.media_library.title')" />
                                        <x-admin.input model="details.caption.ar" :label="__('admin.media_library.caption')" />
                                    </x-slot:ar>
                                    <x-slot:en>
                                        <x-admin.textarea model="details.alt.en" :label="__('admin.media_library.alt')" rows="2" />
                                        <x-admin.input model="details.title.en" :label="__('admin.media_library.title')" />
                                        <x-admin.input model="details.caption.en" :label="__('admin.media_library.caption')" />
                                    </x-slot:en>
                                </x-admin.locale-tabs>
                                @if ($asset->isImage())
                                    <x-admin.segmented model="details.focal" :label="__('admin.media_library.focal')" :options="['top' => __('admin.media_library.focal_top'), 'center' => __('admin.media_library.focal_center'), 'bottom' => __('admin.media_library.focal_bottom')]" />
                                @endif
                                <x-admin.select model="details.folder_id" :label="__('admin.media_library.folder')" :options="$this->folders->mapWithKeys(fn ($f) => [$f->id => $f->getTranslation('name', app()->getLocale(), false) ?: $f->getTranslation('name', 'ar')])->all()" :placeholder="__('admin.media_library.no_folder')" />
                                <button type="submit" class="a-btn a-btn-primary w-full">{{ __('admin.common.save') }}</button>
                            </form>

                            <div class="flex flex-wrap gap-2 border-t border-a-border pt-4">
                                @if ($url = $asset->url())
                                    <button type="button" class="a-btn a-btn-secondary a-btn-sm" x-on:click="navigator.clipboard?.writeText(@js($url)).then(() => window.csToast('success', @js(__('admin.common.copied'))))">{{ __('admin.media_library.copy_url') }}</button>
                                    @if ($asset->kind !== \App\Enums\AssetKind::Embed)
                                        <a href="{{ $url }}" download class="a-btn a-btn-secondary a-btn-sm">{{ __('admin.media_library.download') }}</a>
                                    @endif
                                @endif
                                @if ($asset->kind !== \App\Enums\AssetKind::Embed)
                                    <label class="a-btn a-btn-secondary a-btn-sm cursor-pointer">
                                        <span wire:loading.remove wire:target="replacement">{{ __('admin.media_library.replace') }}</span>
                                        <span wire:loading wire:target="replacement">{{ __('admin.media.processing') }}</span>
                                        <input type="file" class="sr-only" wire:model="replacement">
                                    </label>
                                @endif
                                @can('delete', $asset)
                                    @php($used = $usages !== [])
                                    <button type="button" class="a-link-danger ms-auto px-2 text-[13.5px]"
                                            @click="$confirm({ title: @js(__('admin.media_library.delete_title')), text: @js($used ? __('admin.media_library.in_use_force') : __('admin.media_library.delete_text')), danger: true, confirmText: @js(__('admin.common.yes_delete')) }).then(ok => ok && $wire.delete({{ $asset->id }}, @js($used)))">
                                        {{ __('admin.common.delete') }}
                                    </button>
                                @endcan
                            </div>
                        @endcan
                    </div>
                </div>
            @else
                <div class="a-panel p-6 text-center text-[13.5px] text-[var(--a-muted)]">{{ __('admin.media_library.pick_hint') }}</div>
            @endif
        </aside>
    </div>

    <x-admin.modal name="folder-form" :title="$folderForm['id'] ? __('admin.media_library.edit_folder') : __('admin.media_library.new_folder')">
        <form wire:submit="saveFolder" class="space-y-4" id="folder-form">
            <x-admin.input model="folderForm.name.ar" :label="__('admin.media_library.folder_name').' (العربية)'" required />
            <x-admin.input model="folderForm.name.en" :label="__('admin.media_library.folder_name').' (English)'" dir="ltr" />
            <x-admin.select model="folderForm.parent_id" :label="__('admin.media_library.parent_folder')" :options="$this->folders->whereNull('parent_id')->where('id', '!=', $folderForm['id'])->mapWithKeys(fn ($f) => [$f->id => $f->getTranslation('name', 'ar')])->all()" :placeholder="__('admin.common.none')" />
        </form>
        <x-slot:footer>
            <button type="button" class="a-btn a-btn-secondary" x-on:click="$dispatch('close-modal', 'folder-form')">{{ __('admin.common.cancel') }}</button>
            <button type="submit" form="folder-form" class="a-btn a-btn-primary">{{ __('admin.common.save') }}</button>
        </x-slot:footer>
    </x-admin.modal>

    <x-admin.modal name="move-assets" :title="__('admin.media_library.move')">
        <select wire:model="moveTo" class="a-input" aria-label="{{ __('admin.media_library.folder') }}">
            <option value="none">{{ __('admin.media_library.no_folder') }}</option>
            @foreach ($this->folders as $f)
                <option value="{{ $f->id }}">{{ $f->getTranslation('name', 'ar') }}</option>
            @endforeach
        </select>
        <x-slot:footer>
            <button type="button" class="a-btn a-btn-secondary" x-on:click="$dispatch('close-modal', 'move-assets')">{{ __('admin.common.cancel') }}</button>
            <button type="button" class="a-btn a-btn-primary" wire:click="bulkMove">{{ __('admin.media_library.move') }}</button>
        </x-slot:footer>
    </x-admin.modal>

    <x-admin.modal name="embed-form" :title="__('admin.media_library.add_embed')" width="max-w-xl">
        <form wire:submit="saveEmbed" class="space-y-4" id="embed-form">
            <x-admin.input model="embed.url" type="url" :label="__('admin.media_library.embed_url')" placeholder="https://www.youtube.com/watch?v=…" required />
            <x-admin.input model="embed.title.ar" :label="__('admin.media_library.title').' (العربية)'" required />
            <x-admin.input model="embed.title.en" :label="__('admin.media_library.title').' (English)'" dir="ltr" />
            <livewire:admin.asset-field wire:model="embed.poster" kind="image" :label="__('admin.media_library.poster')" :required="true" :error="$errors->first('embed.poster')" />
        </form>
        <x-slot:footer>
            <button type="button" class="a-btn a-btn-secondary" x-on:click="$dispatch('close-modal', 'embed-form')">{{ __('admin.common.cancel') }}</button>
            <button type="submit" form="embed-form" class="a-btn a-btn-primary">{{ __('admin.common.save') }}</button>
        </x-slot:footer>
    </x-admin.modal>
</div>
