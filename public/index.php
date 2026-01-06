<?php

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../src/Config/config.php';
require_once __DIR__ . '/../src/Config/routes.php';

// Transforme les erreurs en Exception
set_error_handler(function($severity, $message, $file, $line) {
    throw new \ErrorException($message, 0, $severity, $file, $line);
});

try {
    $router->dispatch();
}
catch(Throwable $e) {
    $errorController = new App\Controllers\ErrorController();
    $errorController->error500page();
}
