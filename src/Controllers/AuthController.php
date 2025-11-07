<?php
/**
 * Fichier : AuthController.php
 * Rôle : Gère les actions d'authentification (inscription, connexion, déconnexion)
 * Auteur : Mohamed-Amine HADDAD, Lucas LEPAPE
 */

namespace App\Controllers;

use App\Core\Layout;
use App\Core\Mailer;
use App\Models\UserModel;
use Exception;

/**
 * Classe AuthController qui gère la gestion et les actions des différentes pages d'authentifications
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
     * * @return void
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

                $hash = $user['password'] ?? '';
                if (!password_verify($password, $hash)) {
                    throw new Exception("Mot de passe incorrect.");
                }

                session_regenerate_id(true); // Empêche le vol de session (OWASP #7)
                $_SESSION['user'] = [
                    'id' => $user['id'],
                    'first_name' => $user['first_name'],
                    'last_name' => $user['last_name'],
                    'email' => $user['email'],
                    'role' => $user['role'] ?? 'user'
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
     * * @return void
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
     * * @return void
     */
    public function logout(): void {
        session_start();
        session_destroy();
        header('Location: /login');
        exit;
    }

    /**
     * Affiche et traite le formulaire de "mot de passe oublié"
     */
    public function forgotPassword(): void
    {
        $errors = [];
        $success = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                // Assure que $email est une chaîne avant trim
                $email = isset($_POST['email']) && is_scalar($_POST['email']) ? trim((string)$_POST['email']) : '';

                if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    throw new Exception("Adresse e-mail invalide.");
                }

                $user = $this->userModel->getUserByEmail($email);
                if (!$user) {
                    throw new Exception("Aucun utilisateur trouvé avec cet e-mail.");
                }

                // Génère un token unique et le hash
                $token = bin2hex(random_bytes(32));
                $hashedToken = hash('sha256', $token);

                // Fixe le fuseau et calcule l'expiration
                $expiresAt = date('Y-m-d H:i:s', strtotime('+30 minutes'));

                // Assure que $user['id'] est un entier
                $userId = $user['id'] ?? null;
                if (!is_numeric($userId)) {
                    throw new Exception("ID utilisateur invalide.");
                }
                $this->userModel->storeResetToken((int)$userId, $hashedToken, $expiresAt);

                // Prépare le lien de réinitialisation avec le token non haché
                $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
                
                // Assure que $_SERVER['HTTP_HOST'] est une chaîne
                $httpHost = $_SERVER['HTTP_HOST'] ?? null;
                if (!is_string($httpHost) || empty($httpHost)) {
                    throw new Exception("Impossible de déterminer l'hôte du serveur.");
                }
                $resetLink = "$protocol://{$httpHost}/reset?token=$token";

                // Assure que $user['first_name'] est une chaîne
                $firstName = $user['first_name'] ?? 'Utilisateur';
                if (!is_string($firstName)) {
                    $firstName = 'Utilisateur'; // Fallback
                }

                // Contenu du mail
                $subject = "Réinitialisation de votre mot de passe";
                $message = <<<EOT
                    Bonjour {$firstName},

                    Vous avez demandé à réinitialiser votre mot de passe.
                    Cliquez sur le lien ci-dessous pour choisir un nouveau mot de passe :
                    $resetLink

                    Ce lien expirera dans 30 minutes.

                    Si vous n'êtes pas à l'origine de cette demande, ignorez simplement cet e-mail.

                    L'équipe Energy Dash.
                EOT;

                // Envoi du mail
                $mailer = new Mailer();
                if (!$mailer->send($email, $subject, $message)) {
                    throw new Exception("Impossible d'envoyer l'e-mail. Veuillez réessayer plus tard.");
                }

                $success = "Un e-mail de réinitialisation a été envoyé à $email.";
            } catch (Exception $e) {
                $errors[] = $e->getMessage();
            }
        }

        $layout = new Layout(__DIR__ . '/../Views/auth/forgot.php', 'Mot de passe oublié');
        $layout->render(['errors' => $errors, 'success' => $success]);
    }

    /**
     * Page de réinitialisation du mot de passe via token
     */
    public function resetPassword(): void
    {
        $errors = [];
        $success = '';
        
        // FIX (Ligne 247) : Assure que $token (de $_GET) est une chaîne avant hash()
        $token = isset($_GET['token']) && is_scalar($_GET['token']) ? (string)$_GET['token'] : '';

        if ($token === '') {
            $errors[] = "Lien de réinitialisation invalide ou manquant.";
        } else {
            // On calcule le hash du token pour vérifier
            $hashedToken = hash('sha256', $token);
            $user = $this->userModel->getUserByToken($hashedToken);

            if (!$user) {
                $errors[] = "Ce lien est invalide ou a expiré.";
            } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
                try {
                    // FIX (Ligne 260) : Assure que $password et $confirm sont des chaînes
                    $password = isset($_POST['password']) && is_scalar($_POST['password']) ? (string)$_POST['password'] : '';
                    $confirm  = isset($_POST['confirm_password']) && is_scalar($_POST['confirm_password']) ? (string)$_POST['confirm_password'] : '';

                    if ($password !== $confirm) {
                        throw new Exception("Les mots de passe ne correspondent pas.");
                    }
                    if (mb_strlen($password) < 8) {
                        throw new Exception("Le mot de passe doit contenir au moins 8 caractères.");
                    }

                    // FIX (Ligne 265) : Assure que $user['id'] est un entier et $password une chaîne
                    $userId = $user['id'] ?? null;
                    if (!is_numeric($userId)) {
                        throw new Exception("ID utilisateur invalide pour la mise à jour.");
                    }
                    
                    // $password est déjà une chaîne grâce au fix de la Ligne 260
                    $this->userModel->updatePassword((int)$userId, $password);
                    $this->userModel->invalidateToken($hashedToken);

                    $success = "Votre mot de passe a été réinitialisé avec succès. Vous pouvez maintenant vous connecter.";
                } catch (Exception $e) {
                    $errors[] = $e->getMessage();
                }
            }
        }

        $layout = new Layout(__DIR__ . '/../Views/auth/reset.php', 'Réinitialiser le mot de passe');
        $layout->render([
            'errors'  => $errors,
            'success' => $success
        ]);
    }
}