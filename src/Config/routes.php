<?php

use App\Controllers\HomeController;
use App\Controllers\AuthController;

$router->add('GET', '/', [HomeController::class, 'index']);
$router->add('GET', '/login', [AuthController::class, 'login']);