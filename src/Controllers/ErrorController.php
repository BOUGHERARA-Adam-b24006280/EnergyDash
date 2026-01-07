<?php

namespace App\Controllers;

use App\Core\Controller;

/**
 * Contrôleur ErrorController
 * Gère l'affichage des pages d'erreur (404, 500).
 *
 * @package App\Controllers
 */
class ErrorController extends Controller {
    
    /**
     * Affiche la page d'erreur 404 (Non trouvé).
     *
     * @return void
     */
    public function error404page(): void {
        $title = "Page non trouvée";

        http_response_code(404);

        require __DIR__ . '/../Views/shared/header.php';
        require __DIR__ . '/../Views/error/404.php';
        require __DIR__ . '/../Views/shared/footer.php';
    }

    /**
     * Affiche la page d'erreur 500 (Erreur serveur).
     * Arrête l'exécution du script après affichage.
     *
     * @return void
     */
    public function error500page(): void {
        $title = 'Erreur interne du serveur';

        http_response_code(500);

        require __DIR__ . '/../Views/shared/header.php';
        require __DIR__ . '/../Views/error/500.php';
        require __DIR__ . '/../Views/shared/footer.php';
        exit;
    }
}