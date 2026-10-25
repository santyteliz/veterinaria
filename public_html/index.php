<?php
declare(strict_types=1);

require __DIR__ . '/inc/bootstrap.php';

$page = [
    'title' => c('seo.home.title'),
    'description' => c('seo.home.description'),
    'path' => '/',
    'schema' => [schema_business(), schema_faq((array) c('faq', []))],
    'motion' => true,
];

require __DIR__ . '/inc/layout-start.php';

foreach ([
    'hero',
    'marquee',
    'services',
    'steps',
    'grooming',
    'petshop',
    'patients',
    'team',
    'reviews',
    'emergency',
    'location',
    'faq',
] as $section) {
    require __DIR__ . '/inc/sections/' . $section . '.php';
}

require __DIR__ . '/inc/layout-end.php';
