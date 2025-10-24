<?php
/**
 * Fichier : HomeController.php
 * Rôle : Gère les actions liées à la page d'accueil du site EnergyDash
 * Auteur : Gustin MAILHE, Lucas LEPAPE
 */

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Layout;

/**
 * Classe HomeController qui gère la gestion de l'affichage de la page d'accueil
 */
class HomeController extends Controller {

    /**
     * Affiche la page d'accueil en utilisant la classe Layout pour assembler les différentes parties
     * 
     * @return void
     */
    public function index(): void {
        $layout = new Layout(__DIR__ . '/../Views/home/index.php', 'Accueil');
        $layout->render();
    }
}