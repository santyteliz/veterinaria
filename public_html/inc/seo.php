<?php
/**
 * SEO local: JSON-LD (VeterinaryCare, FAQPage, BreadcrumbList) y Open Graph.
 * Todo sale de site.json, así los horarios y datos del panel se reflejan solos.
 */

declare(strict_types=1);

const SCHEMA_DAYS = [1 => 'Monday', 2 => 'Tuesday', 3 => 'Wednesday', 4 => 'Thursday', 5 => 'Friday', 6 => 'Saturday', 7 => 'Sunday'];

function site_url(string $path = '/'): string
{
    return rtrim((string) c('site.url'), '/') . '/' . ltrim($path, '/');
}

/** Sin marcadores "[A CONFIRMAR…]": nunca se publican en datos estructurados. */
function schema_text($value): string
{
    $s = trim((string) preg_replace('/\s*\[A CONFIRMAR[^\]]*\]\s*/u', ' ', (string) $value));
    return trim((string) preg_replace('/\s+/u', ' ', $s));
}

/** Horarios de la semana agrupados por tramo: un bloque por cada horario distinto. */
function schema_opening_hours(): array
{
    $byRange = [];
    foreach ((array) c('hours.week', []) as $day => $ranges) {
        foreach ((array) $ranges as [$from, $to]) {
            $byRange[$from . '-' . $to]['opens'] = $from;
            $byRange[$from . '-' . $to]['closes'] = $to;
            $byRange[$from . '-' . $to]['days'][] = SCHEMA_DAYS[(int) $day];
        }
    }
    ksort($byRange);
    return array_values(array_map(fn ($r) => [
        '@type' => 'OpeningHoursSpecification',
        'dayOfWeek' => $r['days'],
        'opens' => $r['opens'],
        'closes' => $r['closes'],
    ], $byRange));
}

/** Feriados y días especiales futuros. */
function schema_special_hours(): array
{
    $out = [];
    $today = now_local()->format('Y-m-d');
    foreach ((array) c('hours.special', []) as $s) {
        $date = (string) ($s['date'] ?? '');
        if ($date < $today) {
            continue;
        }
        $ranges = (array) ($s['ranges'] ?? []);
        if (!$ranges) {
            // Cerrado todo el día: Google lo entiende como opens = closes = 00:00.
            $ranges = [['00:00', '00:00']];
        }
        foreach ($ranges as [$from, $to]) {
            $out[] = ['@type' => 'OpeningHoursSpecification', 'validFrom' => $date, 'validThrough' => $date, 'opens' => $from, 'closes' => $to];
        }
    }
    return $out;
}

function schema_business(): array
{
    $contact = c('contact', []);
    $schema = [
        '@context' => 'https://schema.org',
        '@type' => 'VeterinaryCare',
        '@id' => site_url('/#clinica'),
        'name' => c('site.name'),
        'url' => site_url('/'),
        'logo' => site_url('/assets/img/logo-cruz-512.png'),
        'image' => site_url('/assets/img/og-la-guardia.jpg'),
        'description' => schema_text(c('seo.home.description')),
        'telephone' => $contact['phone'] ?? '',
        'email' => $contact['email'] ?? '',
        'address' => [
            '@type' => 'PostalAddress',
            'streetAddress' => schema_text($contact['address'] ?? ''),
            'addressLocality' => $contact['city'] ?? '',
            'addressRegion' => $contact['region'] ?? '',
            'postalCode' => $contact['postal_code'] ?? '',
            'addressCountry' => 'AR',
        ],
        'openingHoursSpecification' => schema_opening_hours(),
        'sameAs' => array_values(array_filter([$contact['instagram'] ?? '', $contact['facebook'] ?? ''])),
        'hasMap' => maps_directions_url(),
        'areaServed' => ['@type' => 'City', 'name' => ($contact['city'] ?? '') . ', ' . ($contact['region'] ?? '')],
        'knowsAbout' => array_values(array_map(fn ($s) => $s['name'], array_filter((array) c('services', []), fn ($s) => !empty($s['visible'])))),
    ];

    $lat = (string) ($contact['geo']['lat'] ?? '');
    $lng = (string) ($contact['geo']['lng'] ?? '');
    if (is_numeric($lat) && is_numeric($lng)) {
        $schema['geo'] = ['@type' => 'GeoCoordinates', 'latitude' => (float) $lat, 'longitude' => (float) $lng];
    }
    if (($price = (string) c('seo.price_range')) !== '' && !is_placeholder($price)) {
        $schema['priceRange'] = $price;
    }
    if ($special = schema_special_hours()) {
        $schema['specialOpeningHoursSpecification'] = $special;
    }
    $reviews = c('reviews', []);
    if (!empty($reviews['verified']) && !empty($reviews['rating']) && !empty($reviews['count'])) {
        $schema['aggregateRating'] = ['@type' => 'AggregateRating', 'ratingValue' => (float) $reviews['rating'], 'reviewCount' => (int) $reviews['count'], 'bestRating' => 5];
    }
    return array_filter($schema, fn ($v) => $v !== '' && $v !== []);
}

function schema_faq(array $faq): ?array
{
    $items = [];
    foreach ($faq as $item) {
        $q = schema_text($item['q'] ?? '');
        $a = schema_text($item['a'] ?? '');
        if ($q !== '' && $a !== '') {
            $items[] = ['@type' => 'Question', 'name' => $q, 'acceptedAnswer' => ['@type' => 'Answer', 'text' => $a]];
        }
    }
    return $items ? ['@context' => 'https://schema.org', '@type' => 'FAQPage', 'mainEntity' => $items] : null;
}

/** $crumbs = [['Inicio', '/'], ['Peluquería canina', '/peluqueria-canina-santo-tome']] */
function schema_breadcrumb(array $crumbs): array
{
    return [
        '@context' => 'https://schema.org',
        '@type' => 'BreadcrumbList',
        'itemListElement' => array_map(fn ($c, $i) => [
            '@type' => 'ListItem',
            'position' => $i + 1,
            'name' => $c[0],
            'item' => site_url($c[1]),
        ], $crumbs, array_keys($crumbs)),
    ];
}

function json_ld(array $schema): string
{
    return '<script type="application/ld+json">'
        . json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP)
        . '</script>';
}

/** Metadatos de la página: canonical, Open Graph y Twitter Card. */
function meta_tags(array $page): string
{
    $url = site_url($page['path'] ?? '/');
    $image = site_url($page['og_image'] ?? '/assets/img/og-la-guardia.jpg');
    $title = schema_text($page['title']);
    $desc = schema_text($page['description']);
    $robots = (string) ($page['robots'] ?? 'index, follow, max-image-preview:large');
    $tags = [
        '<meta name="description" content="' . e($desc) . '">',
        str_contains($robots, 'noindex') ? '' : '<link rel="canonical" href="' . e($url) . '">',
        '<meta name="robots" content="' . e($robots) . '">',
        '<meta property="og:type" content="website">',
        '<meta property="og:locale" content="es_AR">',
        '<meta property="og:site_name" content="' . e(c('site.name')) . '">',
        '<meta property="og:title" content="' . e($title) . '">',
        '<meta property="og:description" content="' . e($desc) . '">',
        '<meta property="og:url" content="' . e($url) . '">',
        '<meta property="og:image" content="' . e($image) . '">',
        '<meta property="og:image:width" content="1200">',
        '<meta property="og:image:height" content="630">',
        '<meta property="og:image:alt" content="' . e(c('site.name') . ', ' . c('contact.city')) . '">',
        '<meta name="twitter:card" content="summary_large_image">',
        '<meta name="twitter:title" content="' . e($title) . '">',
        '<meta name="twitter:description" content="' . e($desc) . '">',
        '<meta name="twitter:image" content="' . e($image) . '">',
    ];
    foreach ((array) ($page['schema'] ?? []) as $schema) {
        if ($schema) {
            $tags[] = json_ld($schema);
        }
    }
    return implode("\n", $tags);
}
