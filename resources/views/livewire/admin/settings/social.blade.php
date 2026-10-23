<x-admin.settings-frame :group="$this->group()" :tabs="$this->tabs()">
    <x-admin.panel :title="__('admin.settings.sections.social')">
        <div class="grid gap-5 md:grid-cols-2">
            @foreach (\App\Livewire\Admin\Settings\Social::NETWORKS as $network)
                <x-admin.input model="data.{{ $network }}" type="url" :label="__('admin.settings.networks.'.$network)" placeholder="https://" />
            @endforeach
        </div>
        <p class="a-hint mt-4">{{ __('admin.settings.hints.social') }}</p>
    </x-admin.panel>
</x-admin.settings-frame>
