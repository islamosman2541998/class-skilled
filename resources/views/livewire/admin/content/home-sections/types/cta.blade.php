@include('livewire.admin.content.home-sections.types._heading')
@foreach (['primary', 'secondary'] as $button)
    <div class="space-y-3 rounded-xl border border-a-border p-3">
        <x-admin.pair model="section.data.{{ $button }}.label" :label="__('admin.blocks.fields.'.$button.'_button')" />
        <x-admin.link-picker model="section.data.{{ $button }}.link" :options="$this->linkOptions" />
    </div>
@endforeach
