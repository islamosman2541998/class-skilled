<x-admin.locale-tabs :missing="$this->missingEnglish([$block['data']['html'] ?? []])">
    <x-slot:ar><x-admin.rich-text :model="$p.'.html.ar'" dir="rtl" /></x-slot:ar>
    <x-slot:en><x-admin.rich-text :model="$p.'.html.en'" dir="ltr" /></x-slot:en>
</x-admin.locale-tabs>
