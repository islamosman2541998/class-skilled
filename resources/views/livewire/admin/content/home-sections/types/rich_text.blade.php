<x-admin.pair model="section.title" :label="__('admin.home.title')" />
<x-admin.locale-tabs :missing="$this->missingEnglish([$section['data']['html'] ?? []])">
    <x-slot:ar><x-admin.rich-text model="section.data.html.ar" dir="rtl" required /></x-slot:ar>
    <x-slot:en><x-admin.rich-text model="section.data.html.en" dir="ltr" /></x-slot:en>
</x-admin.locale-tabs>
