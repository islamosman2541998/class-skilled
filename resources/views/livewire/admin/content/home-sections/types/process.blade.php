@include('livewire.admin.content.home-sections.types._heading')
<x-admin.repeater model="section.data.steps" :label="__('admin.home.steps')" :template="['title' => ['ar' => '', 'en' => ''], 'text' => ['ar' => '', 'en' => '']]">
    @foreach ($section['data']['steps'] ?? [] as $j => $step)
        <x-admin.repeater-item model="section.data.steps" :index="$j" :item="$step" :title="$step['title']['ar'] ?? ''" numbered :open="false">
            <x-admin.pair model="section.data.steps.{{ $j }}.title" :label="__('admin.blocks.fields.title')" required />
            <x-admin.pair model="section.data.steps.{{ $j }}.text" :label="__('admin.blocks.fields.text')" textarea />
        </x-admin.repeater-item>
    @endforeach
</x-admin.repeater>
