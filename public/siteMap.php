<?php
// sitemap.php
header('Content-Type: application/xml; charset=utf-8');

$domain = 'http://localhost:8000';

// Définir les pages
$pages = [
    [
        'url' => '/',
        'title' => 'Accueil',
        'description' => 'Page d\'accueil d\'EnergyDash',
        'priority' => '1.0',
        'changefreq' => 'daily'
    ],
    [
        'url' => '/Login',
        'title' => 'Connexion',
        'description' => 'Se connecter à votre compte',
        'priority' => '0.8',
        'changefreq' => 'monthly'
    ],
    [
        'url' => '/Register',
        'title' => 'Inscription',
        'description' => 'Créer un nouveau compte',
        'priority' => '0.8',
        'changefreq' => 'monthly'
    ],
    [
        'url' => '/Dashboard',
        'title' => 'Tableau de bord',
        'description' => 'Gérez votre consommation énergétique',
        'priority' => '0.9',
        'changefreq' => 'daily'
    ],
    [
        'url' => '/siteMap.php',
        'title' => 'Plan du site',
        'description' => 'Navigation complète du site',
        'priority' => '0.5',
        'changefreq' => 'weekly'
    ],
    [
        'url' => '/mentions-legales',
        'title' => 'Mentions légales',
        'description' => 'Informations légales et CGU',
        'priority' => '0.3',
        'changefreq' => 'yearly'
    ],
];

echo '<?xml version="1.0" encoding="UTF-8"?>';
echo '<?xml-stylesheet type="text/xsl" href="siteMap.xsl"?>';
?>

<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    <?php foreach ($pages as $page): ?>
    <url>
        <loc><?= htmlspecialchars($domain . $page['url']) ?></loc>
        <lastmod><?= date('Y-m-d') ?></lastmod>
        <changefreq><?= $page['changefreq'] ?></changefreq>
        <priority><?= $page['priority'] ?></priority>
        <!-- Données custom pour l'affichage HTML -->
        <title><?= htmlspecialchars($page['title']) ?></title>
        <description><?= htmlspecialchars($page['description']) ?></description>
    </url>
    <?php endforeach; ?>
</urlset>