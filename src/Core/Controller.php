<?php
/**
 * Fichier : Controller.php
 * Rôle : Classe parente de tous les contrôleurs.
 * Elle fait le lien entre le DashboardController et le Layout.
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
    /**
     * Constructeur.
     * Démarre la session si elle n'est pas déjà active.
     */
    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Affiche une vue en utilisant le Layout principal.
     *
     * @param string $view Nom du fichier vue (ex: 'dashboard/dashboard')
     * @param array $data Données à passer à la vue (ex: ['cities' => $cities])
     * @return void
     */
    protected function render(string $view, array $data = []): void {
        $data['success'] = $data['success'] ?? $this->getFlash('success');
        $data['error'] = $data['error'] ?? $this->getFlash('error');

        $viewPath = __DIR__ . '/../Views/' . $view . '.php';
        $title = $data['title'] ?? 'EnergyDash';
        $layout = new Layout($viewPath, $title);
        $layout->render($data);
    }

    /**
     * Vérifie si l'utilisateur est connecté.
     * Sinon, redirige vers la page de login.
     *
     * @return void
     */
    protected function requireLogin(): void {
        if (!isset($_SESSION['user'])) {
            $this->redirect('/login');
        }
    }

    /**
     * Vérifie si l'utilisateur est ADMIN.
     * Sinon, redirige vers le profil ou l'accueil.
     *
     * @return void
     */
    protected function requireAdmin(): void {
        $this->requireLogin();

        if (($_SESSION['user']['role'] ?? '') !== 'admin') {
            $this->redirect('/profile');
        }
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
     * Récupère un message Flash et le supprime de la session.
     *
     * @param string $type Le type de message à récupérer.
     * @return string|null Le message ou null s'il n'existe pas.
     */
    protected function getFlash(string $type): ?string {
        if (isset($_SESSION[$type])) {
            $msg = $_SESSION[$type];
            unset($_SESSION[$type]);
            return $msg;
        }
        return null;
    }
}