<?php
/**
 * Fichier : HomeController.php
 * Rôle : Gère les actions liées à la page d'accueil du site EnergyDash
 */

namespace App\Controllers;

/**
 * Contrôleur HomeController
 * Gère l'affichage de la page d'accueil publique.
 *
 * @package App\Controllers
 */
class HomeController extends \App\Core\Controller {

    /**
     * Affiche la page d'accueil.
     * Route: GET /
     *
     * @return void
     */
    public function index(): void{
        $this->view->render('home/index', ['title' => 'Accueil']);
    }
}