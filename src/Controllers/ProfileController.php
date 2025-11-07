<?php
/**
 * Fichier : ProfileController.php
 * Rôle : 
 * Auteur : Lucas LEPAPE,
 */

namespace App\Controllers;

use App\Core\Layout;
use App\Models\UserModel;
use Exception;

class ProfileController
{
    private UserModel $userModel;

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->userModel = new UserModel();
    }

    public function index(): void
    {

        if (empty($_SESSION['user'])) {
            header('Location: /login');
            exit;
        }

        $user = $_SESSION['user'];
        
        if ($user['role'] === 'admin') {
            $users = $this->userModel->getAllUsers();
            $viewPath = __DIR__ . '/../Views/profile/profile_admin.php';
        } else {
            $users = []; // valeur par défaut vide
            $viewPath = __DIR__ . '/../Views/profile/profile.php';
        }

        // Appelle la vue profile.php via Layout
        $layout = new Layout($viewPath, 'Profil');
        $layout->render(['user' => $user, 'users' => $users,]);
    }

    public function update(): void
    {
        if (empty($_SESSION['user'])) {
            header('Location: /login');
            exit;
        }

        $id = $_SESSION['user']['id'];

        $firstName = filter_input(INPUT_POST, 'first_name', FILTER_SANITIZE_STRING);
        $lastName  = filter_input(INPUT_POST, 'last_name', FILTER_SANITIZE_STRING);
        $email     = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
        $password  = $_POST['password'] ?? '';

        if (!$email) {
            $_SESSION['error'] = "Adresse email invalide.";
            header('Location: /profile');
            exit;
        }

        try {
            $this->userModel->updateUser($id, $firstName, $lastName, $email, $password);
            $_SESSION['user']['first_name'] = $firstName;
            $_SESSION['user']['last_name']  = $lastName;
            $_SESSION['user']['email']      = $email;
            $_SESSION['success'] = "Profil mis à jour avec succès.";
        } catch (Exception $e) {
            $_SESSION['error'] = "Erreur lors de la mise à jour du profil.";
        }

        header('Location: /profile');
        exit;
    }

    public function updateRole(): void
    {
        if (empty($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
            header('Location: /dashboard');
            exit;
        }

        $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        $role = filter_input(INPUT_POST, 'role', FILTER_SANITIZE_STRING);

        if ($id && in_array($role, ['admin', 'user'])) {
            $this->userModel->updateUserRole($id, $role);
            $_SESSION['success'] = "Les droits de l’utilisateur ont été mis à jour.";
        } else {
            $_SESSION['error'] = "Erreur : rôle invalide.";
        }

        header('Location: /profile');
        exit;
    }
}
