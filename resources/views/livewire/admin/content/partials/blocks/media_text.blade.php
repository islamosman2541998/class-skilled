<div class="grid gap-5 lg:grid-cols-[220px_minmax(0,1fr)]">
    <div class="space-y-4">
        <livewire:admin.asset-field wire:model="{{ $p }}.asset_id" kind="image" :label="__('admin.blocks.fields.image')" wire:key="mt-{{ $block['id'] }}-{{ $i }}" :error="$errors->first($p.'.asset_id')" />
        <x-admin.segmented :model="$p.'.image_side'" :label="__('admin.blocks.fields.image_side')" :options="['start' => __('admin.blocks.fields.side_start'), 'end' => __('admin.blocks.fields.side_end')]" />
    </div>
    <div class="space-y-4">
        <x-admin.pair :model="$p.'.title'" :label="__('admin.blocks.fields.title')" />
        <x-admin.locale-tabs :label="__('admin.blocks.fields.text')" :missing="$this->missingEnglish([$block['data']['html'] ?? []])">
            <x-slot:ar><x-admin.rich-text :model="$p.'.html.ar'" dir="rtl" /></x-slot:ar>
            <x-slot:en><x-admin.rich-text :model="$p.'.html.en'" dir="ltr" /></x-slot:en>
        </x-admin.locale-tabs>
    </div>
</div>
