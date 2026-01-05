<?php

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../src/Config/config.php';
require_once __DIR__ . '/../src/Config/routes.php';


try {
    $router->dispatch();
}
catch(Exception $e) {
    error_log("Erreur 500 : " . $e->getMessage());
    http_response_code(500);

    require __DIR__ . '/../src/Views/shared/header.php';
    require __DIR__ . '/../src/Views/error/500.php';
    require __DIR__ . '/../src/Views/shared/footer.php';
    exit;
}
