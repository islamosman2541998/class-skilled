<div>
    <x-admin.page-header :title="__('admin.nav.backups')" :subtitle="__('admin.backups.subtitle')"
        :breadcrumbs="[__('admin.nav.system') => null, __('admin.nav.backups') => null]" />

    <div class="grid gap-6 xl:grid-cols-[360px_minmax(0,1fr)]">
        <x-admin.panel :title="__('admin.backups.create_title')">
            <p class="text-[13.5px] text-[var(--a-muted)]">{{ __('admin.backups.note') }}</p>
            <label class="mt-4 flex items-center gap-2 text-[14px]"><input type="checkbox" class="a-check" wire:model="withMedia"> {{ __('admin.backups.with_media') }}</label>
            <button type="button" class="a-btn a-btn-primary mt-5 w-full" wire:click="create" wire:loading.attr="disabled" wire:target="create">
                <span wire:loading.remove wire:target="create">{{ __('admin.backups.create') }}</span>
                <span wire:loading wire:target="create">{{ __('admin.common.loading') }}</span>
            </button>
        </x-admin.panel>

        <x-admin.panel :title="__('admin.backups.list')" :padded="false">
            <table class="a-table">
                <thead><tr><th>{{ __('admin.backups.file') }}</th><th>{{ __('admin.media_library.size') }}</th><th>{{ __('admin.common.date') }}</th><th></th></tr></thead>
                <tbody>
                    @forelse ($backups as $backup)
                        <tr wire:key="b-{{ $backup['name'] }}">
                            <td class="font-mono text-[12.5px]" dir="ltr">{{ $backup['name'] }}</td>
                            <td dir="ltr" class="text-start">{{ \App\Livewire\Admin\Media\Library::humanSize($backup['size']) }}</td>
                            <td class="text-[13px]">{{ \Illuminate\Support\Carbon::createFromTimestamp($backup['date'])->timezone(config('app.timezone'))->format('Y-m-d H:i') }}</td>
                            <td class="whitespace-nowrap text-end">
                                <a href="{{ $backup['url'] }}" class="a-link me-3">{{ __('admin.common.download') }}</a>
                                <button type="button" class="a-link-danger" @click="$confirm({ title: @js(__('admin.common.confirm_delete_title')), danger: true, confirmText: @js(__('admin.common.yes_delete')) }).then(ok => ok && $wire.delete(@js($backup['name'])))">{{ __('admin.common.delete') }}</button>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4"><x-admin.empty-state :title="__('admin.backups.empty')" /></td></tr>
                    @endforelse
                </tbody>
            </table>
        </x-admin.panel>
    </div>
</div>
