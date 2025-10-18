<?php

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/routes.php';

$request = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');

if (array_key_exists($request, $routes)) {
    $controllerName = $routes[$request]['controller'];
    $methodName = $routes[$request]['method'];

    $controllerPath = __DIR__ . "/controllers/$controllerName.php";

    if (file_exists($controllerPath)) {
        require_once $controllerPath;

        $controller = new $controllerName();

        if (method_exists($controller, $methodName)) {
            $controller->$methodName();
        } else {
            echo "La méthode <b>$methodName</b> n'existe pas dans $controllerName.";
        }
    } else {
        echo "Le contrôleur <b>$controllerName</b> est introuvable.";
    }
} else {
    http_response_code(404);
    require_once __DIR__ . '/controllers/ErrorController.php';
    $controller = new ErrorController();
    $controller->notFound();
}