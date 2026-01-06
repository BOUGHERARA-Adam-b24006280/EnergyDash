<?php
/**
 * Fichier : Controller.php
 * Rôle : Classe parente de tous les contrôleurs.
 * Elle fait le lien entre le DashboardController et le Layout.
 */

namespace App\Core;

abstract class Controller
{
    /**
     * Affiche une vue en utilisant le Layout principal.
     * * @param string $view Nom du fichier vue (ex: 'dashboard/dashboard')
     * @param array $data Données à passer à la vue (ex: ['cities' => $cities])
     */
    protected function render(string $view, array $data = []): void {
        $viewPath = __DIR__ . '/../Views/' . $view . '.php';
        $title = $data['title'] ?? 'EnergyDash';
        $layout = new Layout($viewPath, $title);
        $layout->render($data);
    }

    /**
     * Vérifie si l'utilisateur est connecté.
     * Sinon, redirige vers la page de login.
     */
    protected function requireLogin(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['user'])) {
            $this->redirect('/login');
        }
    }

    /**
     * Vérifie si l'utilisateur est ADMIN.
     * Sinon, redirige vers le profil ou l'accueil.
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
     */
    protected function sanitize(string $input): string {
            return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }

    /**
     * Redirige vers une autre URL.
     */
    protected function redirect(string $url): void {
        header("Location: $url");
        exit;
    }

    /**
     * Gestion des messages Flash (Succès / Erreur).
     */
    protected function flash(string $type, string $message): void
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $_SESSION[$type] = $message;
    }

    protected function getFlash(string $type): ?string
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (isset($_SESSION[$type])) {
            $msg = $_SESSION[$type];
            unset($_SESSION[$type]);
            return $msg;
        }
        return null;
    }
}