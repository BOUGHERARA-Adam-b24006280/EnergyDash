<?php

use App\Controllers\HomeController;

$router->add('GET', '/', [HomeController::class, 'index']);