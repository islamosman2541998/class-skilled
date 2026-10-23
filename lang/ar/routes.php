<?php

return [
    'services' => ['index' => 'الخدمات', 'show' => 'الخدمات/{slug}'],
    'projects' => [
        'index' => 'المشاريع',
        'category' => 'المشاريع/تصنيف/{slug}',
        'show' => 'المشاريع/{slug}',
    ],
    'posts' => [
        'index' => 'المقالات',
        'category' => 'المقالات/تصنيف/{slug}',
        'show' => 'المقالات/{slug}',
    ],
    'careers' => ['index' => 'الوظائف', 'show' => 'الوظائف/{slug}'],
    'quote' => 'طلب-عرض-سعر',
    'contact' => 'تواصل-معنا',
    'faq' => 'الأسئلة-الشائعة',
    'search' => 'بحث',
    'pages' => ['show' => '{slug}'],
];
