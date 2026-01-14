<?php
/**
 * Fichier : ProfileController.php
 * Rôle : Gère l'affichage et la modification du profil utilisateur ainsi que l'administration des rôles.
 */

namespace App\Controllers;

use App\Core\Controller;
use App\Models\UserModel;
use Exception;

/**
 * Contrôleur ProfileController
 * Gère la consultation et la modification du profil utilisateur.
 * Gère également l'administration des utilisateurs pour les admins.
 *
 * @package App\Controllers
 */
class ProfileController extends Controller {
    /** @var UserModel Instance du modèle utilisateur */
    private UserModel $userModel;

    /**
     * Constructeur.
     * Initialise la session si nécessaire et le modèle utilisateur.
     */
    public function __construct(){
        parent::__construct();
        $this->userModel = new UserModel();
    }

    /**
     * Affiche la page de profil.
     * Si l'utilisateur est admin, affiche également la liste des utilisateurs.
     * Route: GET /profile
     *
     * @return void
     */
    public function index(): void {
        $this->requireLogin();

        /** @var array{id: int|string, role: string, first_name: string, last_name: string, email: string} $user */
        $user = $_SESSION['user'];

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

    /**
     * Met à jour les informations du profil connecté (Nom, Prénom, Email, MDP).
     * Route: POST /profile/update
     * 
     * @return void
     */
    public function update(): void {
        
        $this->requireLogin();

        if (!isset($_SESSION['user']) || !is_array($_SESSION['user'])) {
            $this->redirect('/login');
            return;
        }

        $userId = $_SESSION['user']['id'] ?? 0;
        if (!is_numeric($userId)) {
             $this->flash('error', "ID utilisateur invalide.");
             $this->redirect('/logout');
             return;
        }
        $id = (int)$userId;

        $rawEmail = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
        
        if (!$rawEmail) {
            $this->flash('error', "Adresse email invalide.");
            $this->redirect('/profile');
            return;
        }
        $email = (string)$rawEmail;

        $rawFirstName = $_POST['first_name'] ?? '';
        $rawLastName = $_POST['last_name'] ?? '';
        $rawPassword = $_POST['password'] ?? '';

        $firstName = $this->sanitize(is_string($rawFirstName) ? $rawFirstName : '');
        $lastName  = $this->sanitize(is_string($rawLastName) ? $rawLastName : '');
        $password  = is_string($rawPassword) ? $rawPassword : '';

        try {
            $this->userModel->updateUser(
                $id,
                $firstName,
                $lastName,
                $email,
                $password
            );

            $_SESSION['user']['first_name'] = $firstName;
            $_SESSION['user']['last_name']  = $lastName;
            $_SESSION['user']['email']      = $email;

            $this->flash('success', "Profil mis à jour avec succès.");
        } catch (Exception $e) {
            $this->flash('error', "Erreur lors de la mise à jour du profil.");
        }

        $this->redirect('/profile');
    }


    /**
     * Met à jour le rôle d'un utilisateur (Admin seulement).
     * Route: POST /profile/updateRole
     *
     * @return void
     */
    public function updateRole(): void {
        $this->requireAdmin();

        if (!isset($_SESSION['user']) || !is_array($_SESSION['user'])) {
             $this->redirect('/login');
             return;
        }

        $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        $rawRole = filter_input(INPUT_POST, 'role', FILTER_SANITIZE_SPECIAL_CHARS);
        $role = is_string($rawRole) ? $rawRole : '';

        if ($id && in_array($role, ['user', 'editor'])) {
            
            $currentUserId = $_SESSION['user']['id'] ?? null;

            if ($id === $currentUserId) {
                $this->flash('error', "Vous ne pouvez pas modifier votre propre rôle ici.");
                $this->redirect('/profile');
                return;
            }

            $this->userModel->updateUserRole($id, $role);
            $this->flash('success', "Le rôle de l'utilisateur a été mis à jour (Admin impossible).");
        } else {
            $this->flash('error', "Action non autorisée ou rôle invalide.");
        }

        $this->redirect('/profile');
    }
}