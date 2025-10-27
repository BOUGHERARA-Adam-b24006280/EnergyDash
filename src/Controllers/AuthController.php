<?php
/**
 * Fichier : AuthController.php
 * Rôle : Gère les actions d'authentification (inscription, connexion, déconnexion)
 * Auteur : Lucas LEPAPE,
 */

namespace App\Controllers;

use App\Core\Layout;
use App\Models\UserModel;
use Exception;

require_once __DIR__ . '/../Models/UserModel.php';

/**
 * Classe Authcontroller qui gère la gestion et les actions des différentes pages d'authentifications
 */
class AuthController {
    
    /** @var UserModel */
    private UserModel $userModel;

    /**
     * Constructeur qui inititalise les dépendances
     */
    public function __construct() {
        $this->userModel = new UserModel();

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Affiche la page de connexion en utilisant la classe Layout pour afficher la page complète
     * 
     * @return void
     */
    public function login(): void {
        $errors = [];

        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $csrfSession = $_SESSION['csrf_token'] ?? '';
                $csrfPost = $_POST['csrf_token'] ?? '';

                if (!is_string($csrfPost) || !is_string($csrfSession) || !hash_equals($csrfSession, $csrfPost)) {
                    throw new Exception("Le formulaire est invalide (CSRF).");
                }

                $email    = isset($_POST['email']) && is_scalar($_POST['email']) ? trim((string)$_POST['email']) : '';
                $password = isset($_POST['password']) && is_scalar($_POST['password']) ? (string)$_POST['password'] : '';

                if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new Exception("Adresse e-mail invalide.");
                }
                if (mb_strlen($password) < 8) {
                    throw new Exception("Le mot de passe est trop court.");
                }

                $user = $this->userModel->getUserByEmail($email);
                if (!$user) {
                    throw new Exception("Aucun compte trouvé avec cet e-mail.");
                }

                $hash = $user['password'];
                if (!password_verify($password, $hash)) {
                    throw new Exception("Mot de passe incorrect.");
                }

                session_regenerate_id(true); // Empêche le vol de session (OWASP #7)
                $_SESSION['user'] = [
                    'id' => $user['id'],
                    'first_name' => $user['first_name'],
                    'last_name' => $user['last_name'],
                    'email' => $user['email'],
                ];

                unset($_SESSION['csrf_token']);

                header('Location: /dashboard');
                exit;
            } catch (Exception $e) {
                $errors[] = $e -> getMessage();
            }
        }

        $layout = new Layout(__DIR__ . '/../Views/auth/login.php', 'Connexion');
        $layout->render(['errors' => $errors, 'csrf_token' => $_SESSION['csrf_token'], ]);
    }

    /**
     * Affiche la page d'inscription en utilisant la classe Layout pour afficher la page complète
     * 
     * @return void
     */
    public function register(): void {
        $errors = [];

        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $csrfSession = $_SESSION['csrf_token'] ?? '';
                $csrfPost = $_POST['csrf_token'] ?? '';
                
                if (!is_string($csrfPost) || !is_string($csrfSession) || !hash_equals($csrfSession, $csrfPost)) {
                    throw new Exception("Le formulaire est invalide (CSRF).");
                }
                
                $firstName = isset($_POST['first_name']) && is_scalar($_POST['first_name']) ? trim((string)$_POST['first_name']) : '';
                $lastName  = isset($_POST['last_name'])  && is_scalar($_POST['last_name'])  ? trim((string)$_POST['last_name'])  : '';
                $email     = isset($_POST['email'])      && is_scalar($_POST['email'])      ? trim((string)$_POST['email'])      : '';
                $password  = isset($_POST['password'])   && is_scalar($_POST['password'])   ? (string)$_POST['password']         : '';
                $confirm   = isset($_POST['confirm_password']) && is_scalar($_POST['confirm_password']) ? (string)$_POST['confirm_password'] : '';

                if ($firstName === '' || mb_strlen($firstName) > 100)
                    throw new Exception("Le prénom est obligatoire et doit faire moins de 100 caractères.");
                if ($lastName === '' || mb_strlen($lastName) > 100)
                    throw new Exception("Le nom est obligatoire et doit faire moins de 100 caractères.");
                if (!filter_var($email, FILTER_VALIDATE_EMAIL))
                    throw new Exception("L'adresse e-mail n'est pas valide.");
                if (mb_strlen($password) < 8)
                    throw new Exception("Le mot de passe doit contenir au moins 8 caractères.");
                if ($password !== $confirm)
                    throw new Exception("Les mots de passe ne correspondent pas.");
                if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/', $password))
                    throw new Exception("Le mot de passe doit contenir au moins une majuscule, une minuscule, un chiffre et un caractère spécial.");
                if ($this->userModel->emailExists($email))
                    throw new Exception("Un compte avec cette adresse e-mail existe déjà.");

                if ($this->userModel->createUser($firstName, $lastName, $email, $password)) {
                        unset($_SESSION['csrf_token']);
                        header('Location: /login?registered=1');
                        exit;
                    } else {
                        throw new Exception("Une erreur est survenue lors de l’inscription.");
                    }
            } catch (Exception $e) {
                $errors[] = $e->getMessage();
            }
        }

        $layout = new Layout(__DIR__ . '/../Views/auth/register.php', 'Inscription');
        $layout->render(['errors' => $errors, 'csrf_token' => $_SESSION['csrf_token'], ]);
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
        exit;
    }
}