<?php
/**
 * Fichier : DashboardController.php
 * Rôle : 
 * Auteur : Lucas LEPAPE,
 */

namespace App\Controllers;

use App\Core\Layout;
Use Exception;

/**
 * Classe DashboardController
 * Vérifie l'authentification et affiche le tableau de bord utilisateur
 */
class DashboardController
{
    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public function index(): void
    {
        if (empty($_SESSION['user'])) {
            header('Location: /login');
            exit;
        }

        $layout = new Layout(__DIR__ . '/../Views/dashboard/dashboard.php', 'Dashboard');

        $layout->render(['user' => $_SESSION['user']]);
    }
}
