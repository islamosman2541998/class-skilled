<?php

return [
    'services' => ['index' => 'services', 'show' => 'services/{slug}'],
    'projects' => [
        'index' => 'projects',
        'category' => 'projects/category/{slug}',
        'show' => 'projects/{slug}',
    ],
    'posts' => [
        'index' => 'articles',
        'category' => 'articles/category/{slug}',
        'show' => 'articles/{slug}',
    ],
    'careers' => ['index' => 'careers', 'show' => 'careers/{slug}'],
    'quote' => 'request-a-quote',
    'contact' => 'contact',
    'faq' => 'faq',
    'search' => 'search',
    'pages' => ['show' => '{slug}'],
];
