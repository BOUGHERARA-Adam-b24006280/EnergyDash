<?php
/**
 * Fichier : AuthController.php
 * Rôle : Gère les actions d'authentification (inscription, connexion, déconnexion)
 * Auteur : Lucas LEPAPE,
 */

namespace App\Controllers;

use App\Core\Layout;

require_once __DIR__ . '/../Models/UserModel.php';

/**
 * Classe Authcontroller qui gère la gestion et les actions des différentes pages d'authentifications
 */
class AuthController {
    
    /**
     * Affiche la page de connexion en utilisant la classe Layout pour afficher la page complète
     * 
     * @return void
     */
    public function login(): void {
        $layout = new Layout(__DIR__ . '/../Views/auth/login.php', 'Connexion');
        $layout->render();
    }

    /**
     * Affiche la page d'inscription en utilisant la classe Layout pour afficher la page complète
     * 
     * @return void
     */
    public function register(): void {
        $layout = new Layout(__DIR__ . '/../Views/auth/register.php', 'Inscription');
        $layout->render();
    }

    /**
     * Déconnecte l'utilisateur actif et le redirige vers la page de connexion
     * 
     * @return void
     */
    public function logout(): void {
        session_start();
        session_destroy();
        header('Location: /login');
    }
}