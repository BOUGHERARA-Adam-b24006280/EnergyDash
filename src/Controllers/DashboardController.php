<?php
/**
 * Fichier : DashboardController.php
 * Rôle : 
 * Auteur : Lucas LEPAPE
 */

namespace App\Controllers;

use App\Core\Controller;

/**
 * Classe DashboardController
 * Vérifie l'authentification et affiche le tableau de bord utilisateur
 */

class DashboardController extends Controller
{
    public function index(): void
    {
        $this->requireLogin();

        $this->render(
            __DIR__ . '/../Views/dashboard/dashboard.php',
            'Dashboard',
            'dashboard',
            ['user' => $_SESSION['user']]
        );
    }
}