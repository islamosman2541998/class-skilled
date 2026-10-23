{{--
    Slug input showing the localized URL prefix (e.g. http://site/en/services/).
    <x-admin.slug model="form.slug.en" :prefix="$this->slugPrefix('en')" :label="…" />
    Arabic slugs keep Arabic letters; spaces become dashes on save.
--}}
@props(['model', 'prefix' => '', 'label' => null, 'hint' => null, 'required' => false])
<x-admin.input :model="$model" :label="$label ?? __('validation.attributes.slug')" :hint="$hint" :required="$required" :prefix="$prefix" dir="auto" {{ $attributes }} />
