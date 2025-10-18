<?php
$routes = [
    // Page d’accueil
    '' => [
        'controller' => 'HomeController', 
        'method' => 'index'
    ],

    // Authentification
    'login' => [
        'controller' => 'AuthController', 
        'method' => 'login'
    ],
    'register' => [
        'controller' => 'AuthController', 
        'method' => 'register'
    ],
    'logout' => [
        'controller' => 'AuthController', 
        'method' => 'logout'
    ],

    // Tableau de bord (espace membre)
    'dashboard' => [
        'controller' => 'DashboardController', 
        'method' => 'index'
    ],

    // Autres pages
    'profile' => [
        'controller' => 'DashboardController', 
        'method' => 'profile'
    ],

    // Exemple d’erreur 404 personnalisée
    '404' => [
        'controller' => 'ErrorController', 
        'method' => 'notFound'
    ],
];