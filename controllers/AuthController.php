<?php
require_once __DIR__ . '/../models/UserModel.php';

class AuthController {
    private $pdo;
    private $userModel;

    public function __construct($pdo) {
        $this->pdo = $pdo;
        $this->userModel = new UserModel($pdo);
    }

    // Afficher / traiter le formulaire de connexion
    public function login() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';

            if (empty($email) || empty($password)) {
                $error = "Tous les champs doivent être remplis.";
            } else {
                $user = $this->userModel->login($email, $password);
                if ($user) {
                    $_SESSION['user'] = [
                        'id' => $user['id'],
                        'email' => $user['email'],
                    ];
                    header('Location: ' . BASE_URL . '/dashboard');
                    exit;
                } else {
                    $error = "Email ou mot de passe incorrect.";
                }
            }
        }

        $title = "Connexion";
        include __DIR__ . '/../views/auth/login.php';
    }

    // Afficher / traiter le formulaire d'inscription
    public function register() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';
            $first_name = $_POST['first_name'] ?? '';
            $last_name = $_POST['last_name'] ?? '';

            if (empty($email) || empty($password) || empty($first_name) || empty($last_name)) {
                $error = "Tous les champs doivent être remplis.";
            } elseif ($this->userModel->emailExists($email)) {
                $error = "L'email est déjà utilisé.";
            } else {
                if ($this->userModel->register($first_name, $last_name, $email, $password)) {
                    header('Location: ' . BASE_URL . '/login');
                    exit;
                } else {
                    $error = "Erreur lors de l'inscription.";
                }
            }
        }

        $title = "Inscription";
        include __DIR__ . '/../views/auth/register.php';
    }

    public function logout() {
        session_destroy();
        header('Location: ' . BASE_URL . '/login');
        exit;
    }
}
