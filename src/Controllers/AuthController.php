<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\UserModel;
use Exception;

/**
 * Contrôleur gérant l'authentification des utilisateurs.
 *
 * Ce contrôleur gère la connexion, la déconnexion, l'inscription,
 * et la réinitialisation de mot de passe.
 *
 * @package App\Controllers
 */
class AuthController extends Controller {
    /**
     * Modèle pour la gestion des utilisateurs.
     * @var UserModel
     */
    private UserModel $userModel;

    /**
     * Constructeur.
     * Initialise le modèle utilisateur.
     */
    public function __construct() {
        parent::__construct();
        $this->userModel = new UserModel();
    }

    // Connexion

    /**
     * Affiche le formulaire de connexion.
     *
     * Initialise le token CSRF et charge la vue de connexion.
     *
     * @return void
     */
    public function showLogin(): void {
        $this->initCsrf();
        $this->render('auth/login', [
            'title' => 'Connexion',
            'csrf_token' => $_SESSION['csrf_token']
        ]);
    }

    /**
     * Traite la soumission du formulaire de connexion.
     *
     * Vérifie le token CSRF, valide les identifiants utilisateur,
     * crée la session utilisateur et redirige vers le tableau de bord.
     * En cas d'échec, réaffiche le formulaire avec les erreurs.
     *
     * @return void
     */
    public function processLogin(): void {
        try {
            $this->validateCsrf();

            $email = trim((string)$_POST['email'] ?? '');
            $password = (string)$_POST['password'] ?? '';

            $user = $this->userModel->verifyLogin($email, $password);

            if (!$user) {
                usleep(500000); // 0.5 seconde de délai pour ralentir les attaques par force brute
                throw new Exception("Identifiants incorrects.");
            }

            $this->createSession($user);

            $this->redirect('/dashboard');

        } catch (Exception $e) {
            $this->render('auth/login', [
                'title' => 'Connexion',
                'errors' => [$e->getMessage()],
                'csrf_token' => $_SESSION['csrf_token']
            ]);
        }
    }

    // Déconnexion

    /**
     * Déconnecte l'utilisateur.
     *
     * Détruit la session active et supprime le cookie de session,
     * puis redirige vers la page de connexion.
     *
     * @return void
     */
    public function logout(): void {
        $_SESSION = [];

        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(
                (string)session_name(),
                '',
                time() - 42000,
                $params["path"],
                $params["domain"],
                $params["secure"],
                $params["httponly"]
            );
        }

        session_destroy();

        $this->redirect('/login');
    }

    // Inscription

    /**
     * Affiche le formulaire d'inscription.
     *
     * Initialise le token CSRF et charge la vue d'inscription.
     *
     * @return void
     */
    public function showRegister(): void {
        $this->initCsrf();
        $this->render('auth/register', [
            'title' => 'Inscription',
            'csrf_token' => $_SESSION['csrf_token']
        ]);
    }

    /**
     * Traite la soumission du formulaire d'inscription.
     *
     * Vérifie le token CSRF, valide les données saisies, vérifie l'unicité de l'email,
     * crée le nouvel utilisateur et redirige vers la connexion.
     *
     * @return void
     */
    public function processRegister(): void {
        try {
            $this->validateCsrf();

            $data = [
                'first_name' => trim((string)$_POST['first_name'] ?? ''),
                'last_name' => trim((string)$_POST['last_name'] ?? ''),
                'email' => trim((string)$_POST['email'] ?? ''),
                'password' => (string)$_POST['password'] ?? '',
                'confirm' => (string)$_POST['confirm_password'] ?? ''
            ];

            // Validation des champs vides, format, etc.
            $this->validateRegisterInput($data);

            // Validation unicité de l'email
            if ($this->userModel->emailExists($data['email'])) {
                throw new Exception("Un compte avec cette adresse e-mail existe déjà.");
            }

            // Création du compte
            if ($this->userModel->createUser($data['first_name'], $data['last_name'], $data['email'], $data['password'])) {
                unset($_SESSION['csrf_token']);
                $this->redirect('/login?registered=1');
            } else {
                throw new Exception("Erreur technique lors de l'inscription.");
            }

        } catch (Exception $e) {
            $this->render('auth/register', [
                'title' => 'Inscription',
                'errors' => [$e->getMessage()],
                'csrf_token' => $_SESSION['csrf_token'],
                'old' => $_POST //Pour réafficher les données saisies dans le formulaire
            ]);
        }
    }

    // Mot de passe oublié

    /**
     * Affiche le formulaire de demande de réinitialisation de mot de passe.
     *
     * @return void
     */
    public function showForgot(): void {
        $this->initCsrf();
        $this->render('auth/forgot', [
            'title' => 'Mot de passe oublié',
            'csrf_token' => $_SESSION['csrf_token']
        ]);
    }

    /**
     * Traite la demande de réinitialisation de mot de passe.
     *
     * Vérifie si l'email existe, génère un token de réinitialisation,
     * et envoie un email avec le lien de réinitialisation.
     * Inclut une protection contre les attaques temporelles.
     *
     * @return void
     */
    public function processForgot(): void {
        $start_time = microtime(true);
        $success = '';

        try {
            $this->validateCsrf();
            $email = trim((string)$_POST['email'] ?? '');

            if (!filter_var($email, FILTER_VALIDATE_EMAIL))
                throw new Exception("Email invalide.");

            $result = $this->userModel->createResetTokenForEmail($email);

            if ($result) {
                $baseUrl = defined('BASE_URL') ? BASE_URL : 'http://localhost:8000';
                $resetLink = rtrim($baseUrl, '/') . "/reset?token=" . $result['token'];

                $firstName = $result['user']['first_name'] ?? 'Utilisateur';

                $subject = "Réinitialisation de votre mot de passe";
                $message = "Bonjour {$firstName},\n\n" .
                    "Vous avez demandé à réinitialiser votre mot de passe.\n" .
                    "Cliquez sur le lien ci-dessous pour choisir un nouveau mot de passe :\n" .
                    $resetLink . "\n\n" .
                    "Ce lien expire dans 30 minutes.\n\n" .
                    "Si vous ne l'avez pas demandé, ignorez cet e-mail.\n" .
                    "L'équipe Energy Dash.";

                $mailer = new \App\Core\Mailer();
                $mailer->send($email, $subject, $message);
            }

            $success = "Si ce compte existe, un email a été envoyé.";

        } catch (Exception $e) {
            $success = "Si ce compte existe, un email a été envoyé.";
        }

        // Protection contre les attaques temporelles
        $elapsed = microtime(true) - $start_time;
        if ($elapsed < 1.0) {
            usleep((int) ((1.0 - $elapsed) * 1000000));
        }

        $this->render('auth/forgot', [
            'title' => 'Mot de passe oublié',
            'success' => $success,
            'csrf_token' => $_SESSION['csrf_token']
        ]);
    }

    // Réinitialisation mot de passe

    /**
     * Affiche le formulaire de réinitialisation de mot de passe.
     *
     * Vérifie la validité du token fourni dans l'URL.
     *
     * @return void
     */
    public function showReset(): void {
        $this->initCsrf();
        
        $token = $_GET['token'] ?? '';
        $errors = [];

        // Vérification préventive pour UX
        if (empty($token) || !$this->userModel->getUserByToken(hash('sha256', $token))) {
            $errors[] = "Ce lien est invalide ou a expiré.";
        }

        $this->render('auth/reset', [
            'title' => 'Réinitialisation',
            'errors' => $errors,
            'csrf_token' => $_SESSION['csrf_token']
        ]);
    }

    /**
     * Traite la réinitialisation du mot de passe.
     *
     * Vérifie le token, valide le nouveau mot de passe, met à jour le mot de passe
     * de l'utilisateur et invalide tous les tokens de réinitialisation associés.
     *
     * @return void
     */
    public function processReset(): void {
        try {
            $this->validateCsrf();

            $token = $_POST['token'] ?? $_GET['token'] ?? '';
            $password = $_POST['password'] ?? '';
            $confirm = $_POST['confirm_password'] ?? '';

            if (empty($token))
                throw new Exception("Token manquant.");

            $hashedToken = hash('sha256', $token);
            $user = $this->userModel->getUserByToken($hashedToken);

            if (!$user) {
                usleep(500000); // 0.5s délai
                throw new Exception("Lien invalide ou expiré.");
            }

            if ($password !== $confirm) {
                throw new Exception("Les mots de passe ne correspondent pas.");
            }

            if (!UserModel::isPasswordStrong($password)) {
                throw new Exception("Le mot de passe n'est pas assez sécurisé.");
            }

            $this->userModel->updatePassword($user['id'], $password);
            // On invalide TOUS les tokens de cet utilisateur par sécurité
            $this->userModel->deleteResetTokensForUser($user['id']);

            $this->flash('success', "Mot de passe modifié avec succès. Vous pouvez vous connecter.");
            $this->redirect('/login');

        } catch (Exception $e) {
            $this->render('auth/reset', [
                'title' => 'Réinitialisation',
                'errors' => [$e->getMessage()],
                'csrf_token' => $_SESSION['csrf_token']
            ]);
        }
    }

    // Méthodes utilitaires

    /**
     * Crée la session utilisateur après une connexion réussie.
     *
     * @param array $user Les données de l'utilisateur (id, email, prénon, nom, role).
     * @return void
     */
    private function createSession(array $user): void {
        session_regenerate_id(true);
        $_SESSION['user'] = [
            'id' => $user['id'],
            'email' => $user['email'],
            'first_name' => $user['first_name'],
            'last_name' => $user['last_name'],
            'role' => $user['role'] ?? 'user'
        ];
        unset($_SESSION['csrf_token']);
    }

    /**
     * Initialise le token CSRF s'il n'existe pas déjà.
     *
     * @return void
     */
    private function initCsrf(): void {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
    }

    /**
     * Valide le token CSRF reçu en POST.
     *
     * @throws Exception Si le token est invalide ou manquant.
     * @return void
     */
    private function validateCsrf(): void {
        $token = $_POST['csrf_token'] ?? '';
        if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
            throw new Exception("Session expirée. Veuillez recharger la page.");
        }
    }

    /**
     * Valide les données d'inscription.
     *
     * @param array $data Tableau contenant les champs (first_name, last_name, email, password, confirm).
     * @throws Exception Si une validation échoue.
     * @return void
     */
    private function validateRegisterInput(array $data): void{
        if (empty($data['first_name']) || mb_strlen($data['first_name']) > 100) {
            throw new Exception("Prénom invalide.");
        }
        if (empty($data['last_name']) || mb_strlen($data['last_name']) > 100) {
            throw new Exception("Nom invalide.");
        }
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Email invalide.");
        }
        if ($data['password'] !== $data['confirm']) {
            throw new Exception("Les mots de passe ne correspondent pas.");
        }
        if (!UserModel::isPasswordStrong($data['password'])) {
            throw new Exception("Le mot de passe n'est pas assez sécurisé.");
        }
    }
}