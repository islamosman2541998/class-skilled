<x-admin.settings-frame :group="$this->group()" :tabs="$this->tabs()">
    <div class="grid gap-6 xl:grid-cols-2">
        @foreach (\App\Livewire\Admin\Settings\Forms::LISTS as $key)
            <x-admin.panel :title="__('admin.settings.fields.'.$key)" wire:key="list-{{ $key }}">
                <x-admin.repeater model="data.{{ $key }}" :template="['ar' => '', 'en' => '']">
                    @foreach ($data[$key] as $i => $item)
                        <x-admin.repeater-item model="data.{{ $key }}" :index="$i" :item="$item" :title="$item['ar'] ?? ''" :open="false">
                            <div class="grid gap-4 sm:grid-cols-2">
                                <x-admin.input model="data.{{ $key }}.{{ $i }}.ar" label="العربية" required />
                                <x-admin.input model="data.{{ $key }}.{{ $i }}.en" label="English" dir="ltr" />
                            </div>
                        </x-admin.repeater-item>
                    @endforeach
                </x-admin.repeater>
            </x-admin.panel>
        @endforeach

        <x-admin.panel :title="__('admin.settings.sections.uploads_spam')">
            <div class="space-y-5">
                <x-admin.input model="data.max_upload_mb" type="number" :label="__('admin.settings.fields.max_upload_mb')" class="max-w-xs" />
                <x-admin.field :label="__('admin.settings.fields.allowed_extensions')" error="data.allowed_extensions">
                    <div class="flex flex-wrap gap-2">
                        @foreach (\App\Livewire\Admin\Settings\Forms::EXTENSIONS as $ext)
                            <label class="a-chip cursor-pointer" dir="ltr"><input type="checkbox" class="a-check" value="{{ $ext }}" wire:model="data.allowed_extensions"> {{ $ext }}</label>
                        @endforeach
                    </div>
                </x-admin.field>
                <x-admin.toggle model="data.honeypot_enabled" :label="__('admin.settings.fields.honeypot_enabled')" />
                <x-admin.input model="data.turnstile_site_key" :label="__('admin.settings.fields.turnstile_site_key')" dir="ltr" />
                <x-admin.input model="data.turnstile_secret" type="password" :label="__('admin.settings.fields.turnstile_secret')" :hint="__('admin.settings.hints.turnstile')" dir="ltr" />
            </div>
        </x-admin.panel>
    </div>
</x-admin.settings-frame>
