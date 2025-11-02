<?php

use App\Core\Router;
use App\Controllers\HomeController;
use App\Controllers\AuthController;
use App\Controllers\DashboardController;
use App\Controllers\LegalController;
use App\Controllers\EnergyController;
use App\Controllers\ProfileController;

$router = new Router();

// Page d'accueil
$router->add('GET', '/', [HomeController::class, 'index']);
$router->add('POST', '/', [HomeController::class, 'switchTheme']);

//Authentification
$router->add('GET', '/login', [AuthController::class, 'login']);
$router->add('GET', '/register', [AuthController::class, 'register']);
$router->add('POST', '/login', [AuthController::class, 'login']);
$router->add('POST', '/register', [AuthController::class, 'register']);

// Déconnexion
$router->add('GET', '/logout', [AuthController::class, 'logout']);
$router->add('POST', '/logout', [AuthController::class, 'logout']);

// Dashboard
$router->add('GET', '/dashboard', [DashboardController::class, 'index']);
$router->add('POST', '/dashboard', [DashboardController::class, 'index']);

// Mentions légales
$router->add('GET', '/mentions-legales', [LegalController::class, 'mentions']);

// Api
$router->add('GET', '/api/energy', [EnergyController::class, 'index']);

// Profil utilisateur
$router->add('GET', '/profile', [ProfileController::class, 'index']);
$router->add('POST', '/profile/updateRole', [ProfileController::class, 'updateRole']);