<?php
require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Router;

// Charge la table des routes
$routes = require __DIR__ . '/../src/Config/routes.php';

// Démarre le routeur
$router = new Router($routes);
$router->dispatch($_GET['url'] ?? '');