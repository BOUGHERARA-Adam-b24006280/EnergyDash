<?php
// Démarrage de la session
session_start();


// BASE_URL : préfixe d'URL du projet

define('BASE_URL', dirname($_SERVER['SCRIPT_NAME']) !== '/' ? rtrim(dirname($_SERVER['SCRIPT_NAME']), '/') : '');

// Chargement des configurations
require_once __DIR__ . '/config/database.php';
$routes = require_once __DIR__ . '/config/routes.php';

// Récupération du chemin demandé
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Enlever le préfixe BASE_URL si présent (ex: "/EnergyDash")
if (BASE_URL !== '' && strpos($path, BASE_URL) === 0) {
    $path = substr($path, strlen(BASE_URL));
}

// Normaliser (retirer les / de début et fin)
$path = trim($path, '/');
$method = $_SERVER['REQUEST_METHOD'];

// Routage
foreach ($routes as $route => $controllerAction) {
    if ($path === $route) {
        [$controllerName, $action] = $controllerAction;

        // Chargement du contrôleur
        require_once __DIR__ . '/controllers/' . $controllerName . '.php';
        $controller = new $controllerName($pdo);

        // Appel de la méthode
        if (method_exists($controller, $action)) {
            $controller->$action();
            exit;
        }
    }
}

// Route non trouvée
http_response_code(404);
require_once __DIR__ . '/views/errors/404.php';
