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
        http_response_code(404);
        $this->render('error/404', ['title' => 'Page non trouvée']);
    }

    /**
     * Affiche la page d'erreur 500 (Erreur serveur).
     * Arrête l'exécution du script après affichage.
     *
     * @return void
     */
    public function error500page(): void {
        http_response_code(500);
        $this->render('error/500', ['title' => 'Erreur interne du serveur']);
        exit;
    }
}