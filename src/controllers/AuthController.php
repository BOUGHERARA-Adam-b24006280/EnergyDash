<?php
/**
 * Fichier : AuthController.php
 * Rôle : Gère les actions d'authentification (inscription, connexion, déconnexion)
 * Auteur : Lucas LEPAPE,
 */

require_once __DIR__ . '/../models/UserModel.php';

class AuthController {
    public function login() {
        include __DIR__ . '/../views/auth/Login.php';
    }

    public function register() {
        include __DIR__ . '/../views/auth/Register.php';
    }

    public function logout() {
        session_start();
        session_destroy();
        header('Location: /login');
    }
}