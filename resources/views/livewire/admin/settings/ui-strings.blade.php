<div>
    <x-admin.page-header :title="__('admin.nav.ui_strings')" :subtitle="__('admin.ui_strings.subtitle')"
        :breadcrumbs="[__('admin.nav.settings') => null, __('admin.nav.ui_strings') => null]">
        <x-slot:actions>
            <label class="a-btn a-btn-secondary cursor-pointer">
                {{ __('admin.ui_strings.import') }}
                <input type="file" accept="application/json,.json" class="sr-only" wire:model="importFile">
            </label>
            <button type="button" class="a-btn a-btn-secondary" wire:click="exportJson">{{ __('admin.ui_strings.export') }}</button>
        </x-slot:actions>
    </x-admin.page-header>

    @php($rows = $this->rows)
    <div class="a-panel">
        <div class="flex flex-wrap items-center gap-2 border-b border-a-border p-4">
            <x-admin.table.search :placeholder="__('admin.ui_strings.search')" />
            <x-admin.table.filter key="group" :label="__('admin.ui_strings.group')" :options="$groups" :value="$filters['group'] ?? null" />
            <x-admin.table.filter key="missing" :label="__('admin.common.translation')" :options="['1' => __('admin.common.missing_english')]" :value="$filters['missing'] ?? null" />
            @if ($filters || $search)
                <button type="button" wire:click="clearFilters" class="text-[13.5px] font-bold text-[var(--c-deep)] hover:underline">{{ __('admin.common.clear_filters') }}</button>
            @endif
        </div>
        <div class="overflow-x-auto">
            <table class="a-table">
                <thead>
                    <tr>
                        <x-admin.table.th field="key" :sort="$this->currentSort()">{{ __('admin.ui_strings.key') }}</x-admin.table.th>
                        <th>العربية</th>
                        <th>English</th>
                        <th><span class="sr-only">{{ __('admin.common.actions') }}</span></th>
                    </tr>
                </thead>
                <tbody wire:loading.class="opacity-60">
                    @forelse ($rows as $line)
                        <tr wire:key="line-{{ $line->id }}">
                            <td class="align-top">
                                <p class="font-mono text-[12.5px] font-semibold" dir="ltr">{{ $line->group }}.{{ $line->key }}</p>
                            </td>
                            @if ($editing === $line->id)
                                <td class="align-top" colspan="2">
                                    <form wire:submit="save" class="grid gap-3 md:grid-cols-2">
                                        <x-admin.textarea model="text.ar" rows="2" dir="rtl" required />
                                        <x-admin.textarea model="text.en" rows="2" dir="ltr" />
                                        <div class="flex gap-2 md:col-span-2">
                                            <button type="submit" class="a-btn a-btn-primary a-btn-sm">{{ __('admin.common.save') }}</button>
                                            <button type="button" class="a-btn a-btn-secondary a-btn-sm" wire:click="cancel">{{ __('admin.common.cancel') }}</button>
                                            @if ($this->hasDefault($line->group, $line->key))
                                                <button type="button" class="a-btn a-btn-ghost a-btn-sm ms-auto"
                                                        @click="$confirm({ title: @js(__('admin.ui_strings.reset_confirm')) }).then(ok => ok && $wire.resetDefault({{ $line->id }}))">{{ __('admin.ui_strings.reset') }}</button>
                                            @endif
                                        </div>
                                    </form>
                                </td>
                                <td></td>
                            @else
                                <td class="max-w-sm align-top" dir="rtl">{!! \App\Livewire\Admin\Settings\UiStrings::highlight((string) ($line->text['ar'] ?? '')) !!}</td>
                                <td class="max-w-sm align-top" dir="ltr">
                                    @if (filled($line->text['en'] ?? null))
                                        {!! \App\Livewire\Admin\Settings\UiStrings::highlight((string) $line->text['en']) !!}
                                    @else
                                        <x-admin.badge color="amber">{{ __('admin.common.translation_missing') }}</x-admin.badge>
                                    @endif
                                </td>
                                <td class="text-end align-top"><button type="button" class="a-link" wire:click="edit({{ $line->id }})">{{ __('admin.common.edit') }}</button></td>
                            @endif
                        </tr>
                    @empty
                        <tr><td colspan="4"><x-admin.empty-state :title="__('admin.common.no_results')" /></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <x-admin.table.pagination :paginator="$rows" />
    </div>
</div>
