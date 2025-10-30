<?php
/**
 * Fichier : AuthController.php
 * Rôle : Gère les actions d'authentification (inscription, connexion, déconnexion)
 * Auteur : Lucas LEPAPE,
 */

namespace App\Controllers;

use App\Core\Layout;

/**
 * Classe DashboardController qui gère la gestion et les actions des différentes pages du Dashboard
 */
class DashboardController {

    /**
     * Constructeur qui inititalise les dépendances
     */
    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Vérifie l'authentification avant d'afficher le dashboard
     */
    public function index(): void {
        if (empty($_SESSION['user'])) {
            header('Location: /login');
            exit;
        }

        $layout = new Layout(__DIR__ . '/../Views/dashboard/dashboard.php');
        $layout->render(['user' => $_SESSION['user']]);
    }
}