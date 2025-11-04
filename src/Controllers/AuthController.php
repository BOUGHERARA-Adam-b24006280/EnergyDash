<?php
/**
 * Fichier : AuthController.php
 * Rôle : Gère l'authentification (connexion, inscription, déconnexion)
 * Auteur : Lucas LEPAPE
 */

namespace App\Controllers;

use App\Core\Controller;
use App\Models\UserModel;
use Exception;

final class AuthController extends Controller
{
    private UserModel $userModel;

    public function __construct()
    {
        parent::__construct(); // démarre la session via BaseController
        $this->userModel = new UserModel();
    }

    /**
     * Affiche la page de connexion et traite le login.
     */
    public function login(): void
    {
        $errors = [];

        // Génération du token CSRF si nécessaire
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        // Traitement du formulaire
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $csrfSession = $_SESSION['csrf_token'] ?? '';
                $csrfPost = $_POST['csrf_token'] ?? '';

                if (!hash_equals((string)$csrfSession, (string)$csrfPost)) {
                    throw new Exception("Le formulaire est invalide (CSRF).");
                }

                $email    = $this->sanitize($_POST['email'] ?? '');
                $password = $_POST['password'] ?? '';

                if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    throw new Exception("Adresse e-mail invalide.");
                }

                if (mb_strlen($password) < 8) {
                    throw new Exception("Mot de passe trop court (8 caractères minimum).");
                }

                $user = $this->userModel->getUserByEmail($email);
                if (!$user || !password_verify($password, $user['password'])) {
                    throw new Exception("Identifiants incorrects.");
                }

                // Connexion réussie
                session_regenerate_id(true);
                $_SESSION['user'] = [
                    'id'         => $user['id'],
                    'first_name' => $user['first_name'],
                    'last_name'  => $user['last_name'],
                    'email'      => $user['email'],
                    'role'       => $user['role'] ?? 'user'
                ];

                unset($_SESSION['csrf_token']);
                $this->redirect('/dashboard');

            } catch (Exception $e) {
                $errors[] = $e->getMessage();
            }
        }

        $this->render(__DIR__ . '/../Views/auth/login.php', 'Connexion', 'default', [
            'errors' => $errors,
            'csrf_token' => $_SESSION['csrf_token'],
        ]);
    }

    /**
     * Affiche la page d'inscription et gère la création de compte.
     */
    public function register(): void
    {
        $errors = [];

        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $csrfSession = $_SESSION['csrf_token'] ?? '';
                $csrfPost = $_POST['csrf_token'] ?? '';
                if (!hash_equals((string)$csrfSession, (string)$csrfPost)) {
                    throw new Exception("Le formulaire est invalide (CSRF).");
                }

                $firstName = $this->sanitize($_POST['first_name'] ?? '');
                $lastName  = $this->sanitize($_POST['last_name'] ?? '');
                $email     = $this->sanitize($_POST['email'] ?? '');
                $password  = $_POST['password'] ?? '';
                $confirm   = $_POST['confirm_password'] ?? '';

                if ($firstName === '' || mb_strlen($firstName) > 100)
                    throw new Exception("Le prénom est obligatoire et doit faire moins de 100 caractères.");
                if ($lastName === '' || mb_strlen($lastName) > 100)
                    throw new Exception("Le nom est obligatoire et doit faire moins de 100 caractères.");
                if (!filter_var($email, FILTER_VALIDATE_EMAIL))
                    throw new Exception("Adresse e-mail invalide.");
                if (mb_strlen($password) < 8)
                    throw new Exception("Le mot de passe doit contenir au moins 8 caractères.");
                if ($password !== $confirm)
                    throw new Exception("Les mots de passe ne correspondent pas.");
                if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/', $password))
                    throw new Exception("Le mot de passe doit contenir une majuscule, une minuscule, un chiffre et un caractère spécial.");
                if ($this->userModel->emailExists($email))
                    throw new Exception("Un compte avec cette adresse e-mail existe déjà.");

                // Création du compte
                if ($this->userModel->createUser($firstName, $lastName, $email, $password)) {
                    unset($_SESSION['csrf_token']);
                    $this->redirect('/login?registered=1');
                } else {
                    throw new Exception("Une erreur est survenue lors de l’inscription.");
                }

            } catch (Exception $e) {
                $errors[] = $e->getMessage();
            }
        }

        $this->render(__DIR__ . '/../Views/auth/register.php', 'Inscription', 'default', [
            'errors' => $errors,
            'csrf_token' => $_SESSION['csrf_token'],
        ]);
    }

    /**
     * Déconnecte l'utilisateur.
     */
    public function logout(): void
    {
        session_destroy();
        $this->redirect('/login');
    }

    /**
     * Index par défaut -> redirige vers /login
     */
    public function index(): void
    {
        $this->redirect('/login');
    }
}