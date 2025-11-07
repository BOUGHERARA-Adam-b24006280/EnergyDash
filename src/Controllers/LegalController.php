<?php
/**
 * Fichier : LegalController.php
 * Rôle : Gère l’affichage de la page des mentions légales.
 * Auteur : Kenji CLOT-GODARD
 */

namespace App\Controllers;

use App\Core\Controller;

final class LegalController extends Controller
{
    /**
     * Affiche la page des mentions légales.
     */
    public function index(): void
    {
        $this->render(
            __DIR__ . '/../Views/Legal/Mentions.php',
            'Mentions Légales'
        );
    }
}