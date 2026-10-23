<div class="grid gap-4 sm:grid-cols-3">
    <x-admin.input model="section.data.autoplay" type="number" min="4" max="10" :label="__('admin.home.autoplay_seconds')" />
    <x-admin.toggle model="section.data.show_arrows" :label="__('admin.home.show_arrows')" />
    <x-admin.toggle model="section.data.show_progress" :label="__('admin.home.show_progress')" />
</div>
<x-admin.repeater model="section.data.slides" :label="__('admin.home.slides')" :add-label="__('admin.home.add_slide')"
    :template="['image_asset_id' => null, 'mobile_image_asset_id' => null, 'video_asset_id' => null, 'title' => ['ar' => '', 'en' => ''], 'text' => ['ar' => '', 'en' => ''], 'primary' => ['label' => ['ar' => '', 'en' => ''], 'link' => null], 'secondary' => ['label' => ['ar' => '', 'en' => ''], 'link' => null], 'caption' => ['ar' => '', 'en' => ''], 'overlay' => 45]">
    @foreach ($section['data']['slides'] ?? [] as $j => $slide)
        @php($k = $slide['_key'] ?? $j)
        <x-admin.repeater-item model="section.data.slides" :index="$j" :item="$slide" :title="$slide['title']['ar'] ?? ''" :open="false">
            <div class="grid gap-3 sm:grid-cols-3">
                <livewire:admin.asset-field wire:model="section.data.slides.{{ $j }}.image_asset_id" kind="image" :label="__('admin.home.slide_image')" :required="true" :error="$errors->first('section.data.slides.'.$j.'.image_asset_id')" wire:key="sl-i-{{ $k }}-{{ $j }}" />
                <livewire:admin.asset-field wire:model="section.data.slides.{{ $j }}.mobile_image_asset_id" kind="image" :label="__('admin.home.slide_mobile')" wire:key="sl-m-{{ $k }}-{{ $j }}" />
                <livewire:admin.asset-field wire:model="section.data.slides.{{ $j }}.video_asset_id" kind="video" :label="__('admin.home.slide_video')" wire:key="sl-v-{{ $k }}-{{ $j }}" />
            </div>
            <x-admin.pair model="section.data.slides.{{ $j }}.title" :label="__('admin.home.slide_title')" required />
            <x-admin.pair model="section.data.slides.{{ $j }}.text" :label="__('admin.home.slide_text')" textarea />
            <x-admin.pair model="section.data.slides.{{ $j }}.caption" :label="__('admin.home.slide_caption')" />
            @foreach (['primary', 'secondary'] as $button)
                <div class="space-y-3 rounded-xl border border-a-border p-3">
                    <x-admin.pair model="section.data.slides.{{ $j }}.{{ $button }}.label" :label="__('admin.blocks.fields.'.$button.'_button')" />
                    <x-admin.link-picker model="section.data.slides.{{ $j }}.{{ $button }}.link" :options="$this->linkOptions" />
                </div>
            @endforeach
            <x-admin.input model="section.data.slides.{{ $j }}.overlay" type="number" min="30" max="60" :label="__('admin.home.overlay')" :hint="__('admin.home.overlay_hint')" />
        </x-admin.repeater-item>
    @endforeach
</x-admin.repeater>
