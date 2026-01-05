<?php

$router = new App\Core\Router();

// Page d'accueil
$router->add('GET',  '/',                   [App\Controllers\HomeController::class,      'index']);
$router->add('POST', '/',                   [App\Controllers\HomeController::class,      'switchTheme']);

//Authentification
$router->add('GET',  '/login',              [App\Controllers\AuthController::class,      'login']);
$router->add('GET',  '/register',           [App\Controllers\AuthController::class,      'register']);
$router->add('POST', '/login',              [App\Controllers\AuthController::class,      'login']);
$router->add('POST', '/register',           [App\Controllers\AuthController::class,      'register']);
$router->add('GET',  '/forgot',             [App\Controllers\AuthController::class,      'forgot']);

// Déconnexion
$router->add('GET',  '/logout',             [App\Controllers\AuthController::class,      'logout']);
$router->add('POST', '/logout',             [App\Controllers\AuthController::class,      'logout']);

// Dashboard
$router->add('GET',  '/dashboard',          [App\Controllers\DashboardController::class, 'index']);
$router->add('POST', '/dashboard',          [App\Controllers\DashboardController::class, 'index']);

// Mentions légales
$router->add('GET',  '/mentions',           [App\Controllers\LegalController::class,     'index']);

// Api
$router->add('GET',  '/api/energy',         [App\Controllers\EnergyController::class,    'index']);

// Profil utilisateur
$router->add('GET',  '/profile',            [App\Controllers\ProfileController::class,   'index']);
$router->add('POST', '/profile/updateRole', [App\Controllers\ProfileController::class,   'updateRole']);

// Mot de passe oublié
$router->add('GET',  '/forgot',             [App\Controllers\AuthController::class,      'forgot']);            // Affiche la page
$router->add('POST', '/forgot',             [App\Controllers\AuthController::class,      'forgotPassword']);   // Traite le formulaire
$router->add('GET',  '/reset',              [App\Controllers\AuthController::class,      'resetPassword']);
$router->add('POST', '/reset',              [App\Controllers\AuthController::class,      'resetPassword']);