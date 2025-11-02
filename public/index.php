<?php
use App\Core\Router;

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../src/Config/config.php';

$router = new Router();
require __DIR__ . '/../src/Config/routes.php';

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$request_uri = $_SERVER['REQUEST_URI'] ?? '/';

if (!is_string($method) || !is_string($request_uri)) {
    http_response_code(400);
    echo "Bad Request";
    exit;
}

$uri = parse_url($request_uri, PHP_URL_PATH);
if ($uri === false || $uri === null) {
    $uri = '/';
}
$uri = rtrim($uri, '/');
$uri = preg_replace('#/+#', '/', $uri);
if ($uri === '' || $uri === null) {
    $uri = '/';
}

try {
    $router->dispatch($method, $uri);
}catch(Exception $e) {
    error_log("Erreur 500 : " . $e->getMessage());
    http_response_code(500);
    require __DIR__ . '/../src/Views/error/500.php';
    exit;
}
