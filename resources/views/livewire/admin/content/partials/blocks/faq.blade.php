<x-admin.pair :model="$p.'.title'" :label="__('admin.blocks.fields.title')" />
<x-admin.select :model="$p.'.faq_ids'" multiple :label="__('admin.blocks.fields.faq_pick')" :hint="__('admin.blocks.fields.faq_pick_hint')"
    :options="\App\Models\Faq::query()->general()->ordered()->get()->mapWithKeys(fn ($f) => [$f->id => $f->getTranslation('question', 'ar')])->all()" />
<x-admin.repeater :model="$p.'.items'" :label="__('admin.blocks.fields.faq_items')" :template="['q' => ['ar' => '', 'en' => ''], 'a' => ['ar' => '', 'en' => '']]">
    @foreach ($block['data']['items'] ?? [] as $j => $item)
        <x-admin.repeater-item :model="$p.'.items'" :index="$j" :item="$item" :title="$item['q']['ar'] ?? ''">
            <x-admin.pair :model="$p.'.items.'.$j.'.q'" :label="__('admin.blocks.fields.question')" required />
            <x-admin.pair :model="$p.'.items.'.$j.'.a'" :label="__('admin.blocks.fields.answer')" textarea rows="3" required />
        </x-admin.repeater-item>
    @endforeach
</x-admin.repeater>
