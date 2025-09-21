<?php
// Démarrage de la session
session_start();

// Chargement des configurations
require_once './config/database.php';
$routes = require_once './config/routes.php';

// Récupération de l'URL et de la méthode HTTP
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$path = trim($path, '/');
$method = $_SERVER['REQUEST_METHOD'];

// Routage
foreach ($routes as $route => $controllerAction) {
    if ($path === $route) {
        [$controllerName, $action] = $controllerAction;

        // Chargement du contrôleur
        require_once './controllers/' . $controllerName . '.php';
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
require_once './views/errors/404.php';
?>
