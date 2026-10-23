<x-admin.settings-frame :group="$this->group()" :tabs="$this->tabs()">
    <x-admin.panel :title="__('admin.settings.sections.system')">
        <div class="grid gap-5 md:grid-cols-2">
            <x-admin.input model="data.admin_session_minutes" type="number" :label="__('admin.settings.fields.admin_session_minutes')" :hint="__('admin.settings.hints.admin_session_minutes')" />
            <x-admin.input model="data.backup_keep" type="number" :label="__('admin.settings.fields.backup_keep')" />
        </div>
    </x-admin.panel>
</x-admin.settings-frame>
