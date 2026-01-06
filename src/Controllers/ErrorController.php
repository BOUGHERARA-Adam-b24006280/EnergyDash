<?php

namespace App\Controllers;

class ErrorController {
    public function error404page(): void {
        $title = "Page non trouvée";

        http_response_code(404);

        require __DIR__ . '/../Views/shared/header.php';
        require __DIR__ . '/../Views/error/404.php';
        require __DIR__ . '/../Views/shared/footer.php';
    }

    public function error500page(): void {
        $title = 'Erreur interne du serveur';

        http_response_code(500);

        require __DIR__ . '/../Views/shared/header.php';
        require __DIR__ . '/../Views/error/500.php';
        require __DIR__ . '/../Views/shared/footer.php';
        exit;
    }
}