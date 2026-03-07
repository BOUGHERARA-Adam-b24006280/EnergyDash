<?php
namespace App\Controllers;

/**
 * Contrôleur AuthController
 * Centralise la logique de sécurité liée aux utilisateurs.
 *
 * @package App\Controllers
 */
class AuthController extends \App\Core\Controller {
    /** @var \App\Models\UserModel Modèle pour la gestion des utilisateurs. */
    private \App\Models\UserModel $userModel;

    /** @var ErrorController Contrôleur pour gérer les redirections d'erreur */
    private ErrorController $errorController;

    /**
     * Constructeur.
     * Initialise le modèle utilisateur.
     */
    public function __construct() {
        parent::__construct();
        $this->userModel = new \App\Models\UserModel();
        $this->errorController = new ErrorController();
    }

    // Connexion

    /**
     * Affiche le formulaire de connexion.
     * Initialise le token CSRF et charge la vue de connexion.
     * Route: GET /login
     *
     * @return void
     */
    public function showLogin(): void {
        $this->initCsrf();
        $this->view->render('auth/login', [
            'title' => 'Connexion',
            'csrf_token' => $_SESSION['csrf_token']
        ]);
    }

    /**
     * Traite la soumission du formulaire de connexion.
     * Vérifie le CSRF, valide les identifiants et crée la session.
     * Intègre une protection (délai) contre les attaques par force brute.
     * Route: POST /login
     *
     * @return void
     */
    public function processLogin(): void {
        try {
            $this->validateCsrf();

            $rawEmail = $_POST['email'] ?? '';
            $rawPass = $_POST['password'] ?? '';

            $email = is_string($rawEmail) ? trim($rawEmail) : '';
            $password = is_string($rawPass) ? $rawPass : '';

            $user = $this->userModel->verifyLogin($email, $password);

            if (!$user) {
                usleep(500000); // 0.5 seconde de délai pour ralentir les attaques par force brute
                throw new \Exception("Identifiants incorrects.");
            }

            /** @var array{id: int|string, email: string, first_name: string, last_name: string, role?: string} $user */
            $this->createSession($user);

            $this->redirect('/dashboard');

        } catch (\Exception $e) {
            $this->view->render('auth/login', [
                'title' => 'Connexion',
                'errors' => [$e->getMessage()],
                'csrf_token' => $_SESSION['csrf_token']
            ]);
        }
    }

    // Déconnexion

    /**
     * Déconnecte l'utilisateur.
     * Détruit la session côté serveur et supprime le cookie de session côté client.
     * Route: GET/POST /logout
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
     * Route: GET /register
     *
     * @return void
     */
    public function showRegister(): void {
        $this->initCsrf();
        $this->view->render('auth/register', [
            'title' => 'Inscription',
            'csrf_token' => $_SESSION['csrf_token']
        ]);
    }

    /**
     * Traite la soumission du formulaire d'inscription.
     *
     * Vérifie le token CSRF, valide les données saisies, vérifie l'unicité de l'email,
     * crée le nouvel utilisateur et redirige vers la connexion.
     * Route: POST /register
     *
     * @return void
     */
    public function processRegister(): void {
        try {
            $this->validateCsrf();

            $pFirstName = $_POST['first_name'] ?? '';
            $pLastName = $_POST['last_name'] ?? '';
            $pEmail = $_POST['email'] ?? '';
            $pPassword = $_POST['password'] ?? '';
            $pConfirm = $_POST['confirm_password'] ?? '';

            $data = [
                'first_name' => is_string($pFirstName) ? trim($pFirstName) : '',
                'last_name'  => is_string($pLastName) ? trim($pLastName) : '',
                'email'      => is_string($pEmail) ? trim($pEmail) : '',
                'password'   => is_string($pPassword) ? $pPassword : '',
                'confirm'    => is_string($pConfirm) ? $pConfirm : ''
            ];

            // Validation des champs vides, format, etc.
            $this->validateRegisterInput($data);

            // Validation unicité de l'email
            if ($this->userModel->emailExists($data['email'])) {
                throw new \Exception("Un compte avec cette adresse e-mail existe déjà.");
            }

            // Création du compte
            if ($this->userModel->createUser($data['first_name'], $data['last_name'], $data['email'], $data['password'])) {
                unset($_SESSION['csrf_token']);
                $this->redirect('/login?registered=1');
            } else {
                throw new \Exception("Erreur technique lors de l'inscription.");
            }

        } catch (\Exception $e) {
            $this->view->render('auth/register', [
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
     * Route: GET /forgot
     *
     * @return void
     */
    public function showForgot(): void {
        $this->initCsrf();
        
        // Message de succès après redirection PRG
        $success = '';
        if (isset($_GET['sent']) && $_GET['sent'] === '1') {
            $success = "Si ce compte existe, un email a été envoyé.";
        }
        
        $this->view->render('auth/forgot', [
            'title' => 'Mot de passe oublié',
            'csrf_token' => $_SESSION['csrf_token'],
            'success' => $success
        ]);
    }

    /**
     * Traite la demande de réinitialisation.
     * Génère un token unique, l'enregistre en base et envoie un email.
     * Inclut une protection contre les attaques temporelles pour ne pas révéler l'existence d'un email.
     * Route: POST /forgot
     *
     * @return void
     */
    public function processForgot(): void {
        $start_time = microtime(true);
        $success = '';

        try {
            $this->validateCsrf();
            $rawEmail = $_POST['email'] ?? '';
            $email = is_string($rawEmail) ? trim($rawEmail) : '';

            if (!filter_var($email, FILTER_VALIDATE_EMAIL))
                throw new \Exception("Email invalide.");

            $result = $this->userModel->createResetTokenForEmail($email);

            if ($result) {
                $baseUrl = (defined('BASE_URL') && is_string(BASE_URL)) ? BASE_URL : 'http://localhost:8000';
                $tokenStr = $result['token'];
                $resetLink = rtrim($baseUrl, '/') . "/reset?token=" . $tokenStr;

                $userArr = $result['user'];
                $rawName = $userArr['first_name'] ?? 'Utilisateur';
                $firstName = is_string($rawName) ? $rawName : 'Utilisateur';

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

        } catch (\Exception $e) {
            $success = "Si ce compte existe, un email a été envoyé.";
        }

        // Protection contre les attaques temporelles
        $elapsed = microtime(true) - $start_time;
        if ($elapsed < 1.0) {
            usleep((int) ((1.0 - $elapsed) * 1000000));
        }

        // Redirection PRG pour éviter la re-soumission lors d'un refresh
        $this->redirect('/forgot?sent=1');
    }

    // Réinitialisation mot de passe

    /**
     * Affiche le formulaire de définition du nouveau mot de passe.
     * Vérifie la validité du token avant d'afficher la page.
     * Route: GET /reset
     *
     * @return void
     */
    public function showReset(): void {
        $this->initCsrf();
        
        $rawToken = $_GET['token'] ?? '';
        $token = is_string($rawToken) ? $rawToken : '';
        $errors = [];
        $isTokenValid = true;

        // Pré-validation du token : s'il est invalide ou expiré, rediriger vers 404
        if (empty($token) || !$this->userModel->getUserByToken(hash('sha256', $token))) {
            $this->errorController->error404page();
            return;
        }

        $this->view->render('auth/reset', [
            'title' => 'Réinitialisation',
            'errors' => $errors,
            'csrf_token' => $_SESSION['csrf_token'],
            'isTokenValid' => $isTokenValid
        ]);
    }

    /**
     * Traite le changement de mot de passe.
     * Vérifie le token, la complexité du mot de passe, puis met à jour la base de données.
     * Route: POST /reset
     *
     * @return void
     */
    public function processReset(): void {
        $isTokenValid = true;
        try {
            $this->validateCsrf();

            $tokenRaw = $_POST['token'] ?? $_GET['token'] ?? '';
            $token = is_string($tokenRaw) ? $tokenRaw : '';
            
            $pPassword = $_POST['password'] ?? '';
            $password = is_string($pPassword) ? $pPassword : '';
            
            $pConfirm = $_POST['confirm_password'] ?? '';
            $confirm = is_string($pConfirm) ? $pConfirm : '';

            if (empty($token))
                throw new \Exception("Token manquant.");

            $hashedToken = hash('sha256', $token);
            $user = $this->userModel->getUserByToken($hashedToken);

            if (!$user) {
                // Si le token est invalide lors du traitement, afficher une 404
                $this->errorController->error404page();
                return;
            }

            if ($password !== $confirm) {
                throw new \Exception("Les mots de passe ne correspondent pas.");
            }

            if (!\App\Models\UserModel::isPasswordStrong($password)) {
                throw new \Exception("Le mot de passe n'est pas assez sécurisé.");
            }

            $userId = $user['id'] ?? 0;
            if (!is_numeric($userId)) {
                 throw new \Exception("ID utilisateur invalide.");
            }

            $this->userModel->updatePassword((int)$userId, $password);
            $this->userModel->deleteResetTokensForUser((int)$userId);

            $this->flash('success', "Mot de passe modifié avec succès. Vous pouvez vous connecter.");
            $this->redirect('/login');

        } catch (\Exception $e) {
            $this->view->render('auth/reset', [
                'title' => 'Réinitialisation',
                'errors' => [$e->getMessage()],
                'csrf_token' => $_SESSION['csrf_token'],
                'isTokenValid' => $isTokenValid
            ]);
        }
    }

    // Méthodes utilitaires

    /**
     * Crée la session utilisateur après une connexion réussie.
     *
     * @param array{id: int|string, email: string, first_name: string, last_name: string, role?: string} $user
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
     * Valide les données d'inscription.
     *
     * @param array{first_name: string, last_name: string, email: string, password: string, confirm: string} $data
     * @throws \Exception Si une validation échoue.
     * @return void
     */
    private function validateRegisterInput(array $data): void{
        if (empty($data['first_name']) || mb_strlen($data['first_name']) > 100) {
            throw new \Exception("Prénom invalide.");
        }
        if (empty($data['last_name']) || mb_strlen($data['last_name']) > 100) {
            throw new \Exception("Nom invalide.");
        }
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            throw new \Exception("Email invalide.");
        }
        if ($data['password'] !== $data['confirm']) {
            throw new \Exception("Les mots de passe ne correspondent pas.");
        }
        if (!\App\Models\UserModel::isPasswordStrong($data['password'])) {
            throw new \Exception("Le mot de passe n'est pas assez sécurisé.");
        }
    }
}