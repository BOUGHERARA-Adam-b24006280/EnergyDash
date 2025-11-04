<?php
/**
 * Fichier : HomeController.php
 * Rôle : Gère les actions liées à la page d'accueil du site EnergyDash
 * Auteur : Gustin MAILHE, Lucas LEPAPE
 */

namespace App\Controllers;

use App\Core\Controller;

/**
 * Classe HomeController qui gère la gestion de l'affichage de la page d'accueil
 */
class HomeController extends Controller
{
    public function index(): void
    {
        $this->render(__DIR__ . '/../Views/home/index.php', 'Accueil');
    }
}
