<?php
/**
 * Fichier : routes.php
 * Rôle : Défini les routes à suivre pour les méthode liées.
 */

$router = new App\Core\Router();

// Page d'accueil
$router->add('GET',  '/',                   [App\Controllers\HomeController::class,      'index']);

// Connexion
$router->add('GET',  '/login',              [App\Controllers\AuthController::class,      'showLogin']);
$router->add('POST', '/login',              [App\Controllers\AuthController::class,      'processLogin']);

// Inscription
$router->add('GET',  '/register',           [App\Controllers\AuthController::class,      'showRegister']);
$router->add('POST', '/register',           [App\Controllers\AuthController::class,      'processRegister']);

// Mot de passe oublié
$router->add('GET',  '/forgot',             [App\Controllers\AuthController::class,      'showForgot']);
$router->add('POST', '/forgot',             [App\Controllers\AuthController::class,      'processForgot']);

// Réinitialisation du mot de passe
$router->add('GET',  '/reset',              [App\Controllers\AuthController::class,      'showReset']);
$router->add('POST', '/reset',              [App\Controllers\AuthController::class,      'processReset']);

// Déconnexion
$router->add('GET',  '/logout',             [App\Controllers\AuthController::class,      'logout']);
$router->add('POST', '/logout',             [App\Controllers\AuthController::class,      'logout']);

// Dashboard
$router->add('GET',  '/dashboard',          [App\Controllers\DashboardController::class, 'index']);
$router->add('POST', '/dashboard',          [App\Controllers\DashboardController::class, 'index']);

// Mentions légales
$router->add('GET',  '/mentions',           [App\Controllers\LegalController::class,     'index']);

// Profil utilisateur
$router->add('GET',  '/profile',            [App\Controllers\ProfileController::class,   'index']);
$router->add('POST', '/profile/updateRole', [App\Controllers\ProfileController::class,   'updateRole']);