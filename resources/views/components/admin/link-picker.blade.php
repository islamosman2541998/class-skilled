{{--
    Link picker for the link object `{type, route?, model_type?, model_id?, url?, anchor?, target_blank?}`
    (see App\Support\LinkResolver). Empty type = no link (null).
    <x-admin.link-picker model="form.cta_link" :label="…" :options="$this->linkOptions" />
    Pass :options from App\Support\LinkOptions::all() (computed once per component).
--}}
@props(['model', 'label' => null, 'options' => null, 'hint' => null, 'types' => null])
@php
    $options ??= \App\Support\LinkOptions::all();
    $types ??= ['system_page', 'page', 'model', 'external', 'anchor', 'whatsapp', 'phone'];
@endphp
<x-admin.field :label="$label" :error="$model" :hint="$hint" {{ $attributes->only('class') }}>
    <div x-data="linkPicker(@js($options))" x-modelable="link" wire:model="{{ $model }}" class="space-y-3 rounded-xl border border-a-border bg-[#fafbfc] p-3">
        <div class="grid gap-3 sm:grid-cols-2">
            <select class="a-input" :value="link?.type ?? ''" @change="setType($event.target.value)" aria-label="{{ __('admin.link.type') }}">
                <option value="">{{ __('admin.link.none') }}</option>
                @foreach ($types as $type)
                    <option value="{{ $type }}">{{ __('admin.link.'.$type) }}</option>
                @endforeach
            </select>

            <template x-if="link?.type === 'system_page'">
                <select class="a-input" :value="link.route ?? ''" @change="set('route', $event.target.value)" aria-label="{{ __('admin.link.route') }}">
                    <option value="">{{ __('admin.common.choose') }}</option>
                    <template x-for="(text, key) in options.routes" :key="key">
                        <option :value="key" x-text="text" :selected="link.route === key"></option>
                    </template>
                </select>
            </template>

            <template x-if="link?.type === 'page'">
                <select class="a-input" :value="String(link.model_id ?? '')" @change="setModel('page', $event.target.value)" aria-label="{{ __('admin.link.page') }}">
                    <option value="">{{ __('admin.common.choose') }}</option>
                    <template x-for="(text, id) in options.models.page.items" :key="id">
                        <option :value="id" x-text="text" :selected="String(link.model_id) === String(id)"></option>
                    </template>
                </select>
            </template>

            <template x-if="link?.type === 'external'">
                <input type="url" dir="ltr" class="a-input" placeholder="https://" :value="link.url ?? ''" @change="set('url', $event.target.value)" aria-label="{{ __('admin.link.url') }}">
            </template>

            <template x-if="link?.type === 'anchor'">
                <input type="text" dir="ltr" class="a-input" placeholder="contact" :value="link.anchor ?? ''" @change="set('anchor', $event.target.value)" aria-label="{{ __('admin.link.anchor_id') }}">
            </template>
        </div>

        <template x-if="link?.type === 'model'">
            <div class="grid gap-3 sm:grid-cols-2">
                <select class="a-input" :value="link.model_type ?? ''" @change="setModel($event.target.value, '')" aria-label="{{ __('admin.link.type') }}">
                    <option value="">{{ __('admin.common.choose') }}</option>
                    <template x-for="(group, type) in modelGroups()" :key="type">
                        <option :value="type" x-text="group.label" :selected="link.model_type === type"></option>
                    </template>
                </select>
                <select class="a-input" :value="String(link.model_id ?? '')" @change="setModel(link.model_type, $event.target.value)" x-show="link.model_type" aria-label="{{ __('admin.link.item') }}">
                    <option value="">{{ __('admin.common.choose') }}</option>
                    <template x-for="(text, id) in (options.models[link.model_type]?.items ?? {})" :key="id">
                        <option :value="id" x-text="text" :selected="String(link.model_id) === String(id)"></option>
                    </template>
                </select>
            </div>
        </template>

        <label class="flex items-center gap-2 text-[13.5px]" x-show="link && ['system_page', 'page', 'model', 'external'].includes(link.type)">
            <input type="checkbox" class="a-check" :checked="!! link?.target_blank" @change="set('target_blank', $event.target.checked)">
            {{ __('admin.link.new_tab') }}
        </label>
    </div>
</x-admin.field>
