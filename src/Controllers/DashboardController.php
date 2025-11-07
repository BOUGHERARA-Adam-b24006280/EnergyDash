<?php
/**
 * Fichier : DashboardController.php
 * Rôle : 
 * Auteur : Lucas LEPAPE
 */

namespace App\Controllers;

use App\Core\Layout;

/**
 * Classe DashboardController
 * Vérifie l'authentification et affiche le tableau de bord utilisateur
 */

class DashboardController extends Controller
{
    public function index(): void
    {
        $this->requireLogin();

        $layout = new Layout(__DIR__ . '/../Views/dashboard/dashboard.php', 'Dashboard');

        $layout->render(['user' => $_SESSION['user']]);
    }
}