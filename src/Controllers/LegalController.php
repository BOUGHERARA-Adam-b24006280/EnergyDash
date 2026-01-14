<?php
/**
 * Fichier : LegalController.php
 * Rôle : Gère l’affichage de la page des mentions légales.
 */

namespace App\Controllers;

use App\Core\Controller;

/**
 * Contrôleur LegalController
 * Gère l'affichage des pages légales (Mentions légales, CGU, etc.).
 *
 * @package App\Controllers
 */
class LegalController extends Controller {

    /**
     * Affiche les mentions légales.
     * Route: GET /legal
     *
     * @return void
     */
    public function index(): void {
        $this->render('legal/mentions', ['title' => 'Mentions Légales']);
    }
}