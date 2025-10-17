<?php
return [
    // Page d’accueil
    '' => ['HomeController', 'index'],

    // Authentification
    'login' => ['AuthController', 'login'],
    'register' => ['AuthController', 'register'],
    'logout' => ['AuthController', 'logout'],

    // Tableau de bord (espace membre)
    'dashboard' => ['DashboardController', 'index'],

    // Autres pages
    'profile' => ['DashboardController', 'profile'],

    // Exemple d’erreur 404 personnalisée
    '404' => ['ErrorController', 'notFound'],
];