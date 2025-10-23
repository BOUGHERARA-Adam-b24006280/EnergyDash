<?php

use App\Core\Router;
use App\Controllers\HomeController;
use App\Controllers\AuthController;
use App\Controllers\LegalController;

$router = new Router();

$router->add('GET', '/', [HomeController::class, 'index']);
$router->add('POST', '/', [HomeController::class, 'switchTheme']);
$router->add('GET', '/login', [AuthController::class, 'login']);
$router->add('GET', '/register', [AuthController::class, 'register']);
$router->add('GET', '/mentions-legales', [LegalController::class, 'mentions']);