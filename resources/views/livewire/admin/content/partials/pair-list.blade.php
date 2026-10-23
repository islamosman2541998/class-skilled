{{-- Repeater of {ar, en} text rows. Vars: $path (e.g. form.scope), $label, $numbered --}}
<x-admin.repeater :model="$path" :label="$label" :template="['ar' => '', 'en' => '']">
    @foreach (data_get($this, $path) ?? [] as $j => $row)
        <x-admin.repeater-item :model="$path" :index="$j" :item="$row" :title="$row['ar'] ?? ''" :numbered="$numbered ?? false" :open="blank($row['ar'] ?? '')">
            <x-admin.pair :model="$path.'.'.$j" />
        </x-admin.repeater-item>
    @endforeach
</x-admin.repeater>
