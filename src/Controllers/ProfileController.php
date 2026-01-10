<?php
/**
 * Fichier : ProfileController.php
 * Rôle : 
 * Auteur : Lucas LEPAPE,
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

        assert(is_numeric($_SESSION['user']['id']));
        $id = (int) $_SESSION['user']['id'];

        $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);

        if (!$email) {
            $this->flash('error', "Adresse email invalide.");
            $this->redirect('/profile');
        }

        // 1. On récupère et nettoie les variables AVANT de les utiliser
        $firstName = $this->sanitize($_POST['first_name'] ?? '');
        $lastName  = $this->sanitize($_POST['last_name'] ?? '');
        $password  = $_POST['password'] ?? '';

        try {
            // 2. Mise à jour en base de données
            $this->userModel->updateUser(
                $id,
                $firstName,
                $lastName,
                $email,
                $password
            );

            // 3. IMPORTANT : Mise à jour de la session pour l'affichage immédiat
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