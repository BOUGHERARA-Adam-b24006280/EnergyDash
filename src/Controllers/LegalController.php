<?php
/**
 * Fichier : LegalController.php
 * Rôle : Gère l’affichage de la page des mentions légales.
 * Auteur : Kenji CLOT-GODARD
 */

namespace App\Controllers;

use App\Core\Controller;

class LegalController extends Controller {

    public function index(): void {
        $this->render('legal/mentions', ['title' => 'Mentions Légales']);
    }
}