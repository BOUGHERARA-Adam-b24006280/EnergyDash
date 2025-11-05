<?php
/**
 * Fichier : LegalController.php
 * Rôle : Gère l’affichage de la page des mentions légales.
 * Auteur :  Kenji CLOT-GODARD
 */

namespace App\Controllers;

use App\Core\Layout;

/**
 * Classe LegalController qui gère l'affichage des mentions légales
 */
final class LegalController
{
    /**
     * Affiche la pge des mentions légales en utilisant la classe Layout pour afficher la page complète
     * 
     * @return void
     */
    public function legal(): void
    {
        $layout = new Layout(__DIR__ . '/../Views/Legal/Mentions.php', 'Mentions Légales');
        $layout->render();
    }
}
