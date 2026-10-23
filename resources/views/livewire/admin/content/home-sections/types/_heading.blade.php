{{-- Section title + subtitle (+ optional link label/link). Var: $withLink --}}
<x-admin.pair model="section.title" :label="__('admin.home.title')" />
<x-admin.pair model="section.subtitle" :label="__('admin.home.subtitle_field')" textarea />
@if ($withLink ?? false)
    <div class="space-y-3 rounded-xl border border-a-border p-3">
        <x-admin.pair model="section.cta_label" :label="__('admin.home.link_label')" />
        <x-admin.link-picker model="section.cta_link" :options="$this->linkOptions" />
    </div>
@endif
