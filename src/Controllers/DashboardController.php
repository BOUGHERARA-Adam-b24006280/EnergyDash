<?php
/**
 * Fichier : AuthController.php
 * Rôle : Gère les actions d'authentification (inscription, connexion, déconnexion)
 * Auteur : Lucas LEPAPE,
 */

namespace App\Controllers;

use App\Core\Layout;
use App\Models\EnergyModel;
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

        $layout = new Layout(
            __DIR__ . '/../Views/dashboard/dashboard.php',
            'Dashboard',
            'dashboard'
        );

        $layout->render(['user' => $_SESSION['user']]);
    }
}