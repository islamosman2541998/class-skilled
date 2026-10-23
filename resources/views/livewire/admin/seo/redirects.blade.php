<div>
    <x-admin.page-header :title="__('admin.nav.redirects')" :subtitle="__('admin.redirects.subtitle')"
        :breadcrumbs="[__('admin.nav.seo') => null, __('admin.nav.redirects') => null]">
        <x-slot:actions>
            @can('create', \App\Models\Redirect::class)
                <label class="a-btn a-btn-secondary cursor-pointer">
                    {{ __('admin.redirects.import') }}
                    <input type="file" accept=".csv,text/csv" class="sr-only" wire:model="csv">
                </label>
            @endcan
            <button type="button" class="a-btn a-btn-secondary" wire:click="export">{{ __('admin.common.export') }}</button>
            @can('create', \App\Models\Redirect::class)
                <button type="button" class="a-btn a-btn-primary" wire:click="edit">{{ __('admin.redirects.add') }}</button>
            @endcan
        </x-slot:actions>
    </x-admin.page-header>

    @php($rows = $this->rows)
    <div class="a-panel">
        <div class="flex flex-wrap items-center gap-2 border-b border-a-border p-4">
            <x-admin.table.search />
            <x-admin.table.filter key="auto" :label="__('admin.redirects.type')" :options="['1' => __('admin.redirects.auto'), '0' => __('admin.redirects.manual')]" :value="$filters['auto'] ?? null" />
        </div>
        <x-admin.table.bulk-bar :count="$this->selectedCount()" :total="$rows->total()" :all="$selectAllMatching">
            <button type="button" class="a-btn a-btn-secondary a-btn-sm" wire:click="exportSelected">{{ __('admin.common.export_selected') }}</button>
            <button type="button" class="a-link-danger px-2 text-[13.5px]"
                    @click="$confirm({ title: @js(__('admin.common.confirm_bulk_delete_title')), danger: true, confirmText: @js(__('admin.common.yes_delete')) }).then(ok => ok && $wire.bulkDelete())">{{ __('admin.common.delete') }}</button>
        </x-admin.table.bulk-bar>
        <div class="overflow-x-auto">
            <table class="a-table">
                <thead>
                    <tr>
                        <th class="w-10"><input type="checkbox" class="a-check" aria-label="{{ __('admin.common.select_page') }}" wire:click="{{ count($selected) && count($selected) >= $rows->count() ? 'clearSelection' : 'selectPage' }}" @checked($rows->count() && count($selected) >= $rows->count())></th>
                        <x-admin.table.th field="from" :sort="$this->currentSort()">{{ __('admin.redirects.from') }}</x-admin.table.th>
                        <th>{{ __('admin.redirects.to') }}</th>
                        <th>{{ __('admin.redirects.code') }}</th>
                        <x-admin.table.th field="hits" :sort="$this->currentSort()">{{ __('admin.redirects.hits') }}</x-admin.table.th>
                        <x-admin.table.th field="last_hit" :sort="$this->currentSort()">{{ __('admin.redirects.last_hit') }}</x-admin.table.th>
                        <th>{{ __('admin.redirects.type') }}</th>
                        <th><span class="sr-only">{{ __('admin.common.actions') }}</span></th>
                    </tr>
                </thead>
                <tbody wire:loading.class="opacity-60">
                    @forelse ($rows as $redirect)
                        <tr wire:key="r-{{ $redirect->id }}">
                            <td><input type="checkbox" class="a-check" value="{{ $redirect->id }}" wire:model.live="selected" aria-label="{{ $redirect->from_path }}"></td>
                            <td class="max-w-xs break-all font-mono text-[12.5px]" dir="ltr">{{ $redirect->from_path }}</td>
                            <td class="max-w-xs break-all font-mono text-[12.5px]" dir="ltr">{{ $redirect->to_path }}</td>
                            <td><x-admin.badge color="navy" :dot="false">{{ $redirect->status_code }}</x-admin.badge></td>
                            <td dir="ltr" class="text-start">{{ $redirect->hits }}</td>
                            <td class="text-[13px] text-[var(--a-muted)]">{{ $redirect->last_hit_at?->diffForHumans() ?? '—' }}</td>
                            <td><x-admin.badge :color="$redirect->is_auto ? 'sky' : 'gray'">{{ $redirect->is_auto ? __('admin.redirects.auto') : __('admin.redirects.manual') }}</x-admin.badge></td>
                            <td class="whitespace-nowrap text-end">
                                <button type="button" class="a-link me-3" wire:click="edit({{ $redirect->id }})">{{ __('admin.common.edit') }}</button>
                                <button type="button" class="a-link-danger" @click="$confirm({ title: @js(__('admin.common.confirm_delete_title')), danger: true, confirmText: @js(__('admin.common.yes_delete')) }).then(ok => ok && $wire.delete({{ $redirect->id }}))">{{ __('admin.common.delete') }}</button>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8"><x-admin.empty-state :title="__('admin.redirects.empty')" :text="__('admin.redirects.empty_text')" /></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <x-admin.table.pagination :paginator="$rows" />
    </div>

    <x-admin.modal name="redirect-form" :title="$form['id'] ? __('admin.redirects.edit') : __('admin.redirects.add')" width="max-w-xl">
        <form id="redirect-form" wire:submit="save" class="space-y-4">
            <x-admin.input model="form.from_path" :label="__('admin.redirects.from')" :hint="__('admin.redirects.from_hint')" dir="ltr" required />
            <x-admin.input model="form.to_path" :label="__('admin.redirects.to')" :hint="__('admin.redirects.to_hint')" dir="ltr" required />
            <x-admin.segmented model="form.status_code" :label="__('admin.redirects.code')" :options="[301 => '301 — '.__('admin.redirects.permanent'), 302 => '302 — '.__('admin.redirects.temporary')]" />
        </form>
        <x-slot:footer>
            <button type="button" class="a-btn a-btn-secondary" x-on:click="$dispatch('close-modal', 'redirect-form')">{{ __('admin.common.cancel') }}</button>
            <button type="submit" form="redirect-form" class="a-btn a-btn-primary">{{ __('admin.common.save') }}</button>
        </x-slot:footer>
    </x-admin.modal>
</div>
