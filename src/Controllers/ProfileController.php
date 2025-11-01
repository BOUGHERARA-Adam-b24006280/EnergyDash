<?php
/**
 * Fichier : ProfileController.php
 * Rôle : 
 * Auteur : Lucas LEPAPE,
 */

namespace App\Controllers;

use App\Core\Layout;

class ProfileController
{
    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public function index(): void
    {
        // Vérifie que l’utilisateur est connecté
        if (empty($_SESSION['user'])) {
            header('Location: /login');
            exit;
        }

        $user = $_SESSION['user'];

        // Appelle la vue profile.php via Layout
        $layout = new Layout(__DIR__ . '/../Views/profile/profile.php', 'Profil', 'dashboard');
        $layout->render(['user' => $user]);
    }
}