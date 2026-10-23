<x-admin.pair :model="$p.'.title'" :label="__('admin.blocks.fields.title')" required />
<x-admin.pair :model="$p.'.text'" :label="__('admin.blocks.fields.text')" textarea />
<div class="grid gap-5 lg:grid-cols-2">
    @foreach (['primary', 'secondary'] as $button)
        <div class="space-y-3 rounded-xl border border-a-border p-3">
            <x-admin.pair :model="$p.'.'.$button.'.label'" :label="__('admin.blocks.fields.'.$button.'_button')" />
            <x-admin.link-picker :model="$p.'.'.$button.'.link'" :options="$this->linkOptions" />
        </div>
    @endforeach
</div>
