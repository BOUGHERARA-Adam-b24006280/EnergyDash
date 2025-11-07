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
        parent::__construct(); // démarre la session
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
            $viewPath = __DIR__ . '/../Views/profile/profile_admin.php';
        } else {
            $users = [];
            $viewPath = __DIR__ . '/../Views/profile/profile.php';
        }

        // Appelle la vue profile.php via Layout
        $layout = new Layout($viewPath, 'Profil');
        $layout->render(['user' => $user, 'users' => $users,]);
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
        $role = filter_input(INPUT_POST, 'role', FILTER_SANITIZE_STRING);

        if ($id && in_array($role, ['admin', 'user'])) {
            $this->userModel->updateUserRole($id, $role);
            $this->flash('success', "Les droits de l’utilisateur ont été mis à jour.");
        } else {
            $this->flash('error', "Erreur : rôle invalide.");
        }

        $this->redirect('/profile');
    }
}