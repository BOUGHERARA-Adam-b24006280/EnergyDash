<?php
header('Content-Type: text/xml; charset=utf-8');

$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
$base_url = $protocol . "://" . $_SERVER['HTTP_HOST'];

$pages_fixes = [
    '/',
    '/login',
    '/register',
    '/dashboard',
    '/mentions'
];

echo '<?xml version="1.0" encoding="UTF-8"?>';

echo '<?xml-stylesheet type="text/xsl" href="/siteMap.xsl"?>';
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

// 2. GÉNÉRATION AUTOMATIQUE DES PAGES FIXES
foreach ($pages_fixes as $url) {
    echo '<url>';
    echo '<loc>' . $base_url . $url . '</loc>';
    echo '<changefreq>weekly</changefreq>';
    echo '<priority>0.8</priority>';
    echo '</url>';
}

echo '</urlset>';
?>