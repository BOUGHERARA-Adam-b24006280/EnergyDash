<?php
/**
 * Fichier : Controller.php
 * Rôle : Contrôleur parent abstrait pour centraliser les fonctionnalités communes
 * Auteur : Lucas LEPAPE
 */

namespace App\Core;

use App\Core\Layout;

/**
 * Classe abstraite Controller
 * Fournit les utilitaires communs à tous les contrôleurs :
 * - gestion de session
 * - redirections
 * - authentification
 * - rendu de vue
 * - messages flash
 */
abstract class Controller
{
    /**
     * Constructeur : démarre la session automatiquement
     */
    public function __construct()
    {
        $this->startSession();
    }

    /**
     * Méthode par défaut : chaque contrôleur enfant doit la définir.
     * Cela permet d'assurer une cohérence entre les routes.
     */
    abstract public function index(): void;

    /**
     * Démarre la session si elle n'est pas déjà active
     */
    protected function startSession(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Vérifie qu'un utilisateur est connecté, sinon redirige vers /login
     */
    protected function requireLogin(): void
    {
        if (empty($_SESSION['user'])) {
            $this->redirect('/login');
        }
    }

    /**
     * Vérifie qu'un utilisateur est administrateur, sinon redirige vers /dashboard
     */
    protected function requireAdmin(): void
    {
        $this->requireLogin();

        // Sécurise et typise la session utilisateur
        $user = $_SESSION['user'] ?? null;

        // Vérifie que la clé 'role' existe et qu'elle vaut bien 'admin'
        if (!is_array($user) || !isset($user['role']) || $user['role'] !== 'admin') {
            $this->redirect('/dashboard');
        }
    }

    /**
     * Rend une vue avec la classe Layout
     *
     * @param string $viewPath            Chemin du fichier de vue
     * @param string $title               Titre de la page
     * @param string $layout              Nom du layout à utiliser (par défaut 'default')
     * @param array<string, mixed> $data  Données à passer à la vue
     */
    protected function render(string $viewPath, string $title, string $layout = 'default', array $data = []): void
    {
        $layout = new Layout($viewPath, $title, $layout);
        $layout->render($data);
    }

    /**
     * Redirige vers une URL et arrête l’exécution
     *
     * @param string $url URL de redirection
     */
    protected function redirect(string $url): void
    {
        header("Location: {$url}");
        exit;
    }

    /**
     * Ajoute un message flash à la session
     *
     * @param string $type    Type du message ('success', 'error', etc.)
     * @param string $message Contenu du message
     */
    protected function flash(string $type, string $message): void
    {
        $_SESSION[$type] = $message;
    }

    /**
     * Récupère et supprime un message flash de la session
     *
     * @param string $type
     * @return string|null
     */
    protected function getFlash(string $type): ?string
    {
        if (isset($_SESSION[$type]) && is_scalar($_SESSION[$type])) {
            $message = (string) $_SESSION[$type];
            unset($_SESSION[$type]);
            return $message;
        }
        return null;
    }

    /**
     * Sécurise une entrée utilisateur (trim + htmlspecialchars)
     *
     * @param string|null $value
     * @return string
     */
    protected function sanitize(?string $value): string
    {
        return htmlspecialchars(trim((string) $value), ENT_QUOTES, 'UTF-8');
    }
}