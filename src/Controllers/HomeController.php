<?php
/**
 * Fichier : HomeController.php
 * Rôle : Gère les actions liées à la page d'accueil du site EnergyDash
 */

namespace App\Controllers;

use App\Core\Controller;

/**
 * Contrôleur HomeController
 * Gère l'affichage de la page d'accueil publique.
 *
 * @package App\Controllers
 */
class HomeController extends Controller {

    /**
     * Affiche la page d'accueil.
     * Route: GET /
     *
     * @return void
     */
    public function index(): void{
        $this->render('home/index', ['title' => 'Accueil']);
    }
}