<div class="grid gap-5 xl:grid-cols-2">
    @foreach (['left', 'right'] as $side)
        <div class="space-y-4 rounded-xl border border-a-border p-3">
            <p class="font-bold">{{ __('admin.blocks.fields.column_'.$side) }}</p>
            <x-admin.pair :model="$p.'.'.$side.'.title'" :label="__('admin.blocks.fields.title')" required />
            <x-admin.locale-tabs :missing="$this->missingEnglish([$block['data'][$side]['html'] ?? []])">
                <x-slot:ar><x-admin.rich-text :model="$p.'.'.$side.'.html.ar'" dir="rtl" /></x-slot:ar>
                <x-slot:en><x-admin.rich-text :model="$p.'.'.$side.'.html.en'" dir="ltr" /></x-slot:en>
            </x-admin.locale-tabs>
            <x-admin.toggle :model="$p.'.'.$side.'.dark'" :label="__('admin.blocks.fields.dark')" />
        </div>
    @endforeach
</div>
