<?php

require_once __DIR__ . '/../models/UserModel.php';

class AuthController {
    private $pdo;
    private $userModel;

    public function __construct($pdo) {
        $this->pdo = $pdo;
        $this->userModel = new UserModel($pdo);
    }

    // Afficher le formulaire de connexion
    public function login() {
        $title = "Connexion";
        include __DIR__ . '/../views/auth/login.php';
    }

    // Afficher le formulaire d'inscription
    public function register() {
        $title = "Inscription";
        include __DIR__ . '/../views/auth/register.php';
    }

    // Déconnexion
    public function logout() {
        session_destroy();
        header('Location: /login');
        exit;
    }
}
?>
