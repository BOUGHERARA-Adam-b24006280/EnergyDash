<?php
/**
 * Fichier : HomeController.php
 * Rôle : Gère les actions liées à la page d'accueil du site EnergyDash
 * Auteur : Gustin MAILHE, Lucas LEPAPE
 */

namespace App\Controllers;

use App\Core\Controller;

class HomeController extends Controller {

    public function index(): void{
        $this->render('home/index', ['title' => 'Accueil']);
    }
}