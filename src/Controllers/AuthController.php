<?php
/**
 * Fichier : AuthController.php
 * Rôle : Gère les actions d'authentification (inscription, connexion, déconnexion)
 * Auteur : Lucas LEPAPE,
 */

namespace App\Controllers;

use App\Core\Controller;

require_once __DIR__ . '/../models/UserModel.php';

class AuthController extends Controller {
    public function login() {
        include __DIR__ . '/../views/auth/login.php';
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