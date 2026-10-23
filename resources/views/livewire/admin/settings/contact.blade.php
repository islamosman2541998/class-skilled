<x-admin.settings-frame :group="$this->group()" :tabs="$this->tabs()">
    <div class="grid gap-6 xl:grid-cols-2">
        <x-admin.panel :title="__('admin.settings.sections.phones')">
            <x-admin.repeater model="data.phones" :template="['value' => '']" :add-label="__('admin.settings.fields.add_phone')">
                @foreach ($data['phones'] as $i => $item)
                    <x-admin.repeater-item model="data.phones" :index="$i" :item="$item" :title="$item['value'] ?? ''">
                        <x-admin.input model="data.phones.{{ $i }}.value" type="tel" :label="__('admin.settings.fields.phone')" placeholder="+971 50 000 0000" />
                    </x-admin.repeater-item>
                @endforeach
            </x-admin.repeater>

            <div class="mt-6 space-y-4 border-t border-a-border pt-6">
                <x-admin.input model="data.whatsapp_number" type="tel" :label="__('admin.settings.fields.whatsapp_number')" placeholder="+971 52 000 0000" />
                <x-admin.locale-tabs :missing="$this->missingEnglish([$data['whatsapp_message']])">
                    <x-slot:ar>
                        <x-admin.textarea model="data.whatsapp_message.ar" :label="__('admin.settings.fields.whatsapp_message')" rows="2" live />
                        @if ($url = $this->whatsappPreview('ar'))<a href="{{ $url }}" target="_blank" rel="noopener" class="a-link break-all text-[12.5px]" dir="ltr">{{ $url }}</a>@endif
                    </x-slot:ar>
                    <x-slot:en>
                        <x-admin.textarea model="data.whatsapp_message.en" :label="__('admin.settings.fields.whatsapp_message')" rows="2" live />
                        @if ($url = $this->whatsappPreview('en'))<a href="{{ $url }}" target="_blank" rel="noopener" class="a-link break-all text-[12.5px]" dir="ltr">{{ $url }}</a>@endif
                    </x-slot:en>
                </x-admin.locale-tabs>
            </div>
        </x-admin.panel>

        <x-admin.panel :title="__('admin.settings.sections.emails_address')">
            <x-admin.repeater model="data.emails" :template="['value' => '']" :add-label="__('admin.settings.fields.add_email')">
                @foreach ($data['emails'] as $i => $item)
                    <x-admin.repeater-item model="data.emails" :index="$i" :item="$item" :title="$item['value'] ?? ''">
                        <x-admin.input model="data.emails.{{ $i }}.value" type="email" :label="__('admin.settings.fields.email')" />
                    </x-admin.repeater-item>
                @endforeach
            </x-admin.repeater>

            <div class="mt-6 border-t border-a-border pt-6">
                <x-admin.locale-tabs :missing="$this->missingEnglish([$data['address']])">
                    <x-slot:ar><x-admin.textarea model="data.address.ar" :label="__('admin.settings.fields.address')" rows="2" /></x-slot:ar>
                    <x-slot:en><x-admin.textarea model="data.address.en" :label="__('admin.settings.fields.address')" rows="2" /></x-slot:en>
                </x-admin.locale-tabs>
            </div>
        </x-admin.panel>

        <x-admin.panel :title="__('admin.settings.sections.map')">
            <div class="grid gap-4 sm:grid-cols-2">
                <x-admin.input model="data.map_lat" :label="__('admin.settings.fields.map_lat')" dir="ltr" />
                <x-admin.input model="data.map_lng" :label="__('admin.settings.fields.map_lng')" dir="ltr" />
            </div>
            <x-admin.textarea model="data.map_embed_url" :label="__('admin.settings.fields.map_embed_url')" :hint="__('admin.settings.hints.map_embed_url')" rows="2" dir="ltr" class="mt-4" live />
            @if (filled($data['map_embed_url'] ?? null) && str_starts_with($data['map_embed_url'], 'https://www.google.com/maps'))
                <iframe src="{{ $data['map_embed_url'] }}" class="mt-4 h-56 w-full rounded-xl border border-a-border" loading="lazy" referrerpolicy="no-referrer-when-downgrade" title="{{ __('admin.settings.fields.map_embed_url') }}"></iframe>
            @endif
        </x-admin.panel>

        <x-admin.panel :title="__('admin.settings.sections.working_hours')">
            <x-admin.repeater model="data.working_hours" :template="['days' => ['ar' => '', 'en' => ''], 'hours' => ['ar' => '', 'en' => '']]" :add-label="__('admin.settings.fields.add_hours')">
                @foreach ($data['working_hours'] as $i => $item)
                    <x-admin.repeater-item model="data.working_hours" :index="$i" :item="$item" :title="($item['days']['ar'] ?? '').' — '.($item['hours']['ar'] ?? '')">
                        <div class="grid gap-4 sm:grid-cols-2">
                            <x-admin.input model="data.working_hours.{{ $i }}.days.ar" :label="__('admin.settings.fields.days').' (ع)'" required />
                            <x-admin.input model="data.working_hours.{{ $i }}.days.en" :label="__('admin.settings.fields.days').' (EN)'" dir="ltr" />
                            <x-admin.input model="data.working_hours.{{ $i }}.hours.ar" :label="__('admin.settings.fields.hours').' (ع)'" required />
                            <x-admin.input model="data.working_hours.{{ $i }}.hours.en" :label="__('admin.settings.fields.hours').' (EN)'" dir="ltr" />
                        </div>
                    </x-admin.repeater-item>
                @endforeach
            </x-admin.repeater>
        </x-admin.panel>
    </div>
</x-admin.settings-frame>
