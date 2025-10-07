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
            $confirm_password = $_POST['confirm_password'] ?? '';
            $first_name = $_POST['first_name'] ?? '';
            $last_name = $_POST['last_name'] ?? '';

            // Vérification des champs vides
            if (empty($email) || empty($password) || empty($confirm_password) || empty($first_name) || empty($last_name)) {
                $error = "Tous les champs doivent être remplis.";

            } 
            // Vérification du format d'email
            elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error = "L'adresse e-mail est invalide. Exemple : nom@domaine.com";
            }
            // Vérification de la compléxité du mot de passe (OWASP)
            elseif (!$this->isPasswordStrong($password)) {
                $error = "Le mot de passe doit contenir au minimum 8 caractères, une majuscule, une minuscule, un chiffre et un symbol spécial";
            }
            // Vérification de la correspondance des mots de passe
            elseif ($password !== $confirm_password) {
                $error = "Les mots de passe ne correspondent pas.";
            }
            // Vérification de l'unicité de l'email
            elseif ($this->userModel->emailExists($email)) {
                $error = "L'email est déjà utilisé.";
            } 
            // Tout est OK -> enregistrement
            else {
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

    private function isPasswordStrong($password) {
    // Longueur minimale : 8 caractères
    if (strlen($password) < 8) {
        return false;
    }

    // Au moins une majuscule
    if (!preg_match('/[A-Z]/', $password)) {
        return false;
    }

    // Au moins une minuscule
    if (!preg_match('/[a-z]/', $password)) {
        return false;
    }

    // Au moins un chiffre
    if (!preg_match('/[0-9]/', $password)) {
        return false;
    }

    // Au moins un caractère spécial
    if (!preg_match('/[\W_]/', $password)) { // \W = tout sauf lettre/chiffre
        return false;
    }

    return true;
    }
}
