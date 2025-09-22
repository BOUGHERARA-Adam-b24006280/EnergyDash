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
     if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = $_POST['email'];
            $password = $_POST['password'];
            $first_name = $_POST['first_name'];
            $last_name = $_POST['last_name'];

            // Vérifier que tous les champs sont remplis
            if (empty($email) || empty($password) || empty($first_name) || empty($last_name)) {
                $error = "Tous les champs doivent être remplis.";
            } elseif ($this->userModel->emailExists($email)) {
                $error = "L'email est déjà utilisé.";
            } else {
                if ($this->userModel->register($first_name, $last_name, $email, $password)) {
                    header('Location: /login');
                    exit;
                } else {
                    $error = "Erreur lors de l'inscription.";
                }
            }
        }

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
