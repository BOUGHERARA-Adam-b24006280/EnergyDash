<?php
use App\Core\Router;

require('../vendor/autoload.php');

$router = new Router();

require('../src/Config/routes.php');

$method = $_SERVER['REQUEST_METHOD'];

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = rtrim($uri, '/');
if ($uri === '') {
    $uri = '/';
}

$router->dispatch($uri, $method);