{{-- Shared editor for list sections (services, projects, posts, testimonials, clients, certificates). --}}
@include('livewire.admin.content.home-sections.types._heading', ['withLink' => true])
<x-admin.segmented model="section.source" live :label="__('admin.home.source')" :options="\App\Enums\DataSource::options()" />
@if (($section['source'] ?? '') === 'manual')
    <x-admin.select model="section.data.item_ids" multiple :label="__('admin.home.manual_items')" :hint="__('admin.home.manual_hint')" :options="$this->itemOptions($section['type'])" wire:key="items-{{ $editing }}" />
@endif
<div class="grid gap-4 sm:grid-cols-2">
    <x-admin.input model="section.items_limit" type="number" min="1" max="24" :label="__('admin.home.limit')" />
    <x-admin.segmented model="section.data.display" :label="__('admin.home.display')" :options="['slider' => __('admin.home.display_slider'), 'grid' => __('admin.home.display_grid')]" />
</div>
<x-admin.toggle model="section.data.autoplay" :label="__('admin.home.slider_autoplay')" />
@if ($section['type'] === 'projects')
    <x-admin.toggle model="section.data.show_filters" :label="__('admin.home.show_filters')" />
@endif
