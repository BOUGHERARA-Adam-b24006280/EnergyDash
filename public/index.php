<?php

use App\Core\Router;

require('../vendor/autoload.php');

$router = new Router();

require('../src/Config/routes.php');

$method = $_SERVER['REQUEST_METHOD'];

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = rtrim($uri, '/');
$uri = preg_replace('#/+#', '/', $uri);
if ($uri === '') {
    $uri = '/';
}

try {
    $router->dispatch($uri, $method);
}catch(Exception $e) {
    http_response_code(500);
    echo "<h1>Erreur serveur</h1><p>{$e->getMessage()}</p>";
}