<?php
/**
 * Fichier : ProfileController.php
 * Rôle : 
 * Auteur : Lucas LEPAPE,
 */

namespace App\Controllers;

use App\Core\Controller;
use App\Models\UserModel;
use App\Core\Layout;
use Exception;


class ProfileController extends Controller
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
        $this->requireLogin();
        $user = $_SESSION['user'] ?? null;

        if (!is_array($user) || !isset($user['role'])) {
            $this->redirect('/login');
            exit;
        }

        if ($user['role'] === 'admin') {
            $users = $this->userModel->getAllUsers();
            $viewPath = 'profile/profile_admin';
        } else {
            $users = [];
            $viewPath = 'profile/profile';
        }

        $this->render($viewPath, [
            'title' => 'Mon Profil',
            'user'  => $user,
            'users' => $users
        ]);
    }

    public function update(): void
    {
        $this->requireLogin();

        if (
            !isset($_SESSION['user']) ||
            !is_array($_SESSION['user']) ||
            !isset($_SESSION['user']['id'])
        ) {
            $this->redirect('/login');
            exit;
        }
        assert(is_numeric($_SESSION['user']['id']));
        $id = (int) $_SESSION['user']['id'];

        $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);

        if (!$email) {
            $this->flash('error', "Adresse email invalide.");
            $this->redirect('/profile');
        }

        try {
            $this->userModel->updateUser(
                $id,
                $this->sanitize($_POST['first_name'] ?? ''),
                $this->sanitize($_POST['last_name'] ?? ''),
                $email,
                $_POST['password'] ?? ''
            );

            $_SESSION['user']['email'] = $email;
            $this->flash('success', "Profil mis à jour avec succès.");
        } catch (Exception $e) {
            $this->flash('error', "Erreur lors de la mise à jour du profil.");
        }

        $this->redirect('/profile');
    }


    public function updateRole(): void
    {
        $this->requireAdmin();

        $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        $role = filter_input(INPUT_POST, 'role', FILTER_SANITIZE_SPECIAL_CHARS);

        if ($id && in_array($role, ['user', 'editor'])) {
            
            if ($id === $_SESSION['user']['id']) {
                $this->flash('error', "Vous ne pouvez pas modifier votre propre rôle ici.");
                $this->redirect('/profile');
                exit;
            }

            $this->userModel->updateUserRole($id, $role);
            $this->flash('success', "Le rôle de l'utilisateur a été mis à jour (Admin impossible).");
        } else {
            $this->flash('error', "Action non autorisée ou rôle invalide.");
        }

        $this->redirect('/profile');
    }
}