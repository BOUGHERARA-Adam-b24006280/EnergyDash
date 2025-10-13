<?php
require_once __DIR__ . '/../models/UserModel.php';
require_once __DIR__ . '/../vendor/autoload.php'; // pour charger PHPMailer avec Composer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class AuthController {
    private $pdo;
    private $userModel;

    public function __construct($pdo) {
        $this->pdo = $pdo;
        $this->userModel = new UserModel($pdo);
    }

    // Afficher / traiter le formulaire de connexion
    public function login() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';

            if (empty($email) || empty($password)) {
                $error = "Tous les champs doivent être remplis.";
            } else {
                $user = $this->userModel->login($email, $password);
                if ($user) {
                    $_SESSION['user'] = [
                        'id' => $user['id'],
                        'email' => $user['email'],
                    ];
                    header('Location: /dashboard');
                    exit;
                } else {
                    $error = "Email ou mot de passe incorrect.";
                }
            }
        }

        $title = "Connexion";
        $body = __DIR__ . '/../views/auth/login.php';
        $navbar = __DIR__ . '/../views/shared/navbar.php';
        $footer = __DIR__ . '/../views/shared/footer.php';
        include __DIR__ . '/../views/shared/layout.php';
    }

    // Afficher / traiter le formulaire d'inscription
    public function register() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';
            $confirm_password = $_POST['confirm_password'] ?? '';
            $first_name = $_POST['first_name'] ?? '';
            $last_name = $_POST['last_name'] ?? '';

            // Vérification des champs vides
            if (empty($email) || empty($password) || empty($confirm_password) || empty($first_name) || empty($last_name)) {
                $error = "Tous les champs doivent être remplis.";

            } 
            // Vérification du format d'email
            elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error = "L'adresse e-mail est invalide. Exemple : nom@domaine.com";
            }
            // Vérification de la compléxité du mot de passe (OWASP)
            elseif (!$this->isPasswordStrong($password)) {
                $error = "Le mot de passe doit contenir au minimum 8 caractères, une majuscule, une minuscule, un chiffre et un symbol spécial";
            }
            // Vérification de la correspondance des mots de passe
            elseif ($password !== $confirm_password) {
                $error = "Les mots de passe ne correspondent pas.";
            }
            // Vérification de l'unicité de l'email
            elseif ($this->userModel->emailExists($email)) {
                $error = "L'email est déjà utilisé.";
            } 
            // Tout est OK -> enregistrement
            else {
                if ($this->userModel->register($first_name, $last_name, $email, $password)) {
                    header('Location: /login');
                    exit;
                } else {
                    $error = "Erreur lors de l'inscription.";
                }
            }
        }

        $title = "Inscription";
        include __DIR__ . '/../views/auth/register.php';
    }

    public function logout() {
        session_destroy();
        header('Location: /login');
        exit;
    }

    // Fonction mot de passe oublié
    private function isPasswordStrong($password) {
        if (strlen($password) < 8) {
            return false;
        }

        // Au moins une majuscule
        if (!preg_match('/[A-Z]/', $password)) {
            return false;
        }

        // Au moins une minuscule
        if (!preg_match('/[a-z]/', $password)) {
            return false;
        }

        // Au moins un chiffre
        if (!preg_match('/[0-9]/', $password)) {
            return false;
        }

        // Au moins un caractère spécial
        if (!preg_match('/[\W_]/', $password)) { // \W = tout sauf lettre/chiffre
            return false;
        }

        return true;
    }

    public function forgotPassword() {
        if (session_status() === PHP_SESSION_NONE) session_start();

        $error = $success = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = trim($_POST['email'] ?? '');

            if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error = "Veuillez saisir une adresse e-mail valide.";
            } else {
                // Cherche l'utilisateur
                $stmt = $this->pdo->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
                $stmt->execute([$email]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);

                // Message neutre (évite de révéler si l'email existe)
                $success = "Si cet e-mail est enregistré, vous allez recevoir un lien de réinitialisation.";

                if ($user) {
                    // Génère token fort
                    $token = bin2hex(random_bytes(32));

                    // Store token via UserModel (ou PDO direct)
                    $userModel = $this->userModel ?? new UserModel($this->pdo);
                    $userModel->storeResetToken((int)$user['id'], $token);

                    // Génère le lien complet
                    $host = $_SERVER['HTTP_HOST'];
                    $scheme = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
                    $resetLink = "{$scheme}://{$host}" . BASE_URL . "/reset-password?token=" . urlencode($token);

                    // Charge la config SMTP
                    $mailConfig = require __DIR__ . '/../config/mail.php';

                    $mail = new PHPMailer(true);
                    try {
                        $mail->isSMTP();
                        $mail->Host       = $mailConfig['host'];
                        $mail->SMTPAuth   = true;
                        $mail->Username   = $mailConfig['username'];
                        $mail->Password   = $mailConfig['password'];
                        $mail->SMTPSecure = 'tls'; // ou 'ssl' selon ton fournisseur
                        $mail->Port       = $mailConfig['port'];

                        $mail->setFrom($mailConfig['from_email'], $mailConfig['from_name']);
                        $mail->addAddress($email);

                        $mail->isHTML(true);
                        $mail->Subject = 'Réinitialisation de votre mot de passe - EnergyDash';
                        $mail->Body = "
                            <p>Bonjour,</p>
                            <p>Vous avez demandé la réinitialisation de votre mot de passe EnergyDash.</p>
                            <p>Cliquez sur le lien ci-dessous pour définir un nouveau mot de passe :</p>
                            <p><a href='{$resetLink}'>Réinitialiser mon mot de passe</a></p>
                            <p>Ce lien expirera dans 30 minutes.</p>
                            <p>Si vous n'êtes pas à l'origine de cette demande, ignorez simplement ce message.</p>
                            <p><em>L'équipe EnergyDash</em></p>
                        ";

                        $mail->send();
                        $success = "Si cet e-mail est enregistré, vous allez recevoir un lien de réinitialisation.";
                    } catch (Exception $e) {
                        // Ne pas afficher les détails d’erreur à l’utilisateur (pour la sécurité)
                        error_log("Erreur envoi mail : " . $mail->ErrorInfo);
                        $success = "Si cet e-mail est enregistré, vous allez recevoir un lien de réinitialisation.";
                    }

                }
            }
        }

        $title = "Mot de passe oublié";
        $body = __DIR__ . '/../views/auth/forgot_password.php';
        $navbar = __DIR__ . '/../views/shared/navbar.php';
        $footer = __DIR__ . '/../views/shared/footer.php';
        include __DIR__ . '/../views/shared/layout.php';
    }

    public function resetPassword() {
        if (session_status() === PHP_SESSION_NONE) session_start();

        $error = $success = null;
        $token = $_GET['token'] ?? $_POST['token'] ?? '';

        // si POST -> essayer d'appliquer le changement
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $password = $_POST['password'] ?? '';
            $confirm  = $_POST['confirm_password'] ?? '';

            if (empty($token)) {
                $error = "Lien invalide.";
            } elseif ($password !== $confirm) {
                $error = "Les mots de passe ne correspondent pas.";
            } elseif (!$this->isPasswordStrong($password)) {
                $error = "Mot de passe trop faible (8+, maj, min, chiffre, symbole).";
            } else {
                $userModel = $this->userModel ?? new UserModel($this->pdo);
                $reset = $userModel->findResetByToken($token, 30); // 30 minutes validade

                if (!$reset) {
                    $error = "Lien de réinitialisation invalide ou expiré.";
                } else {
                    // update
                    $hashed = password_hash($password, PASSWORD_DEFAULT);
                    $userModel->updatePassword((int)$reset['user_id'], $hashed);
                    $userModel->deleteResetTokens((int)$reset['user_id']);

                    // flash + redirect to login
                    $_SESSION['flash'] = "Votre mot de passe a été réinitialisé avec succès.";
                    header('Location: /login');
                    exit;
                }
            }
        } else {
            // GET : vérifier rapidement token (optionnel)
            if (!empty($token)) {
                $userModel = $this->userModel ?? new UserModel($this->pdo);
                $reset = $userModel->findResetByToken($token, 30);
                if (!$reset) {
                    $error = "Lien de réinitialisation invalide ou expiré.";
                }
            }
        }

        $title = "Réinitialisation du mot de passe";
        $body = __DIR__ . '/../views/auth/reset_password.php';
        $navbar = __DIR__ . '/../views/shared/navbar.php';
        $footer = __DIR__ . '/../views/shared/footer.php';
        include __DIR__ . '/../views/shared/layout.php';
    }

}
