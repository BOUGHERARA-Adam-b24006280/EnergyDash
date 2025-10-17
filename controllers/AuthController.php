<?php
/**
 * Fichier : AuthController.php
 * Rôle : Gère les actions d'authentification (inscription, connexion, déconnexion)
 * Auteur : Lucas LEPAPE,
 */

require_once __DIR__ . '/../models/UserModel.php'

class AuthController {

    public function register(): void{
        if (session_status() === PHP_SESSION_NONE) {
            session_start():
        }

        if(isset($_SESSION['user'])) {
            header('Location : /Dashboard');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email =
            $password =
            $confirm =
        
            
        }
    }
}