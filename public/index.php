<?php

use App\Core\Router;

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../src/Config/config.php';

$router = new Router();
require __DIR__ . '/../src/Config/routes.php';

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
    error_log("Erreur 500 : " . $e->getMessage());
    http_response_code(500);
    require __DIR__ ; '/../src/Views/error/500.php';
    exit;
}