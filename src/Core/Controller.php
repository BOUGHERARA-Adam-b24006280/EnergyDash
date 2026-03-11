<?php
/**
 * Fichier : Controller.php
 * Rôle : Classe parente de tous les contrôleurs.
 * Elle fait le lien entre le DashboardController et la View.
 */

namespace App\Core;

/**
 * Classe abstraite Controller
 * Base pour tous les contrôleurs de l'application.
 * Fournit des méthodes utilitaires (rendu de vue, redirection, flash messages, etc.).
 *
 * @package App\Core
 */
abstract class Controller {
    /** @var View Instance de la View pour le rendu des vues */
    protected View $view;

    /**
     * Constructeur.
     * Démarre la session si elle n'est pas déjà active.
     * Initialise la View.
     */
    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $this->view = new View();
    }

    /**
     * Vérifie si l'utilisateur est connecté.
     * Sinon, redirige vers la page de login.
     *
     * @return void
     */
    protected function requireLogin(): void {
        if (!isset($_SESSION['user'])) $this->redirect('/login');
    }

    /**
     * Vérifie si l'utilisateur est ADMIN.
     * Sinon, redirige vers le profil ou l'accueil.
     *
     * @return void
     */
    protected function requireAdmin(): void {
        $this->requireLogin();

        $user = $_SESSION['user'] ?? null;

        if (!is_array($user) || ($user['role'] ?? '') !== 'admin') $this->redirect('/profile');
    }

    /**
     * Nettoie une chaîne de caractères pour éviter les failles XSS.
     * Remplace les caractères spéciaux par des entités HTML.
     *
     * @param string $input La chaîne à nettoyer.
     * @return string La chaîne nettoyée.
     */
    protected function sanitize(string $input): string{
        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }

    /**
     * Redirige vers une autre URL.
     *
     * @param string $url L'URL de redirection.
     * @return void
     */
    protected function redirect(string $url): void{
        header("Location: $url");
        exit;
    }

    /**
     * Gestion des messages Flash (Succès / Erreur).
     *
     * @param string $type Le type de message ('success' ou 'error').
     * @param string $message Le contenu du message.
     * @return void
     */
    protected function flash(string $type, string $message): void {
        $_SESSION[$type] = $message;
    }

    /**
     * Intialise le token CRSF s'il n'existe pas déjà dans la session.
     * 
     * @return void
     */
    protected function initCsrf(): void {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
    }

    /**
     * Valide le token CRSF réçu en POST.
     * Compare le token de la requête POST avec celui stocké en session.
     * 
     * @throws \Exception Si le token est invalide ou manquant.
     * @return void
     */
    protected function validateCsrf(): void {
        $rawToken = $_POST['csrf_token'] ?? '';
        $token = is_string($rawToken) ? $rawToken : '';
        
        $rawSession = $_SESSION['csrf_token'] ?? '';
        $sessionToken = is_string($rawSession) ? $rawSession : '';

        if (empty($sessionToken) || !hash_equals($sessionToken, $token)) {
            throw new \Exception("Session expirée ou requête non autorisée. Veuillez recharger la page.");
        }
    }

}